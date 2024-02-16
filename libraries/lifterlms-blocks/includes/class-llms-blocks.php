<?php
/**
 * Plugin Initialization
 *
 * @package LifterLMS_Blocks/Classes
 *
 * @since 1.0.0
 * @version 2.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Blocks class
 *
 * @since 1.0.0
 * @since 2.2.1 Handle '-src' in WordPress version numbers in `init()`.
 */
class LLMS_Blocks {

	/**
	 * Minimum LifterLMS core version required to run as a plugin.
	 *
	 * @since 2.5.0
	 *
	 * @var string
	 */
	const MIN_CORE_VERSION = '7.2.0';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Updated.
	 * @since 1.5.1 Add `admin_print_scripts` hook to handle outputting dynamic block information.
	 * @since 1.10.0 Load localization files when running as an independent plugin.
	 * @since 2.0.0 Move action & filter hooks to the the `init()` method.
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'init' ) );

	}

	/**
	 * Add a custom LifterLMS block category
	 *
	 * @since 1.0.0
	 * @since 1.6.0 Add Form Fields category.
	 *
	 * @param array $categories existing block cats.
	 * @return array
	 */
	public function add_block_category( $categories ) {

		$categories[] = array(
			'slug'  => 'llms-blocks',
			'title' => __( 'LifterLMS Blocks', 'lifterlms' ),
		);

		array_unshift(
			$categories,
			array(
				'slug'  => 'llms-custom-fields',
				'title' => __( 'Custom User Information', 'lifterlms' ),
			)
		);

		array_unshift(
			$categories,
			array(
				'slug'  => 'llms-user-info-fields',
				'title' => __( 'User Information', 'lifterlms' ),
			)
		);

		return $categories;
	}


	/**
	 * Print dynamic block information as a JS variable.
	 *
	 * Allows us to ensure we only add visibility attributes to static blocks.
	 * Prevents an issue causing rest api validation issues during attribute validation
	 * because it's impossible to register custom attributes on a block.
	 *
	 * @link https://github.com/gocodebox/lifterlms-blocks/issues/30
	 *
	 * @since 1.5.1
	 * @since 2.0.0 Since WordPress 5.8 blocks are available in widgets and customizer screen too.
	 *
	 * @return void
	 */
	public function admin_print_scripts() {

		$screen = get_current_screen();
		if ( ! $screen || ( empty( $screen->is_block_editor ) && 'customize' !== $screen->base ) ) {
			return;
		}

		echo '<script>window.llms.dynamic_blocks = ' . wp_json_encode( $this->get_dynamic_block_names() ) . ';</script>';

	}

	/**
	 * Retrieve a list of dynamic block names registered with WordPress (excluding LifterLMS blocks).
	 *
	 * @since 1.5.1
	 *
	 * @return array
	 */
	private function get_dynamic_block_names() {
		$blocks = array();
		foreach ( get_dynamic_block_names() as $name ) {
			if ( 0 !== strpos( $name, 'llms/' ) ) {
				$blocks[] = $name;
			}
		}
		return apply_filters( 'llms_blocks_get_dynamic_block_names', $blocks );
	}

	/**
	 * Include all files.
	 *
	 * @since 2.0.0
	 * @since 2.3.0 Include php template block file.
	 *
	 * @return void
	 */
	private function includes() {

		// Functions.
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/functions-llms-blocks.php';

		// Classes.
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-assets.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-abstract-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-migrate.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-page-builders.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-post-instructors.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-post-types.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-post-visibility.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-reusable.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-status-tools.php';

		// Block Visibility Component.
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-visibility.php';

		// Dynamic Blocks.
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-course-information-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-course-syllabus-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-course-progress-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-instructors-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-lesson-navigation-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-lesson-progression-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-pricing-table-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-php-template-block.php';

	}

	/**
	 * Register all blocks & components.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Add status tools class.
	 * @since 1.9.0 Added course progress block class.
	 * @since 2.0.0 Return early if LifterLMS isn't installed, move file inclusion to `$this->includes()`,
	 *              and moved actions and filters from the constructor.
	 * @since 2.2.1 Handle '-src' in WordPress version numbers.
	 * @since 2.5.0 Updated minimum LifterLMS core version to 7.2.0.
	 *
	 * @return  void
	 */
	public function init() {

		if ( ! function_exists( 'llms' ) || ! version_compare( self::MIN_CORE_VERSION, llms()->version, '<=' ) ) {
			return;
		}

		$this->includes();

		add_action( 'add_meta_boxes', array( $this, 'remove_metaboxes' ), 999, 2 );

		global $wp_version;
		$filter = version_compare( $wp_version, '5.8-src', '>=' ) ? 'block_categories_all' : 'block_categories';

		add_filter( $filter, array( $this, 'add_block_category' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ), 15 );

		/**
		 * When loaded as a library included by the LifterLMS core localization is handled by the LifterLMS core.
		 *
		 * When the plugin is loaded by itself as a plugin, we must localize it independently.
		 */
		if ( ! defined( 'LLMS_BLOCKS_LIB' ) || ! LLMS_BLOCKS_LIB ) {
			add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		}

	}

	/**
	 * Load l10n files.
	 *
	 * This method is only used when the plugin is loaded as a standalone plugin (for development purposes),
	 * otherwise (when loaded as a library from within the LifterLMS core plugin) the localization
	 * strings are included into the LifterLMS Core plugin's po/mo files and are localized by the LifterLMS
	 * core plugin.
	 *
	 * Files can be found in the following order (The first loaded file takes priority):
	 *   1. WP_LANG_DIR/lifterlms/lifterlms-blocks-LOCALE.mo
	 *   2. WP_LANG_DIR/plugins/lifterlms-blocks-LOCALE.mo
	 *   3. WP_CONTENT_DIR/plugins/lifterlms-blocks/i18n/lifterlms-blocks-LOCALE.mo
	 *
	 * Note: The function `load_plugin_textdomain()` is not used because the same textdomain as the LifterLMS core
	 * is used for this plugin but the file is named `lifterlms-blocks` in order to allow using a separate language
	 * file for each codebase.
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function load_textdomain() {

		// load locale.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'lifterlms' );

		// Load from the LifterLMS "safe" directory if it exists.
		load_textdomain( 'lifterlms', WP_LANG_DIR . '/lifterlms/lifterlms-blocks-' . $locale . '.mo' );

		// Load from the default plugins language file directory.
		load_textdomain( 'lifterlms', WP_LANG_DIR . '/plugins/lifterlms-blocks-' . $locale . '.mo' );

		// Load from the plugin's language file directory.
		load_textdomain( 'lifterlms', LLMS_BLOCKS_PLUGIN_DIR . '/i18n/lifterlms-blocks-' . $locale . '.mo' );

	}

	/**
	 * Remove deprecated core metaboxes.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Updated.
	 *
	 * @param string $post_type WP post type of the current post.
	 * @param string $post WP_Post.
	 * @return void
	 */
	public function remove_metaboxes( $post_type, $post ) {

		if ( ! llms_blocks_is_classic_enabled_for_post( $post ) ) {

			remove_meta_box( 'llms-instructors', 'course', 'normal' );
			remove_meta_box( 'llms-instructors', 'llms_membership', 'normal' );

		}

	}

}

return new LLMS_Blocks();
