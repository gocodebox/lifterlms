<?php
/**
 * Single Quiz Tab: Non-Attempts Subtab
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

// Display link back to regular attempts view
$quiz_id = llms_filter_input( INPUT_GET, 'quiz_id', FILTER_SANITIZE_NUMBER_INT );
$attempts_url = LLMS_Admin_Reporting::get_current_tab_url(
	array(
		'tab'    => 'quizzes',
		'stab'   => 'attempts',
		'quiz_id' => $quiz_id,
	)
);

echo '<div style="margin-bottom: 20px;">';
echo '<a href="' . esc_url( $attempts_url ) . '" class="button button-secondary">';
echo esc_html__( '← Back to Quiz Attempts', 'lifterlms' );
echo '</a>';
echo '</div>';

$table = new LLMS_Table_Quiz_Non_Attempts();
$table->get_results(
	array(
		'quiz_id' => $quiz_id,
	)
);
$table->output_table_html();