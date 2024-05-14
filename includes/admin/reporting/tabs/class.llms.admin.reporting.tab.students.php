<?php
/**
 * LLMS_Admin_Reporting_Tab_Students class file
 *
 * @package LifterLMS/Admin/Reporting/Tabs/Classes
 *
 * @since 3.2.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Students Tab on Reporting Screen
 *
 * @since 3.2.0
 */
class LLMS_Admin_Reporting_Tab_Students {

	/**
	 * Constructor
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'llms_reporting_content_students', array( $this, 'output' ) );
		add_action( 'llms_reporting_student_tab_breadcrumbs', array( $this, 'breadcrumbs' ) );
	}

	/**
	 * Add breadcrumb links to the tab depending on current view
	 *
	 * @since 3.2.0
	 * @since 3.35.0 Sanitize input data.
	 *
	 * @return void
	 */
	public function breadcrumbs() {

		$links = array();

		// Single student.
		if ( isset( $_GET['student_id'] ) ) {
			$student = new LLMS_Student( absint( $_GET['student_id'] ) );
			$links[ LLMS_Admin_Reporting::get_stab_url( 'information' ) ] = $student->get_name();

			if ( isset( $_GET['stab'] ) && 'courses' === $_GET['stab'] ) {
				$links[ LLMS_Admin_Reporting::get_stab_url( 'courses' ) ] = __( 'All Courses', 'lifterlms' );

				if ( isset( $_GET['course_id'] ) ) {

					$course_id     = llms_filter_input( INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT );
					$student_id    = llms_filter_input( INPUT_GET, 'student_id', FILTER_SANITIZE_NUMBER_INT );
					$url           = LLMS_Admin_Reporting::get_current_tab_url(
						array(
							'stab'       => 'courses',
							'student_id' => $student_id,
							'course_id'  => $course_id,
						)
					);
					$links[ $url ] = get_the_title( $course_id );

					if ( isset( $_GET['quiz_id'] ) ) {
						$quiz_id       = llms_filter_input( INPUT_GET, 'quiz_id', FILTER_SANITIZE_NUMBER_INT );
						$url           = LLMS_Admin_Reporting::get_current_tab_url(
							array(
								'stab'       => 'courses',
								'student_id' => $student_id,
								'course_id'  => $course_id,
								'quiz_id'    => $quiz_id,
							)
						);
						$links[ $url ] = get_the_title( $quiz_id );

					}
				}
			}
		}

		foreach ( $links as $url => $title ) {

			echo '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>';

		}
	}

	/**
	 * Output HTML for the current view within the students tab
	 *
	 * @since 3.2.0
	 * @since 4.20.0 Added a report permission check and a user existence check.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return void
	 */
	public function output() {

		// Single student.
		if ( isset( $_GET['student_id'] ) ) {

			$student_id = llms_filter_input( INPUT_GET, 'student_id', FILTER_SANITIZE_NUMBER_INT );
			if ( ! llms_current_user_can( 'view_lifterlms_reports', $student_id ) ) {
				wp_die( esc_html__( "You do not have permission to access this student's reports", 'lifterlms' ) );
			}
			$student = llms_get_student( $student_id );
			if ( ! $student ) {
				wp_die( esc_html__( "This student doesn't exist.", 'lifterlms' ) );
			}

			$tabs = apply_filters(
				'llms_reporting_tab_student_tabs',
				array(
					'information'  => __( 'Information', 'lifterlms' ),
					'courses'      => __( 'Courses', 'lifterlms' ),
					'memberships'  => __( 'Memberships', 'lifterlms' ),
					'achievements' => __( 'Achievements', 'lifterlms' ),
					'certificates' => __( 'Certificates', 'lifterlms' ),
				)
			);

			llms_get_template(
				'admin/reporting/tabs/students/student.php',
				array(
					'current_tab' => isset( $_GET['stab'] ) ? esc_attr( llms_filter_input_sanitize_string( INPUT_GET, 'stab' ) ) : 'information',
					'tabs'        => $tabs,
					'student'     => $student,
				)
			);

		} else {

			llms_get_template( 'admin/reporting/tabs/students/students.php' );

		}
	}
}
return new LLMS_Admin_Reporting_Tab_Students();
