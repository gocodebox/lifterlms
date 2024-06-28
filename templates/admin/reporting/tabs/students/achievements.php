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
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in method.
echo $table->get_table_html();
