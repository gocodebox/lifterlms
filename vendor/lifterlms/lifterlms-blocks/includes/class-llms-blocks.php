<?php
/**
 * Serverside block compononent registration
 *
 * @package  LifterLMS_Blocks/Classes
 * @since    1.0.0
 * @version  1.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Blocks class
 */
class LLMS_Blocks {

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 * @version  1.3.0
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'init' ) );

		add_action( 'add_meta_boxes', array( $this, 'remove_metaboxes' ), 999, 2 );

		add_filter( 'block_categories', array( $this, 'add_block_category' ) );

	}

	/**
	 * Add a custom LifterLMS block category
	 *
	 * @param   array $categories existing block cats.
	 * @return  array
	 * @since   1.0.0
	 * @version 1.0.0
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
	 * Register all blocks & components.
	 *
	 * @return  void
	 * @since   1.0.0
	 * @version 1.3.0
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
	 * @param string $post_type WP post type of the current post.
	 * @param string $post WP_Post.
	 * @return  void
	 * @since   1.0.0
	 * @version 1.3.0
	 */
	public function remove_metaboxes( $post_type, $post ) {

		if ( ! llms_blocks_is_classic_enabled_for_post( $post ) ) {

			remove_meta_box( 'llms-instructors', 'course', 'normal' );
			remove_meta_box( 'llms-instructors', 'llms_membership', 'normal' );

		}

	}

}

return new LLMS_Blocks();
