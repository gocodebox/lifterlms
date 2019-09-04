<?php
/**
 * Students Table
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

$table = new LLMS_Table_Students();
$table->get_results();
echo $table->get_table_html();
