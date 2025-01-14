<?php
/**
 * Methods for static asset registration and enqueueing
 *
 * These methods require assets to be "defined" in a structured format.
 *
 * A defined asset is then enqueued or registered with the WordPress core using this derivative
 * API that requires only script handles.
 *
 * This API also aims to reduce redundancy in asset registrations by allowing "partial" definitions
 * which are filled with default values. For example, every asset in LifterLMS shares the same base
 * plugin url. Using this API we define that URL one time, instead of defining it over and over for
 * each individual asset.
 *
 * @package LifterLMS/Classes
 *
 * @since 4.4.0
 * @version 7.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Assets Class
 *
 * @since 4.4.0
 * @since 4.9.0 Added new default values related to script localization.
 * @since 5.5.0 Added new script default for `asset_file`.
 */
class LLMS_Assets {

	/**
	 * An ID used to identify the originating package (plugin or theme) of the asset handler instance.
	 *
	 * @var string
	 */
	protected $package_id = '';

	/**
	 * Determines if SCRIPT_DEBUG is enabled.
	 *
	 * @var boolean
	 */
	protected $debugging_assets = false;

	/**
	 * List of default asset definitions.
	 *
	 * @since 7.2.0 Use `LLMS_ASSETS_VERSION` for default asset version.
	 *
	 * @var array[]
	 */
	protected $defaults = array(
		// Base defaults shared by all asset types.
		'base'   => array(
			'base_file'    => LLMS_PLUGIN_FILE,
			'base_url'     => LLMS_PLUGIN_URL,
			'suffix'       => LLMS_ASSETS_SUFFIX,
			'dependencies' => array(),
			'version'      => LLMS_ASSETS_VERSION,
		),
		// Script specific defaults.
		'script' => array(
			'path'       => 'assets/js',
			'extension'  => '.js',
			'in_footer'  => true,
			'translate'  => false,
			'asset_file' => false,
		),
		// Stylesheet specific defaults.
		'style'  => array(
			'path'      => 'assets/css',
			'extension' => '.css',
			'media'     => 'all',
			'rtl'       => true,
		),
	);

	protected $inline = array();

	/**
	 * List of defined scripts.
	 *
	 * The full list of core script definitions can be found at includes/assets/llms-assets-scripts.php
	 *
	 * @var array[]
	 */
	protected $scripts = array();

	/**
	 * List of defined stylesheets.
	 *
	 * The full list of core stylesheet definitions can be found at includes/assets/llms-assets-styles.php
	 *
	 * @var array[]
	 */
	protected $styles = array();

