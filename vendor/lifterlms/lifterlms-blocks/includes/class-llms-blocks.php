<?php
/**
 * Plugin Initialization.
 *
 * @package LifterLMS_Blocks/Classes
 *
 * @since 1.0.0
 * @version 1.5.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Blocks class
 *
 * @since 1.0.0
 * @since 1.4.0 Add status tools class.
 * @since 1.5.1 Output
 */
class LLMS_Blocks {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Updated.
	 * @since 1.5.1 Add `admin_print_scripts` hook to handle outputting dynamic block information.
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'add_meta_boxes', array( $this, 'remove_metaboxes' ), 999, 2 );
		add_filter( 'block_categories', array( $this, 'add_block_category' ) );
		add_action( 'admin_print_scripts', array( $this, 'admin_print_scripts' ), 15 );

	}

	/**
	 * Add a custom LifterLMS block category
	 *
	 * @since 1.0.0
	 *
	 * @param array $categories existing block cats.
	 * @return array
	 */
	public function add_block_category( $categories ) {
		$categories[] = array(
			'slug'  => 'llms-blocks',
			'title' => sprintf(
				// Translators: %1$s = LifterLMS.
				__( '%1$s Blocks', 'lifterlms' ),
				'LifterLMS'
			),
		);
		return $categories;
	}

	/**
	 * Print dynamic block information as a JS variable
	 *
	 * Allows us to ensure we only add visibility attributes to static blocks.
	 * Prevents an issue causing rest api validation issues during attribute validation
	 * because it's impossible to register custom attributes on a block.
	 *
	 * @link https://github.com/gocodebox/lifterlms-blocks/issues/30
	 *
	 * @since 1.5.1
	 *
	 * @return void
	 */
	public function admin_print_scripts() {

		$screen = get_current_screen();
		if ( ! $screen || 'post' !== $screen->base ) {
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
	 * Register all blocks & components.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Add status tools class.
	 *
	 * @return  void
	 */
	public function init() {

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
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-status-tools.php';

		// Block Visibility Component.
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks-visibility.php';

		// Dynamic Blocks.
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-course-information-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-course-syllabus-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-instructors-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-lesson-navigation-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-lesson-progression-block.php';
		require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/blocks/class-llms-blocks-pricing-table-block.php';

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
