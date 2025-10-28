<?php
/**
 * Single Student View: Quiz Attempts Tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since [version]
 * @version [version]
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