<?php
/**
 * Single Membership Tab: Overview Subtab.
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.32.0
 * @since 3.35.0 Access `$_GET` data via `llms_filter_input()`.
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;
is_admin() || exit;

$data   = new LLMS_Membership_Data( $membership->get( 'id' ) );
$period = llms_filter_input( INPUT_GET, 'period', FILTER_SANITIZE_STRING );
if ( ! $period ) {
	$period = 'today';
}
$data->set_period( $period );

$periods     = LLMS_Admin_Reporting::get_period_filters();
$period_text = strtolower( $periods[ $period ] );
$now         = current_time( 'timestamp' );
?>

<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>

			<?php
			LLMS_Admin_Reporting::output_widget_range_filter(
				$period,
				'memberships',
				array(
					'membership_id' => $membership->get( 'id' ),
				)
			);
			?>
			<h3><?php _e( 'Membership Overview', 'lifterlms' ); ?></h3>

		</header>
		<?php

		do_action( 'llms_reporting_single_membership_overview_before_widgets', $membership );

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols' => 'd-1of3',
				'icon' => 'users',
				'id'   => 'llms-reporting-membership-total-enrollments',
				'data' => $membership->get_student_count(),
				'text' => __( 'Currently enrolled students', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'         => 'd-1of3',
				'icon'         => 'shopping-cart',
				'id'           => 'llms-reporting-membership-orders',
				'data'         => $data->get_orders( 'current' ),
				'data_compare' => $data->get_orders( 'previous' ),
				'text'         => sprintf( __( 'New orders %s', 'lifterlms' ), $period_text ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'         => 'd-1of3',
				'icon'         => 'money',
				'id'           => 'llms-reporting-membership-revenue',
				'data'         => $data->get_revenue( 'current' ),
				'data_compare' => $data->get_revenue( 'previous' ),
				'data_type'    => 'monetary',
				'text'         => sprintf( __( 'Total sales %s', 'lifterlms' ), $period_text ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'icon'         => 'smile-o',
				'id'           => 'llms-reporting-membership-enrollments',
				'data'         => $data->get_enrollments( 'current' ),
				'data_compare' => $data->get_enrollments( 'previous' ),
				'text'         => sprintf( __( 'New enrollments %s', 'lifterlms' ), $period_text ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'icon'         => 'frown-o',
				'id'           => 'llms-reporting-membership-unenrollments',
				'data'         => $data->get_unenrollments( 'current' ),
				'data_compare' => $data->get_unenrollments( 'previous' ),
				'text'         => sprintf( __( 'Unenrollments %s', 'lifterlms' ), $period_text ),
				'impact'       => 'negative',
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'         => 'd-1of3',
				'icon'         => 'trophy',
				'id'           => 'llms-reporting-membership-achievements',
				'data'         => $data->get_engagements( 'achievement_earned', 'current' ),
				'data_compare' => $data->get_engagements( 'achievement_earned', 'previous' ),
				'text'         => sprintf( __( 'Achievements earned %s', 'lifterlms' ), $period_text ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'         => 'd-1of3',
				'icon'         => 'certificate',
				'id'           => 'llms-reporting-membership-certificates',
				'data'         => $data->get_engagements( 'certificate_earned', 'current' ),
				'data_compare' => $data->get_engagements( 'certificate_earned', 'previous' ),
				'text'         => sprintf( __( 'Certificates earned %s', 'lifterlms' ), $period_text ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'         => 'd-1of3',
				'icon'         => 'envelope',
				'id'           => 'llms-reporting-membership-email',
				'data'         => $data->get_engagements( 'email_sent', 'current' ),
				'data_compare' => $data->get_engagements( 'email_sent', 'previous' ),
				'text'         => sprintf( __( 'Emails sent %s', 'lifterlms' ), $period_text ),
			)
		);

		do_action( 'llms_reporting_single_membership_overview_after_widgets', $membership );
		?>

	</section>

	<aside class="llms-reporting-tab-side">

		<h3><i class="fa fa-bolt" aria-hidden="true"></i> <?php _e( 'Recent events', 'lifterlms' ); ?></h3>

		<?php foreach ( $data->recent_events() as $event ) : ?>
			<?php LLMS_Admin_Reporting::output_event( $event, 'membership' ); ?>
		<?php endforeach; ?>

	</aside>

</div>
