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
				'achievements' => __( 'Achievements', 'lifterlms' ),
			) );

			llms_get_template( 'admin/reporting/tabs/students/student.php', array(
				'current_tab' => isset( $_GET['stab'] ) ? esc_attr( $_GET['stab'] ) : 'information',
				'tabs' => $tabs,
				'student' => new LLMS_Student( intval( $_GET['student_id'] ) ),
			) );

		}
		// table
		else {

			llms_get_template( 'admin/reporting/tabs/students/students.php' );

		}

	}

}
return new LLMS_Admin_Reporting_Tab_Students();
