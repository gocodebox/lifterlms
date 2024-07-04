<?php
/**
 * Single Quiz Tab: Overview Subtab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.16.0
 * @since 3.35.0 Access `$_GET` data via `llms_filter_input()`.
 * @since 4.10.1 Remove unneded require of the file LLMS_PLUGIN_DIR . 'includes/class.llms.quiz.data.php', the autoloader will do the job.
 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING` and validate the period exists before attempting to use it.
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;
is_admin() || exit;

$data   = new LLMS_Quiz_Data( $quiz->get( 'id' ) );
$period = $data->parse_period();
$data->set_period( $period );
$period_text = strtolower( LLMS_Admin_Reporting::get_period_filters()[ $period ] );
$now         = current_time( 'timestamp' );
?>

<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>

			<?php
			LLMS_Admin_Reporting::output_widget_range_filter(
				$period,
				'quizzes',
				array(
					'quiz_id' => $quiz->get( 'id' ),
				)
			);
			?>
			<h3><?php esc_html_e( 'Quiz Overview', 'lifterlms' ); ?></h3>

		</header>
		<?php

		do_action( 'llms_reporting_single_quiz_overview_before_widgets', $quiz );

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'         => 'd-1of2',
				'icon'         => 'users',
				'id'           => 'llms-reporting-quiz-total-attempts',
				'data'         => $data->get_attempt_count( 'current' ),
				'data_compare' => $data->get_attempt_count( 'previous' ),
				'text'         => sprintf( __( 'Attempts %s', 'lifterlms' ), $period_text ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'         => 'd-1of2',
				'icon'         => 'graduation-cap',
				'id'           => 'llms-reporting-quiz-avg-grade',
				'data'         => $data->get_average_grade( 'current' ),
				'data_compare' => $data->get_average_grade( 'previous' ),
				'data_type'    => 'percentage',
				'text'         => sprintf( __( 'Average grade %s', 'lifterlms' ), $period_text ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'icon'         => 'check-circle',
				'id'           => 'llms-reporting-quiz-passes',
				'data'         => $data->get_pass_count( 'current' ),
				'data_compare' => $data->get_pass_count( 'previous' ),
				'text'         => sprintf( __( 'Passed attempts %s', 'lifterlms' ), $period_text ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'icon'         => 'times-circle',
				'id'           => 'llms-reporting-quiz-fails',
				'data'         => $data->get_fail_count( 'current' ),
				'data_compare' => $data->get_fail_count( 'previous' ),
				'text'         => sprintf( __( 'Failed attempts %s', 'lifterlms' ), $period_text ),
				'impact'       => 'negative',
			)
		);

		do_action( 'llms_reporting_single_quiz_overview_after_widgets', $quiz );
		?>

	</section>

	<aside class="llms-reporting-tab-side">

		<h3><i class="fa fa-bolt" aria-hidden="true"></i> <?php esc_html_e( 'Recent events', 'lifterlms' ); ?></h3>

		<em><?php esc_html_e( 'Quiz events coming soon...', 'lifterlms' ); ?></em>

	</aside>

</div>
