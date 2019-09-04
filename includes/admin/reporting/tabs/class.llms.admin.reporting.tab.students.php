<?php
/**
 * Students Tab on Reporting Screen
 *
 * @since  3.2.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 *
 * @since  3.2.0
 * @since 3.35.0 Sanitize input data.
 */
class LLMS_Admin_Reporting_Tab_Students {

	/**
	 * Constructor
	 *
	 * @since    3.2.0
	 * @version  3.2.0
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
	 * @return   void
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

			echo '<a href="' . esc_url( $url ) . '">' . $title . '</a>';

		}

	}

	/**
	 * Output HTML for the current view within the students tab
	 *
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function output() {

		// single student
		if ( isset( $_GET['student_id'] ) ) {

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
					'current_tab' => isset( $_GET['stab'] ) ? esc_attr( llms_filter_input( INPUT_GET, 'stab', FILTER_SANITIZE_STRING ) ) : 'information',
					'tabs'        => $tabs,
					'student'     => new LLMS_Student( intval( $_GET['student_id'] ) ),
				)
			);

		} else {

			llms_get_template( 'admin/reporting/tabs/students/students.php' );

		}

	}

}
return new LLMS_Admin_Reporting_Tab_Students();
