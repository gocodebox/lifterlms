<?php
/**
 * Single Course Tab: Quizzes Subtab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 10.1.0
 * @version 10.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}

$table = new LLMS_Table_Course_Quizzes();
$table->get_results();
$table->output_table_html();
