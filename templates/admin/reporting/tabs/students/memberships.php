<?php
/**
 * Single Student View: Memberships Tab
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

$table = new LLMS_Table_Student_Memberships();
$table->get_results(
	array(
		'student' => $student,
	)
);
echo $table->get_table_html();
