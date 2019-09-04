<?php
/**
 * Single Quiz Tab: Attempts Subtab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.16.0
 * @since 3.35.0 Access `$_GET` data via `llms_filter_input()`.
 * @version  3.16.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}

if ( isset( $_GET['attempt_id'] ) ) {

	llms_get_template(
		'admin/reporting/tabs/quizzes/attempt.php',
		array(
			'attempt' => new LLMS_Quiz_Attempt( llms_filter_input( INPUT_GET, 'attempt_id', FILTER_SANITIZE_NUMBER_INT ) ),
		)
	);

} else {

	$table = new LLMS_Table_Quiz_Attempts();
	$table->get_results(
		array(
			'quiz_id' => llms_filter_input( INPUT_GET, 'quiz_id', FILTER_SANITIZE_NUMBER_INT ),
		)
	);
	echo $table->get_table_html();

}
