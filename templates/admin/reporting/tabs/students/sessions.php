<?php
/**
 * Single Student View: Sessions Tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;
is_admin() || exit;

$data   = new LLMS_Student_Session_data( $student->get( 'id' ) );
$period = llms_filter_input( INPUT_GET, 'period', FILTER_SANITIZE_STRING );
if ( ! $period ) {
	$period = 'today';
}
$data->set_period( $period );
$periods     = LLMS_Admin_Reporting::get_period_filters();
$period_text = strtolower( $periods[ $period ] );
?>

<?php do_action( 'llms_reporting_student_tab_sessions_stab_before_content' ); ?>

<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>
			<?php
			LLMS_Admin_Reporting::output_widget_range_filter(
				$period,
				'students',
				array(
					'stab'       => 'sessions',
					'student_id' => $student->get( 'id' ),
				)
			);
			?>
			<h3><?php _e( 'Sessions', 'lifterlms' ); ?></h3>
		</header>
		<?php

		do_action( 'llms_reporting_single_student_sessions_before_widgets', $student );

		LLMS_Admin_Reporting::output_widget(
			array(
				'icon'         => 'clock-o',
				'id'           => 'llms-reporting-student-sessions-total',
				'data'         => $data->get_sessions( 'current' ),
				'data_compare' => $data->get_sessions( 'previous' ),
				'text'         => sprintf( __( 'Sessions %s', 'lifterlms' ), $period_text ),
			)
		);

		do_action( 'llms_reporting_single_student_sessions_after_widgets', $student );
		?>

	</section>

	<aside class="llms-reporting-tab-side">

		<h3><i class="fa fa-bolt" aria-hidden="true"></i> <?php _e( 'Recent events', 'lifterlms' ); ?></h3>

		<?php foreach ( $student->get_events() as $event ) : ?>
			<?php LLMS_Admin_Reporting::output_event( $event, 'student' ); ?>
		<?php endforeach; ?>

	</aside>

</div>

<?php do_action( 'llms_reporting_student_tab_sessions_stab_after_content' ); ?>
