<?php
/**
 * Add, Customize, and Manage LifterLMS Course Post Table Columns
 *
 * @package LifterLMS/Admin/PostTypes/PostTables/Classes
 *
 * @since 3.3.0
 * @version 7.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Post_Table_Courses class
 *
 * @since 3.3.0
 * @since 3.24.0 Unknown.
 */
class LLMS_Admin_Post_Table_Courses {

	/**
	 * Constructor.
	 *
	 * @since 3.3.0
	 * @since 3.13.0 Unknown.
	 * @since 7.1.0 Added new custom columns.
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'post_row_actions', array( $this, 'add_links' ), 1, 2 );

		add_filter( 'manage_course_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_course_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

		add_filter( 'bulk_actions-edit-course', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-course', array( $this, 'handle_bulk_actions' ), 10, 3 );
	}

	/**
	 * Add course builder edit link
	 *
	 * @param    array $actions  existing actions
	 * @param    obj   $post     WP_Post object
	 * @since    3.13.0
	 * @version  3.13.1
	 */
	public function add_links( $actions, $post ) {

		if ( 'course' === $post->post_type && current_user_can( 'edit_course', $post->ID ) ) {

			$url = add_query_arg(
				array(
					'page'      => 'llms-course-builder',
					'course_id' => $post->ID,
				),
				admin_url( 'admin.php' )
			);

			$actions = array_merge(
				array(
					'llms-builder' => '<a href="' . esc_url( $url ) . '">' . __( 'Builder', 'lifterlms' ) . '</a>',
				),
				$actions
			);

		}

		return $actions;
	}

	/**
	 * Exports courses from the Bulk Actions menu on the courses post table
	 *
	 * @param    string $redirect_to  url to redirect to upon export completion (not used)
	 * @param    string $doaction     action name called
	 * @param    array  $post_ids     selected post ids
	 * @return   null
	 * @since    3.3.0
	 * @version  3.24.0
	 */
	public function handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {

		// Ensure it's our custom action.
		if ( 'llms_export' !== $doaction ) {
			return $redirect_to;
		}

		$data = array(
			'_generator' => 'LifterLMS/BulkCourseExporter',
			'_source'    => get_site_url(),
			'_version'   => llms()->version,
			'courses'    => array(),
		);

		foreach ( $post_ids as $post_id ) {

			$c                 = new LLMS_Course( $post_id );
			$data['courses'][] = $c->toArray();

		}

		$title = str_replace( ' ', '-', __( 'courses export', 'lifterlms' ) );
		$title = preg_replace( '/[^a-zA-Z0-9-]/', '', $title );

		$filename = apply_filters( 'llms_bulk_export_courses_filename', $title . '_' . current_time( 'Ymd' ), $this );

		header( 'Content-type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '.json"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo json_encode( $data );

		die;
	}

	/**
	 * Register bulk actions
	 *
	 * @since 3.3.0
	 *
	 * @param array $actions Existing bulk actions.
	 * @return string[]
	 */
	public function register_bulk_actions( $actions ) {

		$actions['llms_export'] = __( 'Export', 'lifterlms' );
		return $actions;
	}


	/**
	 * Add custom course columns.
	 *
	 * @since 7.1.0
	 *
	 * @param array $columns Array of default columns.
	 * @return array
	 */
	public function add_columns( $columns ) {

		// Add a new column for Lessons.
		$new_columns            = array();
		$new_columns['lessons'] = __( 'Lessons', 'lifterlms' );

		// Insert column into third position in existing columns array.
		$columns = array_merge( array_slice( $columns, 0, 3 ), $new_columns, array_slice( $columns, 3 ) );

		return $columns;
	}


	/**
	 * Manage content of custom course columns.
	 *
	 * @since 7.1.0
	 *
	 * @param string $column  Column key/name.
	 * @param int    $post_id WP Post ID of the course for the row.
	 * @return void
	 */
	public function manage_columns( $column, $post_id ) {

		if ( 'lessons' !== $column ) {
			return $column;
		}

		// Get a count of lessons in the course.
		$course       = llms_get_post( $post_id );
		$lesson_count = $course->get_lessons_count();

		if ( ! $lesson_count ) {
			echo '&ndash;';

			return;
		}

		// Build the URL to link to lesson post type filtered for course ID.
		$url = add_query_arg(
			array(
				'post_status'           => 'all',
				'post_type'             => 'lesson',
				'llms_filter_course_id' => $post_id,
			),
			admin_url( 'edit.php' )
		);

		// Translators: %d = Number of lessons in the specified course.
		$label = sprintf( _n( '%d Lesson', '%d Lessons', $lesson_count, 'lifterlms' ), $lesson_count );
		echo '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
	}
}

return new LLMS_Admin_Post_Table_Courses();
