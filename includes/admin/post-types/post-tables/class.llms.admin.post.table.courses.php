<?php
defined( 'ABSPATH' ) || exit;

/**
 * Add, Customize, and Manage LifterLMS Course
 * @since    3.3.0
 * @version  3.24.0
 */
class LLMS_Admin_Post_Table_Courses {

	/**
	 * Constructor
	 * @return  void
	 * @since    3.3.0
	 * @version  3.13.0
	 */
	public function __construct() {

		add_filter( 'post_row_actions', array( $this, 'add_links' ), 1, 2 );

		add_filter( 'bulk_actions-edit-course', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-course', array( $this, 'handle_bulk_actions' ), 10, 3 );

	}

	/**
	 * Add course builder edit link
	 * @param    array     $actions  existing actions
	 * @param    obj       $post     WP_Post object
	 * @since    3.13.0
	 * @version  3.13.1
	 */
	public function add_links( $actions, $post ) {

		if ( 'course' === $post->post_type && current_user_can( 'edit_course', $post->ID ) ) {

			$url = add_query_arg( array(
				'page' => 'llms-course-builder',
				'course_id' => $post->ID,
			), admin_url( 'admin.php' ) );

			$actions = array_merge( array(
				'llms-builder' => '<a href="' . esc_url( $url ) . '">' . __( 'Builder', 'lifterlms' ) . '</a>',
			), $actions );

		}

		return $actions;

	}

	/**
	 * Exports courses from the Bulk Actions menu on the courses post table
	 * @param    string     $redirect_to  url to redirect to upon export comletion (not used)
	 * @param    string     $doaction     action name called
	 * @param    array      $post_ids     selected post ids
	 * @return   void
	 * @since    3.3.0
	 * @version  3.24.0
	 */
	public function handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {

		// ensure it's our custom action
		if ( 'llms_export' !== $doaction ) {
			return $redirect_to;
		}

		$data = array(
			'_generator' => 'LifterLMS/BulkCourseExporter',
			'_source' => get_site_url(),
			'_version' => LLMS()->version,
			'courses' => array(),
		);

		foreach ( $post_ids as $post_id ) {

			$c = new LLMS_Course( $post_id );
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
	 * @param    array     $actions  existing bulk actions
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	function register_bulk_actions( $actions ) {

		$actions['llms_export'] = __( 'Export', 'lifterlms' );
		return $actions;

	}

}

return new LLMS_Admin_Post_Table_Courses();
