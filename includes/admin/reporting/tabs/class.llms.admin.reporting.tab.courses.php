<?php
/**
 * Courses Tab on Reporting Screen
 * @since    ??
 * @version  ??
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Reporting_Tab_Courses {

	/**
	 * Constructor
	 * @return   void
	 * @since    ??
	 * @version  ??
	 */
	public function __construct() {

		add_action( 'llms_reporting_content_courses', array( $this, 'output' ) );

	}

	/**
	 * Output tab content
	 * @return   void
	 * @since    ??
	 * @version  ??
	 */
	public function output() {

		// single course
		if ( isset( $_GET['course_id'] ) ) {

			echo $_GET['course_id'];

		} // courses table
		else {

			$table = new LLMS_Table_Courses();
			$table->get_results();
			echo $table->get_table_html();

		}

	}

}
return new LLMS_Admin_Reporting_Tab_Courses();
