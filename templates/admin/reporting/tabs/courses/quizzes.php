<?php
/**
 * Single Course Tab: Quizzes Subtab
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

$table = new LLMS_Table_Course_Quizzes();
$table->get_results();
$table->output_table_html();
