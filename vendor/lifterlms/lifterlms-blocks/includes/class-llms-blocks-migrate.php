<?php
/**
 * Handle post migration to the block editor.
 *
 * @package LifterLMS_Blocks/Classes
 *
 * @since 1.0.0
 * @version 1.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handle post migration to the block editor.
 *
 * @since 1.0.0
 * @since 1.4.0 Add post unmigrator, improve migration script.
 */
class LLMS_Blocks_Migrate {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Add action for unmigrating posts from the Tools & Utilities status screen.
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'migrate_post' ), 2 );
		add_action( 'wp', array( $this, 'remove_template_hooks' ) );
		add_action( 'llms_blocks_unmigrate_posts', array( $this, 'unmigrate_posts' ) );

		add_filter( 'llms_blocks_is_post_migrated', array( __CLASS__, 'check_sales_page' ), 15, 2 );

	}

	/**
	 * Add a template to a post & save migration status in postmeta.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_Post $post Post object.
	 * @return bool
	 */
	private function add_template_to_post( $post ) {

		// Update the post.
		if ( $this->update_post_content( $post->ID, $post->post_content . "\r\r" . $this->get_template( $post->post_type ) ) ) {
			// Save migration state.
			$this->update_migration_status( $post->ID );
			return true;
		}

		return false;

	}

	/**
	 * Don't remove core template actions when a sales page is enabled and the page is restricted.
	 *
	 * @param   bool $ret Default migration status.
	 * @param   int  $post_id WP_Post ID.
	 * @return  bool
	 * @since   1.2.0
	 * @version 1.3.1
	 */
	public static function check_sales_page( $ret, $post_id ) {

		$page_restricted = llms_page_restricted( $post_id );
		if ( $page_restricted['is_restricted'] ) {
			$sales_page = get_post_meta( $post_id, '_llms_sales_page_content_type', true );
			if ( '' === $sales_page || 'content' === $sales_page ) {
				$ret = false;
			}
		}

		return $ret;

	}

	/**
	 * Get an array of post types which can be migrated.
	 *
	 * @return  array
	 * @since   1.3.3
	 * @version 1.3.3
	 */
	public function get_migrateable_post_types() {
		return apply_filters( 'llms_blocks_migrateable_post_types', array( 'course', 'lesson' ) );
	}

	/**
	 * Retrieve a WP_Query for posts which have already been migrated.
	 *
	 * @since 1.4.0
	 *
	 * @param array $args Optional query arguments to pass to WP_Query.
	 * @return WP_Query
	 */
	public function get_migrated_posts( $args = array() ) {

		return new WP_Query(
			wp_parse_args(
				$args,
				array(
					'post_type'  => $this->get_migrateable_post_types(),
					'meta_query' => array(
						array(
							'key'   => '_llms_blocks_migrated',
							'value' => 'yes',
						),
					),
				)
			)
		);

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

	/**
	 * Migrate posts created prior to the block editor to have default LifterLMS templates
	 *
	 * @since 1.0.0
	 * @since 1.4.0 Moves content updating methods to it's own function.
	 *
	 * @return  void
	 */
	public function migrate_post() {

		global $pagenow, $post;

		if ( 'post.php' !== $pagenow || ! is_object( $post ) ) {
			return;
		}

		if ( ! $this->should_migrate_post( $post->ID ) ) {
			return;
		}

		// Already Has blocks.
		if ( has_blocks( $post->post_content ) ) {
			$this->update_migration_status( $post->ID );
			return;
		}

		$this->add_template_to_post( $post );

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
	 * Remove post type templates and any LifterLMS Blocks from a given post.
	 *
	 * @since 1.4.0
	 *
	 * @param WP_Post $post Post object.
	 * @return bool
	 */
	private function remove_template_from_post( $post ) {

		$template = $this->get_template( $post->post_type );
		if ( ! $template ) {
			return;
		}

		// explicitly remove template pieces.
		$parts   = array_filter( array_map( 'trim', explode( "\n", $template ) ) );
		$content = str_replace( $parts, '', $post->post_content );

		// replace any remaining LLMS blocks not found in the template (also grabs any openers that have block settings JSON).
		$content = trim( preg_replace( '#<!-- \/?wp:llms\/.* \/?-->#', '', $content ) );

		if ( $this->update_post_content( $post->ID, $content ) ) {
			$this->update_migration_status( $post->ID, 'no' );
			return true;
		}

		return false;

	}

	/**
	 * Removes core template action hooks from posts which have been migrated to the block editor
	 *
	 * @return  void
	 * @since   1.1.0
	 * @version 1.3.2
	 */
	public function remove_template_hooks() {

		if ( ! llms_blocks_is_post_migrated( get_the_ID() ) ) {
			return;
		}

		// Course Information.
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_meta_wrapper_start', 5 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_length', 10 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_difficulty', 20 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_tracks', 25 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_categories', 30 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_tags', 35 );
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_meta_wrapper_end', 50 );

		// Remove Course Progress.
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_course_progress', 60 );

		// Course Syllabus.
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_single_syllabus', 90 );

		// Instructors.
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_course_author', 40 );

		// Lesson Navigation.
		remove_action( 'lifterlms_single_lesson_after_summary', 'lifterlms_template_lesson_navigation', 20 );

		// Lesson Progression.
		remove_action( 'lifterlms_single_lesson_after_summary', 'lifterlms_template_complete_lesson_link', 10 );

		// Pricing Table.
		remove_action( 'lifterlms_single_course_after_summary', 'lifterlms_template_pricing_table', 60 );
		remove_action( 'lifterlms_single_membership_after_summary', 'lifterlms_template_pricing_table', 10 );

	}

	/**
	 * Determine if a post should be migrated.
	 *
	 * @param   int $post_id WP_Post ID.
	 * @return  bool
	 * @since   1.3.3
	 * @version 1.3.3
	 */
	public function should_migrate_post( $post_id ) {

		$ret = true;

		// Not a valid post type.
		if ( ! in_array( get_post_type( $post_id ), $this->get_migrateable_post_types(), true ) ) {

			$ret = false;

			// Classic is enabled, don't migrate.
		} elseif ( llms_blocks_is_classic_enabled_for_post( $post_id ) ) {

			$ret = false;

			// Already Migrated.
		} elseif ( llms_parse_bool( get_post_meta( $post_id, '_llms_blocks_migrated', true ) ) ) {

			$ret = false;

		}

		return apply_filters( 'llms_blocks_should_migrate_post', $ret, $post_id );

	}

	/**
	 * Unmigrates migrated posts.
	 *
	 * @since 1.4.0
	 *
	 * @return void.
	 */
	public function unmigrate_posts() {

		$posts = $this->get_migrated_posts( array( 'posts_per_page' => 250 ) );

		if ( $posts->posts ) {
			foreach ( $posts->posts as $post ) {
				$this->remove_template_from_post( $post );
			}
		}

	}

	/**
	 * Update post meta data to signal status of the editor migration.
	 *
	 * @param   int    $post_id WP_Post ID.
	 * @param   string $status  Yes or no.
	 * @return  void
	 * @since   1.1.0
	 * @version 1.1.0
	 */
	private function update_migration_status( $post_id, $status = 'yes' ) {
		update_post_meta( $post_id, '_llms_blocks_migrated', $status );
	}

	/**
	 * Update the post content for a given post.
	 *
	 * @since 1.4.0
	 *
	 * @param int    $id WP_Post ID.
	 * @param string $content Post content to update.
	 * @return bool
	 */
	private function update_post_content( $id, $content ) {

		global $wpdb;
		$update = $wpdb->update(
			$wpdb->posts,
			array(
				'post_content' => $content,
			),
			array(
				'ID' => $id,
			),
			array( '%s' ),
			array( '%d' )
		);

		return false === $update ? false : true;

	}

}

global $llms_blocks_migrate;
$llms_blocks_migrate = new LLMS_Blocks_Migrate();
return $llms_blocks_migrate;
