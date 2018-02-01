<?php
/**
 * Single Quiz Tab: Overview Subtab
 * @since    3.16.0
 * @version  3.16.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

include LLMS_PLUGIN_DIR . 'includes/class.llms.quiz.data.php';

$data = new LLMS_Quiz_Data( $quiz->get( 'id' ) );
$period = isset( $_GET['period'] ) ? $_GET['period'] : 'today';
$data->set_period( $period );
$periods = LLMS_Admin_Reporting::get_period_filters();
$period_text = strtolower( $periods[ $period ] );
$now = current_time( 'timestamp' );
?>

<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>

			<?php LLMS_Admin_Reporting::output_widget_range_filter( $period, 'quizzes', array(
				'quiz_id' => $quiz->get( 'id' ),
			) ); ?>
			<h3><?php _e( 'Quiz Overview', 'lifterlms' ); ?></h3>

		</header><?php

		do_action( 'llms_reporting_single_quiz_overview_before_widgets', $quiz );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of2',
			'icon' => 'users',
			'id' => 'llms-reporting-quiz-total-attempts',
			'data' => $data->get_attempt_count( 'current' ),
			'data_compare' => $data->get_attempt_count( 'previous' ),
			'text' => sprintf( __( 'Attempts %s', 'lifterlms' ), $period_text ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of2',
			'icon' => 'graduation-cap',
			'id' => 'llms-reporting-quiz-avg-grade',
			'data' => $data->get_average_grade( 'current' ),
			'data_compare' => $data->get_average_grade( 'previous' ),
			'data_type' => 'percentage',
			'text' => sprintf( __( 'Average grade %s', 'lifterlms' ), $period_text ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'icon' => 'check-circle',
			'id' => 'llms-reporting-quiz-passes',
			'data' => $data->get_pass_count( 'current' ),
			'data_compare' => $data->get_pass_count( 'previous' ),
			'text' => sprintf( __( 'Passed attempts %s', 'lifterlms' ), $period_text ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'icon' => 'times-circle',
			'id' => 'llms-reporting-quiz-fails',
			'data' => $data->get_fail_count( 'current' ),
			'data_compare' => $data->get_fail_count( 'previous' ),
			'text' => sprintf( __( 'Failed attempts %s', 'lifterlms' ), $period_text ),
			'impact' => 'negative',
		) );

		do_action( 'llms_reporting_single_quiz_overview_after_widgets', $quiz ); ?>

	</section>

	<aside class="llms-reporting-tab-side">

		<h3><i class="fa fa-bolt" aria-hidden="true"></i> <?php _e( 'Recent events', 'lifterlms' ); ?></h3>

		<em><?php _e( 'Quiz events coming soon...', 'lifterlms' ); ?></em>

		<?php // foreach ( $data->recent_events() as $event ) : ?>
			<?php //LLMS_Admin_Reporting::output_event( $event, 'quiz' ); ?>
		<?php //endforeach; ?>

	</aside>

</div>
