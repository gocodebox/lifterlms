<?php
/**
 * Single Membership Tab: Students Subtab.
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.32.0
 * @version 3.32.0
 */

defined( 'ABSPATH' ) || exit;
is_admin() || exit;

$table = new LLMS_Table_Membership_Students();
$table->get_results();
echo $table->get_table_html();
