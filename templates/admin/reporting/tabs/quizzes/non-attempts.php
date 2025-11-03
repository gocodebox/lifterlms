<?php
/**
 * Single Quiz Tab: Non-Attempts Subtab
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

$table = new LLMS_Table_Quiz_Non_Attempts();
$table->get_results(
	array(
		'quiz_id' => llms_filter_input( INPUT_GET, 'quiz_id', FILTER_SANITIZE_NUMBER_INT ),
	)
);
$table->output_table_html();