	/**
	 * Constructor
	 *
	 * @since 4.4.0
	 * @since 4.9.0 Replace defaults instead of merging them.
	 *
	 * @param string  $package_id An ID used to identify the originating package (plugin or theme) of the asset handler instance.
	 * @param array[] $defaults   Array of asset definitions values. Accepts a partial list of values that is merged with the default defaults.
	 */
	public function __construct( $package_id, $defaults = array() ) {

		$this->package_id = $package_id;
		$this->defaults   = array_replace_recursive( $this->defaults, $defaults );

		/**
		 * Filter asset debug mode.
		 *
		 * Asset debug mode is used only to help debug inline assets although the asset suffix is also controlled by the same
		 * WP Core constants.g
		 *
		 * @since 4.4.0
		 *
		 * @param bool   $debugging  Whether or not debugging is enabled. Returns `true` when `SCRIPT_DEBUG` is on, and `false` otherwise.
		 * @param string $package_id An ID used to identify the originating plugin or theme that defined the asset.
		 */
		$this->debugging_assets = apply_filters( 'llms_assets_debug', ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ), $this->package_id );
	}

	/**
	 * Define a list of assets by type so they can be enqueued or registered later.
	 *
	 * If an asset is already defined, redefining it will overwrite the previous definition.
	 *
	 * @since 4.4.0
	 *
	 * @param string  $type   Asset type. Accepts 'scripts' or 'styles'.
	 * @param array[] $assets List of assets to define. The array key is the asset's handle. Each array value is an array of asset definitions.
	 * @return array[] Returns the updated list of defined assets.
	 */
	public function define( $type, $assets ) {

		if ( ! in_array( $type, array( 'scripts', 'styles' ), true ) ) {
			return false;
		}

		foreach ( $assets as $handle => $definition ) {
			$this->{$type}[ $handle ] = $definition;
		}

		return $this->$type;
	}

	/**
	 * Enqueue an inline script or style
	 *
	 * @since 4.4.0
	 *
	 * @param string    $handle   Inline asset ID.
	 * @param string    $asset    The inline script or CSS rule. This should *not* be wrapped in <script> or <style> tags.
	 * @param string    $location Output location of the inline asset. Accepts "style" (for stylesheets in the headr), "header" (for
	 *                            scripts in the header), or "footer" (for scripts in the footer).
	 * @param int|float $priority Output priority of the inline asset.
	 * @return float Returns the priority of the enqueued script
	 */
	public function enqueue_inline( $handle, $asset, $location, $priority = 10 ) {

		// If script already exists, remove it and re-enqueue.
		if ( $this->is_inline_enqueued( $handle ) ) {
			unset( $this->inline[ $handle ] );
		}

		$priority                = $this->get_inline_priority( $priority, $this->get_definitions_inline( $location ) );
		$this->inline[ $handle ] = compact( 'handle', 'asset', 'location', 'priority' );

		return $priority;
	}

	/**
	 * Enqueue (and maybe register) a defined script
	 *
	 * If the script has not yet been registered, it will be automatically registered.
	 *
	 * The script *must* be defined in one of the following places:
	 *
	 *   + The script definition list found at includes/assets/llms-assets-scripts.php
	 *   + Added to the definitions list via the `LLMS_Assets::define()` method.
	 *   + Added to the definition list via the `llms_get_script_asset_definitions` filter
	 *   + Added "just in time" via the `llms_get_script_asset` filter.
	 *
	 * If the script is *not defined* this function will return `false` because registration
	 * will fail.
	 *
	 * @since 4.4.0
	 *
	 * @param string $handle The script's handle.
	 * @return boolean
	 */
	public function enqueue_script( $handle ) {

		// Script was not registered and registration failed.
		if ( ! wp_script_is( $handle, 'registered' ) && ! $this->register_script( $handle ) ) {
			return false;
		}

		wp_enqueue_script( $handle );

		return wp_script_is( $handle, 'enqueued' );
	}

	/**
	 * Enqueue (and maybe register) a defined stylesheet
	 *
	 * If the stylesheet has not yet been registered, it will be automatically registered.
	 *
	 * The stylesheet *must* be defined in one of the following places:
	 *
	 *   + The stylesheet definition list found at includes/assets/llms-assets-styles.php
	 *   + Added to the definitions list via the `LLMS_Assets::define()` method.
	 *   + Added to the definition list via the `llms_get_style_asset_definitions` filter
	 *   + Added "just in time" via the `llms_get_style_asset` filter.
	 *
	 * If the stylesheet is *not defined* this function will return `false` because registration
	 * will fail.
	 *
	 * @since 4.4.0
	 *
	 * @param string $handle The stylesheets's handle.
	 * @return boolean
	 */
	public function enqueue_style( $handle ) {

		// Style was not registered and registration failed.
		if ( ! wp_style_is( $handle, 'registered' ) && ! $this->register_style( $handle ) ) {
			return false;
		}

		wp_enqueue_style( $handle );

		return wp_style_is( $handle, 'enqueued' );
	}

	/**
	 * Retrieve an asset definition by type and handle
	 *
	 * Locates the asset by type and handle and merges a potentially impartial asset definition
	 * with default values from the `get_defaults()` method.
	 *
	 * @since 4.4.0
	 * @since 4.4.1 Replace truthy check with an strict check against `false` to ensure assets defined with an empty array signifying all default values should be used.
	 * @since 5.5.0 Load dependency and version info from an asset.php file when `$asset_file` is `true`.
	 *
	 * @param string $type   The asset type. Accepts either "script" or "style".
	 * @param string $handle The asset handle.
	 * @return array|false {
	 *     An asset definition array or `false` if an asset definition could not be located.
	 *
	 *     @type string   $file_name    The file name of the asset. Excludes the path, suffix, and extension,  eg: 'llms' for 'llms.js'. Defaults to the asset's handle.
	 *     @type string   $base_url     The base URL used to locate the asset on the server. Defaults to `LLMS_PLUGIN_URL`.
	 *     @type string   $path         The relative path to the asset within the plugin directory. Defaults to `assets/js` for scripts and `assets/css` for styles.
	 *     @type string   $extension    The filename extension for the asset. Defaults to `.js` for scripts and `.css` for styles.
	 *     @type string   $suffix       The file suffix for the asset, for example `.min` for minified files. Defaults to `LLMS_ASSETS_SUFFIX`.
	 *     @type string[] $dependencies An array of asset handles the asset depends on. These assets do not necessarily need to be assets defined by LifterLMS, for example WP Core scripts, such as `jquery`, can be used.
	 *     @type string   $version      The asset version. Defaults to `LLMS_ASSETS_VERSION`.
	 *     @type string   $package_id   An ID used to identify the originating plugin or theme that defined the asset.
	 *     @type boolean  $in_footer    (For `script` assets only) Whether or not the script should be output in the footer. Defaults to `true`.
	 *     @type boolean  $translate    (For `script` assets only) Whether or not script translations should be set. Defaults to `false`.
	 *     @type boolean  $asset_file   (For `script` assets only) Whether or not the script has an asset file (generated via the @wordpress/dependency-extraction-webpack-plugin).
	 *     @type boolean  $rtl          (For `style` assets only) Whether or not to automatically add RTL style data for the stylesheet. Defaults to `true`.
	 *     @type boolean  $media        (For `style` assets only) The stylesheet's media type. Defaults to `all`.
	 * }
	 */
	protected function get( $type, $handle ) {

		$list  = $this->get_definitions( $type );
		$asset = isset( $list[ $handle ] ) ? $list[ $handle ] : false;

		/**
		 * Filter static asset data prior to preparing the definition
		 *
		 * The definition is "prepared" by merging its data with the default data and preparing its src.
		 *
		 * The dynamic portion of this filter, `{$type}`, refers to the asset type. Either "script" or "style".
		 *
		 * @since 4.4.0
		 *
		 * @param array|false $asset      Array of asset data or `false` if the asset has not been defined with LifterLMS.
		 * @param string      $handle     The asset handle.
		 * @param string      $package_id An ID used to identify the originating plugin or theme that defined the asset.
		 */
		$asset = apply_filters( "llms_get_{$type}_asset_before_prep", $asset, $handle, $this->package_id );

		if ( false !== $asset && is_array( $asset ) ) {

			$asset = wp_parse_args( $asset, $this->get_defaults( $type ) );

			$asset['handle']     = $handle;
			$asset['package_id'] = $this->package_id;
			$asset['file_name']  = ! empty( $asset['file_name'] ) ? $asset['file_name'] : $handle;
			$asset['src']        = ! empty( $asset['src'] ) ? $asset['src'] : implode(
				'',
				array(
					trailingslashit( $asset['base_url'] ),
					trailingslashit( $asset['path'] ),
					$asset['file_name'],
					$asset['suffix'],
					$asset['extension'],
				)
			);

			$asset = $this->merge_asset_file( $asset );

		}

		/**
		 * Filter static asset data prior to enqueueing or registering it with the WordPress core
		 *
		 * The dynamic portion of this filter, `{$type}`, refers to the asset type. Either "script" or "style".
		 *
		 * @since 4.4.0
		 *
		 * @param array|false $asset  Array of asset data or `false` if the asset has not been defined with LifterLMS.
		 * @param string      $handle The asset handle.
		 */
		return apply_filters( "llms_get_{$type}_asset", $asset, $handle );
	}

	/**
	 * Retrieves an array of definition values based on asset type.
	 *
	 * @since 4.4.0
	 *
	 * @param string $type The asset type. Accepts either "script" or "style".
	 * @return array
	 */
	protected function get_defaults( $type ) {

		$type_defaults = isset( $this->defaults[ $type ] ) ? $this->defaults[ $type ] : array();
		$defaults      = array_merge( $this->defaults['base'], $type_defaults );

		/**
		 * Filter the default values used to register or enqueue an asset
		 *
		 * The dynamic portion of this filter, `{$type}`, refers to the asset type. Either "script" or "style".
		 *
		 * @since 4.4.0
		 *
		 * @param array  $defaults   Default definition values.
		 * @param string $package_id An ID used to identify the originating plugin or theme that defined the asset.
		 */
		return apply_filters( "llms_get_{$type}_asset_defaults", $defaults, $this->package_id );
	}

	/**
	 * Retrieve the asset definition list for a given asset type.
	 *
	 * @since 4.4.0
	 *
	 * @param string $type The asset type. Accepts either "script" or "style".
	 * @return array[]
	 */
	protected function get_definitions( $type ) {

		switch ( $type ) {
			case 'script':
				$list = $this->scripts;
				break;

			case 'style':
				$list = $this->styles;
				break;

			default:
				$list = array();

		}

		/**
		 * Filter the definition list of static assets for the given type
		 *
		 * The dynamic portion of this filter, `{$type}`, refers to the asset type. Either "script" or "style".
		 *
		 * @since 4.4.0
		 *
		 * @param array[] $list       The definition list.
		 * @param string  $package_id An ID used to identify the originating plugin or theme that defined the asset.
		 */
		return apply_filters( "llms_get_{$type}_asset_definitions", $list, $this->package_id );
	}


	/**
	 * Retrieve a list of inline asset definitions by location.
	 *
	 * @since 4.4.0
	 *
	 * @param string $location Location of scripts to output. Accepts "style", "header", or "footer".
	 *                         Inline header styles are output using "style".
	 *                         Inline scripts are output using either "header" or "footer", output in their respective locations.
	 * @return array[]
	 */
	protected function get_definitions_inline( $location ) {

		$assets = array();

		foreach ( $this->inline as $handle => $definition ) {

			if ( $location === $definition['location'] ) {
				$assets[ $handle ] = $definition;
			}
		}

		// Sort by priority.
		uasort(
			$assets,
			function ( $a, $b ) {
				if ( $a['priority'] === $b['priority'] ) {
					return 0;
				}
				return $a['priority'] < $b['priority'] ? -1 : 1;
			}
		);

		return $assets;
	}

	/**
	 * Auto-increment inline asset priority to prevent duplicates.
	 *
	 * This ensures that inline assets are always enqueued with a unique priority for their requested
	 * location.
	 *
	 * @since 4.4.0
	 * @since 7.0.0 When increasing priorities, round to the nearest two decimals.
	 *
	 * @param float $priority      Requested enqueue priority.
	 * @param array $inline_assets List of existing inline assets for the requested location.
	 * @return float
	 */
	protected function get_inline_priority( $priority, $inline_assets = array() ) {

		$priority = floatval( $priority );

		if ( $inline_assets ) {

			$priorities = wp_list_pluck( $inline_assets, 'priority' );
			while ( in_array( $priority, $priorities, true ) ) {
				$priority = round( $priority + 0.01, 2 );
			}
		}

		return $priority;
	}

	/**
	 * Determines if an inline asset is enqueued
	 *
	 * @since 4.4.0
	 *
	 * @param string $handle Inline asset handle.
	 * @return boolean
	 */
	public function is_inline_enqueued( $handle ) {
		return in_array( $handle, array_keys( $this->inline ), true );
	}

	/**
	 * Retrieve dependency and version info from a script asset's asset.php file
	 *
	 * Loads the asset.php file (generated via the @wordpress/dependency-extraction-webpack-plugin) and merges it
	 * into an existing asset array.
	 *
	 * @since 5.5.0
	 *
	 * @param array $asset An asset definition array.
	 * @return array
	 */
	protected function merge_asset_file( $asset ) {

		if ( empty( $asset['asset_file'] ) ) {
			return $asset;
		}

		$asset_file_path = plugin_dir_path( $asset['base_file'] ) . trailingslashit( $asset['path'] ) . $asset['file_name'] . '.asset.php';
		if ( file_exists( $asset_file_path ) ) {
			$info                  = include $asset_file_path;
			$asset['dependencies'] = array_merge( $asset['dependencies'], $info['dependencies'] );
			$asset['version']      = $info['version'];
		}

		return $asset;
	}

	/**
	 * Output inline scripts
	 *
	 * @since 4.4.0
	 *
	 * @param string $location Location of scripts to output. Accepts "style", "header", or "footer".
	 *                         Inline header styles are output using "style".
	 *                         Inline scripts are output using either "header" or "footer", output in their respective locations.
	 * @return void
	 */
	public function output_inline( $location ) {

		$defs = self::get_definitions_inline( $location );

		if ( $defs ) {

			$assets = array();
			foreach ( $defs as $def ) {
				$assets[] = $this->prepare_inline_asset_for_output( $def, $location );
			}

			$open  = 'style' === $location ? '<style id="llms-inline-styles" type="text/css">' : sprintf( '<script id="llms-inline-%s-scripts" type="text/javascript">', $location );
			$close = 'style' === $location ? '</style>' : '</script>';

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $open . implode( '', $assets ) . $close;

		}
	}

	/**
	 * Prepares an inline asset definition for being output.
	 *
	 * When `$this->debugging_assets` is `true` this will add line breaks between each inline asset
	 * and output the asset's handle as a comment before the asset's script/style so that the
	 * inline assets can be quickly located and reviewed in the generated source of the page.
	 *
	 * @since 4.4.0
	 *
	 * @param array  $asset    The inline asset definition array.
	 * @param string $location The location of the asset. Accepts "header", "footer", or "style".
	 * @return string
	 */
	protected function prepare_inline_asset_for_output( $asset, $location ) {

		$before = '';
		$after  = '';

		// Output inline asset handles and add line breaks when debugging.
		if ( $this->debugging_assets ) {

			// Setup the comment template.
			$before = 'style' === $location ? '/* %s. */' : '// %s.';

			// Add line breaks.
			$before .= "\n";
			$after   = "\n";

		}

		return sprintf( $before, $asset['handle'] ) . $asset['asset'] . $after;
	}

	/**
	 * Registers a defined script with WordPress
	 *
	 * The script *must* be defined in one of the following places:
	 *
	 *   + The script definition list found at includes/assets/llms-assets-scripts.php
	 *   + Added to the definition list via the `llms_get_script_asset_definitions` filter
	 *   + Added "just in time" via the `llms_get_script_asset` filter.
	 *
	 * If the script is *not defined* this function will return `false`.
	 *
	 * @since 4.4.0
	 * @since 4.9.0 Automatically set script translations when `translate=true`.
	 * @since 5.5.0 Automatically register all of the asset's dependencies.
	 *
	 * @param string $handle The script's handle.
	 * @return boolean
	 */
	public function register_script( $handle ) {

		$script = $this->get( 'script', $handle );
		if ( $script ) {

			array_map( array( $this, 'register_script' ), $script['dependencies'] );

			$reg = wp_register_script( $handle, $script['src'], $script['dependencies'], $script['version'], $script['in_footer'] );
			if ( $reg && $script['translate'] ) {
				$this->set_script_translations( $script );
			}

			return $reg;

		}

		return false;
	}

	/**
	 * Register a defined stylesheet
	 *
	 * If the stylesheet has not yet been registered, it will be automatically registered.
	 *
	 * The stylesheet *must* be defined in one of the following places:
	 *
	 *   + The stylesheet definition list found at includes/assets/llms-assets-styles.php
	 *   + Added to the definition list via the `llms_get_style_asset_definitions` filter
	 *   + Added "just in time" via the `llms_get_style_asset` filter.
	 *
	 * If the stylesheet is *not defined* this function will return `false`.
	 *
	 * This method will also automatically add RTL style data unless explicitly told not to do so.
	 *
	 * The RTL stylesheet should have the same name (and suffix) with `-rtl` included prior to the suffix, for example
	 * `llms.css` (or `llms.min.css`) would add the RTL stylesheet `llms-rtl.css` (or `llms-rtl.min.css`).
	 *
	 * @since 4.4.0
	 * @since 5.5.0 Automatically register all of the asset's dependencies.
	 *
	 * @param string $handle The stylesheets's handle.
	 * @return boolean
	 */
	public function register_style( $handle ) {

		$style = $this->get( 'style', $handle );
		if ( $style ) {

			array_map( array( $this, 'register_style' ), $style['dependencies'] );

			$reg = wp_register_style( $handle, $style['src'], $style['dependencies'], $style['version'], $style['media'] );

			if ( $reg && $style['rtl'] ) {
				wp_style_add_data( $handle, 'rtl', 'replace' );
				wp_style_add_data( $handle, 'suffix', $style['suffix'] );
			}

			return $reg;

		}

		return false;
	}

	/**
	 * Load JSON format localization files for a registered script
	 *
	 * This method mimics the behavior of PO/MO pot files loaded for PHP localization.
	 *
	 * Language files can be found in the following locations (The first loaded file takes priority):
	 *
	 *   1. wp-content/languages/{$textdomain}/{$textdomain}-{$locale}-{$file_md5_hash}.json
	 *
	 *      This is recommended "safe" location where custom language files can be stored. A file
	 *      stored in this directory will never be automatically overwritten.
	 *
	 *   2. wp-content/languages/plugins/{$textdomain}-{$locale}-{$file_md5_hash}.json
	 *
	 *      This is the default directory where WordPress will download language files from the
	 *      WordPress GlotPress server during updates. If you store a custom language file in this
	 *      directory it will be overwritten during updates.
	 *
	 *   3. wp-content/plugins/{$textdomain}/languages/{$textdomain}-{$locale}-{$file_md5_hash}.json
	 *
	 *      This is the the LifterLMS plugin directory. A language file stored in this directory will
	 *      be removed from the server during a LifterLMS plugin update.
	 *
	 * @since 4.9.0
	 *
	 * @param array $script An asset definition array from the return of `LLMS_Assets::get()`.
	 * @return void
	 */
	protected function set_script_translations( $script ) {

		$plugin_data = get_plugin_data( $script['base_file'], false, false );
		$domain      = $plugin_data['TextDomain'];

		// Possible directories where the language files may be found.
		$dirs = array(
			llms_l10n_get_safe_directory(),
			WP_LANG_DIR . '/plugins', // Default language directory.
			trailingslashit( plugin_dir_path( $script['base_file'] ) ) . untrailingslashit( ltrim( $plugin_data['DomainPath'], '/' ) ), // Language directory within the plugin.
		);

		foreach ( $dirs as $dir ) {
			wp_set_script_translations( $script['handle'], $domain, $dir );
		}
	}
}
