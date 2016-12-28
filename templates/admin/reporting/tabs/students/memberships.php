<?php
/**
 * Single Student View: Memberships Tab
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

$table = new LLMS_Table_Student_Memberships();
$table->get_results( array(
	'student' => $student,
) );
echo $table->get_table_html();
