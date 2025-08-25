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

	// Display link to non-attempts view
	$quiz_id = llms_filter_input( INPUT_GET, 'quiz_id', FILTER_SANITIZE_NUMBER_INT );
	$non_attempts_url = LLMS_Admin_Reporting::get_current_tab_url(
		array(
			'tab'    => 'quizzes',
			'stab'   => 'non-attempts',
			'quiz_id' => $quiz_id,
		)
	);
	
	echo '<div style="margin-bottom: 20px;">';
	echo '<a href="' . esc_url( $non_attempts_url ) . '" class="button button-secondary">';
	echo esc_html__( 'View Students Without Attempts', 'lifterlms' );
	echo '</a>';
	echo '</div>';

	$table = new LLMS_Table_Quiz_Attempts();
	$table->get_results(
		array(
			'quiz_id' => $quiz_id,
		)
	);
	$table->output_table_html();

}
