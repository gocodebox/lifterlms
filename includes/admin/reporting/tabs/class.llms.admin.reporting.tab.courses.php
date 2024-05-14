<?php
/**
 * Courses Tab on Reporting Screen
 *
 * @package LifterLMS/Admin/Reporting/Tabs/Classes
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

		// Single student.
		if ( isset( $_GET['course_id'] ) ) {
			$course = llms_get_post( absint( $_GET['course_id'] ) );
			$links[ LLMS_Admin_Reporting::get_stab_url( 'overview' ) ] = $course->get( 'title' );
		}

		foreach ( $links as $url => $title ) {

			echo '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>';

		}
	}

	/**
	 * Output tab content
	 *
	 * @since 3.15.0
	 * @since 3.35.0 Sanitize input data.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	public function output() {

		// Single course.
		if ( isset( $_GET['course_id'] ) ) {

			if ( ! current_user_can( 'edit_post', llms_filter_input( INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT ) ) ) {
				wp_die( esc_html__( 'You do not have permission to access this content.', 'lifterlms' ) );
			}

			$tabs = apply_filters(
				'llms_reporting_tab_course_tabs',
				array(
					'overview' => __( 'Overview', 'lifterlms' ),
					'students' => __( 'Students', 'lifterlms' ),
				)
			);

			llms_get_template(
				'admin/reporting/tabs/courses/course.php',
				array(
					'current_tab' => isset( $_GET['stab'] ) ? esc_attr( llms_filter_input_sanitize_string( INPUT_GET, 'stab' ) ) : 'overview',
					'tabs'        => $tabs,
					'course'      => llms_get_post( intval( $_GET['course_id'] ) ),
				)
			);

		} else {

			$table = new LLMS_Table_Courses();
			$table->get_results();
			$table->output_table_html();

		}
	}
}
return new LLMS_Admin_Reporting_Tab_Courses();
