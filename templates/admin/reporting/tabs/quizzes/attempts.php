<?php
/**
 * Single Quiz Tab: Attempts Subtab
 * @since    3.16.0
 * @version  3.16.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

if ( isset( $_GET['attempt_id'] ) ) {

	llms_get_template( 'admin/reporting/tabs/quizzes/attempt.php', array(
		'attempt' => new LLMS_Quiz_Attempt( $_GET['attempt_id'] ),
	) );

} else {

	$table = new LLMS_Table_Quiz_Attempts();
	$table->get_results( array(
		'quiz_id' => absint( $_GET['quiz_id'] ),
	) );
	echo $table->get_table_html();

}
