<?php
/**
 * Single Student View: Achievements Tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}

$table = new LLMS_Table_Achievements();
$table->get_results(
	array(
		'student' => $student,
	)
);
$table->output_table_html();
