<?php
/**
 * Single Student View: Achievements Tab
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

$table = new LLMS_Table_Achievements();
$table->get_results( array(
	'student' => $student,
) );
echo $table->get_table_html();
