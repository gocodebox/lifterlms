<?php
/**
 * Single Student View: Courses Tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.2.0
 * @since 3.35.0 Access `$_GET` data via `llms_filter_input()`.
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}

$course_id = llms_filter_input( INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT );

if ( empty( $course_id ) ) {

	$table = new LLMS_Table_Student_Courses();
	$table->get_results(
		array(
			'student' => $student,
		)
	);
	echo $table->get_table_html();

} else {

	$quiz_id   = llms_filter_input( INPUT_GET, 'quiz_id', FILTER_SANITIZE_NUMBER_INT );
	$lesson_id = llms_filter_input( INPUT_GET, 'lesson_id', FILTER_SANITIZE_NUMBER_INT );

	if ( $quiz_id && $lesson_id ) {

		$table = new LLMS_Table_Quiz_Attempts();
		$table->get_results(
			array(
				'quiz_id'    => $quiz_id,
				'student_id' => llms_filter_input( INPUT_GET, 'student_id', FILTER_SANITIZE_NUMBER_INT ),
			)
		);
		echo $table->get_table_html();

	} else {

		if ( ! current_user_can( 'edit_post', $course_id ) ) {
			wp_die( __( 'You do not have permission to access this content.', 'lifterlms' ) );
		}

		llms_get_template(
			'admin/reporting/tabs/students/courses-course.php',
			array(
				'student' => $student,
			)
		);

	}
}

