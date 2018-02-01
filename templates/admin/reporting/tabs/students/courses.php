<?php
/**
 * Single Student View: Courses Tab
 * This routes to the following templates based on present query vars
 * @since   3.2.0
 * @version 3.13.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

if ( empty( $_GET['course_id'] ) ) {

	$table = new LLMS_Table_Student_Courses();
	$table->get_results( array(
		'student' => $student,
	) );
	echo $table->get_table_html();

} elseif ( ! empty( $_GET['course_id'] ) ) {

	if ( ! empty( $_GET['quiz_id'] ) && ! empty( $_GET['lesson_id'] ) ) {

		$table = new LLMS_Table_Quiz_Attempts();
		$table->get_results( array(
			'quiz_id' => absint( $_GET['quiz_id'] ),
			'student_id' => absint( $_GET['student_id'] ),
		) );
		echo $table->get_table_html();

	} else {

		if ( ! current_user_can( 'edit_post', $_GET['course_id'] ) ) {
			wp_die( __( 'You do not have permission to access this content.', 'lifterlms' ) );
		}

		llms_get_template( 'admin/reporting/tabs/students/courses-course.php', array(
			'student' => $student,
		) );

	}
}

