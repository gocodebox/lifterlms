<?php
/**
 * Courses Tab on Reporting Screen
 *
 * @since 3.15.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Reporting_Tab_Courses
 *
 * @since 3.15.0
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Admin_Reporting_Tab_Courses {

	/**
	 * Constructor
	 *
	 * @return   void
	 * @since    3.15.0
	 */
	public function __construct() {

		add_action( 'llms_reporting_content_courses', array( $this, 'output' ) );
		add_action( 'llms_reporting_course_tab_breadcrumbs', array( $this, 'breadcrumbs' ) );

	}

	/**
	 * Add breadcrumb links to the tab depending on current view
	 *
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function breadcrumbs() {

		$links = array();

		// single student
		if ( isset( $_GET['course_id'] ) ) {
			$course = llms_get_post( absint( $_GET['course_id'] ) );
			$links[ LLMS_Admin_Reporting::get_stab_url( 'overview' ) ] = $course->get( 'title' );

			// if ( isset( $_GET['stab'] ) && 'courses' === $_GET['stab'] ) {
			// $links[ LLMS_Admin_Reporting::get_stab_url( 'courses' ) ] = __( 'All Courses', 'lifterlms' );

			// if ( isset( $_GET['course_id'] ) ) {
			// $url = LLMS_Admin_Reporting::get_current_tab_url( array(
			// 'stab' => 'courses',
			// 'course_id' => $_GET['course_id'],
			// 'course_id' => $_GET['course_id'],
			// ) );
			// $links[ $url ] = get_the_title( $_GET['course_id'] );

			// if ( isset( $_GET['quiz_id'] ) ) {
			// $url = LLMS_Admin_Reporting::get_current_tab_url( array(
			// 'stab' => 'courses',
			// 'course_id' => $_GET['course_id'],
			// 'course_id' => $_GET['course_id'],
			// 'quiz_id' => $_GET['quiz_id'],
			// ) );
			// $links[ $url ] = get_the_title( $_GET['quiz_id'] );

			// }

			// }
			// }
		}

		foreach ( $links as $url => $title ) {

			echo '<a href="' . esc_url( $url ) . '">' . $title . '</a>';

		}

	}

	/**
	 * Output tab content
	 *
	 * @since 3.15.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return void
	 */
	public function output() {

		// single course
		if ( isset( $_GET['course_id'] ) ) {

			if ( ! current_user_can( 'edit_post', llms_filter_input( INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT ) ) ) {
				wp_die( __( 'You do not have permission to access this content.', 'lifterlms' ) );
			}

			$tabs = apply_filters(
				'llms_reporting_tab_course_tabs',
				array(
					'overview' => __( 'Overview', 'lifterlms' ),
					'students' => __( 'Students', 'lifterlms' ),
				// 'quizzes' => __( 'Quizzes', 'lifterlms' ),
				)
			);

			llms_get_template(
				'admin/reporting/tabs/courses/course.php',
				array(
					'current_tab' => isset( $_GET['stab'] ) ? esc_attr( llms_filter_input( INPUT_GET, 'stab', FILTER_SANITIZE_STRING ) ) : 'overview',
					'tabs'        => $tabs,
					'course'      => llms_get_post( intval( $_GET['course_id'] ) ),
				)
			);

		} else {

			$table = new LLMS_Table_Courses();
			$table->get_results();
			echo $table->get_table_html();

		}

	}

}
return new LLMS_Admin_Reporting_Tab_Courses();
