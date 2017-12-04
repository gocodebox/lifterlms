<?php
/**
 * Single Course Tab: Students Subtab
 * @since    3.15.0
 * @version  3.15.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }


$table = new LLMS_Table_Course_Students();
$table->get_results();
echo $table->get_table_html();
