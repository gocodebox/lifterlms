<?php
/**
 * Students Tab on Reporting Screen
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Reporting_Tab_Students {

	/**
	 * Constructor
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function __construct() {

		add_action( 'llms_reporting_content_students', array( $this, 'output' ) );
		add_action( 'llms_reporting_student_tab_breadcrumbs', array( $this, 'breadcrumbs' ) );

	}

	/**
	 * Add breadcrumb links to the tab depending on current view
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function breadcrumbs() {

		$links = array();

		// single student
		if ( isset( $_GET['student_id'] ) ) {
			$student = new LLMS_Student( absint( $_GET['student_id'] ) );
			$links[ LLMS_Admin_Reporting::get_stab_url( 'information' ) ] = $student->get_name();

			if ( isset( $_GET['stab'] ) && 'courses' === $_GET['stab'] ) {
				$links[ LLMS_Admin_Reporting::get_stab_url( 'courses' ) ] = __( 'All Courses', 'lifterlms' );

				if ( isset( $_GET['course_id'] ) ) {
					$url = LLMS_Admin_Reporting::get_current_tab_url( array(
						'stab' => 'courses',
						'student_id' => $_GET['student_id'],
						'course_id' => $_GET['course_id'],
					) );
					$links[ $url ] = get_the_title( $_GET['course_id'] );

					if ( isset( $_GET['quiz_id'] ) ) {
						$url = LLMS_Admin_Reporting::get_current_tab_url( array(
							'stab' => 'courses',
							'student_id' => $_GET['student_id'],
							'course_id' => $_GET['course_id'],
							'quiz_id' => $_GET['quiz_id'],
						) );
						$links[ $url ] = get_the_title( $_GET['quiz_id'] );

					}
				}
			}
		}

		foreach ( $links as $url => $title ) {

			echo '<a href="' . esc_url( $url ) . '">' . $title . '</a>';

		}

	}

	/**
	 * Output HTML for the current view within the students tab
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function output() {

		// single student
		if ( isset( $_GET['student_id'] ) ) {

			$tabs = apply_filters( 'llms_reporting_tab_student_tabs', array(
				'information' => __( 'Information', 'lifterlms' ),
				'courses' => __( 'Courses', 'lifterlms' ),
				'memberships' => __( 'Memberships', 'lifterlms' ),
				'achievements' => __( 'Achievements', 'lifterlms' ),
				'certificates' => __( 'Certificates', 'lifterlms' ),
			) );

			llms_get_template( 'admin/reporting/tabs/students/student.php', array(
				'current_tab' => isset( $_GET['stab'] ) ? esc_attr( $_GET['stab'] ) : 'information',
				'tabs' => $tabs,
				'student' => new LLMS_Student( intval( $_GET['student_id'] ) ),
			) );

		} // End if().
		else {

			llms_get_template( 'admin/reporting/tabs/students/students.php' );

		}

	}

}
return new LLMS_Admin_Reporting_Tab_Students();
