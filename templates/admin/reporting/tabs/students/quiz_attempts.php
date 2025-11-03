<?php
/**
 * Single Student View: Quiz Attempts Tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 9.1.0
 * @version 9.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}

$table = new LLMS_Table_Student_Quiz_Attempts();
$table->get_results(
	array(
		'student_id' => $student->get_id(),
	)
);
$table->output_table_html();