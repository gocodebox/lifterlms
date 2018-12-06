<?php
/**
 * Handle post migration to the block editor.
 *
 * @package  LifterLMS_Blocks/Classes
 * @since    1.0.0
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle post migration to the block editor.
 */
class LLMS_Blocks_Migrate {

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'migrate_post' ), 2 );

	}

	/**
	 * Migrate posts created prior to the block editor to have default LifterLMS templates
	 *
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function migrate_post() {

		global $pagenow, $post;

		if ( 'post.php' !== $pagenow || ! is_object( $post ) || ! in_array( $post->post_type, array( 'course', 'lesson' ), true ) ) {
			return;
		}

		// Already Migrated.
		if ( llms_parse_bool( get_post_meta( $post->ID, '_llms_blocks_migrated', true ) ) ) {
			return;
		}

		// Already Has blocks.
		if ( has_blocks( $post->post_content ) ) {
			update_post_meta( $post->ID, '_llms_blocks_migrated', 'yes' );
			return;
		}

		// Update the post.
		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_content' => $post->post_content . "\r\r" . $this->get_template( $post->post_type ),
				'meta_input'   => array(
					'_llms_blocks_migrated' => 'yes',
				),
			)
		);

		// Reload.
		wp_safe_redirect(
			add_query_arg(
				array(
					'post'   => $post->ID,
					'action' => 'edit',
				),
				admin_url( 'post.php' )
			)
		);
		exit;

	}

	/**
	 * Retrieve the block template by post type.
	 *
	 * @param   string $post_type wp post type.
	 * @return  string
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	private function get_template( $post_type ) {

		if ( 'course' === $post_type ) {
			ob_start();

			?><!-- wp:llms/course-information /-->

<!-- wp:llms/instructors /-->

<!-- wp:llms/pricing-table /-->

<!-- wp:llms/course-progress -->
<div class="wp-block-llms-course-progress">[lifterlms_course_progress]</div>
<!-- /wp:llms/course-progress -->

<!-- wp:llms/course-continue-button -->
<div class="wp-block-llms-course-continue-button" style="text-align:center">[lifterlms_course_continue_button]</div>
<!-- /wp:llms/course-continue-button -->

<!-- wp:llms/course-syllabus /-->
			<?php

			return ob_get_clean();

		}

		if ( 'lesson' === $post_type ) {
			ob_start();

			?>
			<!-- wp:llms/lesson-progression /-->

<!-- wp:llms/lesson-navigation /-->
			<?php

			return ob_get_clean();
		}

		return '';

	}

}

return new LLMS_Blocks_Migrate();
