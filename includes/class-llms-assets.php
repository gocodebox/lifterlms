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
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Assets Class
 *
 * @since [version]
 */
class LLMS_Assets {

	/**
	 * An ID used to identify the originating package (plugin or theme) of the asset handler instance.
	 *
	 * @var string
	 */
	protected $package_id = '';

	/**
	 * List of default asset definitions
	 *
	 * @var array[]
	 */
	protected $defaults = array(
		// Base defaults shared by all asset types.
		'base'   => array(
			'base_url'     => LLMS_PLUGIN_URL,
			'suffix'       => LLMS_ASSETS_SUFFIX,
			'dependencies' => array(),
			'version'      => LLMS_VERSION,
		),
		// Script specific defaults.
		'script' => array(
			'path'      => 'assets/js',
			'extension' => '.js',
			'in_footer' => true,
		),
		// Stylesheet specific defaults.
		'style'  => array(
			'path'      => 'assets/css',
			'extension' => '.css',
			'media'     => 'all',
			'rtl'       => true,
		),
	);

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
	 * @since [version]
	 *
	 * @param string  $package_id An ID used to identify the originating package (plugin or theme) of the asset handler instance.
	 * @param array[] $defaults   Array of asset definitions values. Accepts a partial list of values that is merged with the default defaults.
	 */
	public function __construct( $package_id, $defaults = array() ) {

		$this->package_id = $package_id;
		$this->defaults   = array_merge_recursive( $defaults, $this->defaults );

	}

	/**
	 * Define a list of assets by type so they can be enqueued or registered later.
	 *
	 * If an asset is already defined, redefining it will overwrite the previous definition.
	 *
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 *     @type string   $version      The asset version. Defaults to `LLMS_VERSION`.
	 *     @type string   $package_id   An ID used to identify the originating plugin or theme that defined the asset.
	 *     @type boolean  $in_footer    (For `script` assets only) Whether or not the script should be output in the footer. Defaults to `true`.
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
		 * @since [version]
		 *
		 * @param array|false $asset  Array of asset data or `false` if the asset has not been defined with LifterLMS.
		 * @param string      $handle The asset handle.
		 */
		$asset = apply_filters( "llms_get_{$type}_asset_before_prep", $asset, $handle );

		if ( $asset && is_array( $asset ) ) {

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

		}

		/**
		 * Filter static asset data prior to enqueueing or registering it with the WordPress core
		 *
		 * The dynamic portion of this filter, `{$type}`, refers to the asset type. Either "script" or "style".
		 *
		 * @since [version]
		 *
		 * @param array|false $asset  Array of asset data or `false` if the asset has not been defined with LifterLMS.
		 * @param string      $handle The asset handle.
		 */
		return apply_filters( "llms_get_{$type}_asset", $asset, $handle );

	}

	/**
	 * Retrieves an array of definition values based on asset type.
	 *
	 * @since [version]
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
		 * @since [version]
		 *
		 * @param array  $defaults Default definition values.
		 */
		return apply_filters( "llms_get_{$type}_asset_defaults", $defaults );

	}

	/**
	 * Retrieve the asset definition list for a given asset type.
	 *
	 * @since [version]
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
		 * @since [version]
		 *
		 * @param array[] $list The definition list.
		 */
		return apply_filters( "llms_get_{$type}_asset_definitions", $list );

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
	 * @since [version]
	 *
	 * @param string $handle The script's handle.
	 * @return boolean
	 */
	public function register_script( $handle ) {

		$script = $this->get( 'script', $handle );
		if ( $script ) {
			return wp_register_script( $handle, $script['src'], $script['dependencies'], $script['version'], $script['in_footer'] );
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
	 * @since [version]
	 *
	 * @param string $handle The stylesheets's handle.
	 * @return boolean
	 */
	public function register_style( $handle ) {

		$style = $this->get( 'style', $handle );
		if ( $style ) {

			$reg = wp_register_style( $handle, $style['src'], $style['dependencies'], $style['version'], $style['media'] );

			if ( $reg && $style['rtl'] ) {
				wp_style_add_data( $handle, 'rtl', 'replace' );
				wp_style_add_data( $handle, 'suffix', $style['suffix'] );
			}

			return $reg;

		}

		return false;

	}

}
