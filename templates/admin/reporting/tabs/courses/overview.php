<?php
/**
 * Single Course Tab: Overview Subtab
 * @since    3.15.0
 * @version  3.17.2
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

$data = new LLMS_Course_Data( $course->get( 'id' ) );
$period = isset( $_GET['period'] ) ? $_GET['period'] : 'today';
$data->set_period( $period );
$periods = LLMS_Admin_Reporting::get_period_filters();
$period_text = strtolower( $periods[ $period ] );
$now = current_time( 'timestamp' );
?>

<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>

			<?php LLMS_Admin_Reporting::output_widget_range_filter( $period, 'courses', array(
				'course_id' => $course->get( 'id' ),
			) ); ?>
			<h3><?php _e( 'Course Overview', 'lifterlms' ); ?></h3>

		</header><?php

		do_action( 'llms_reporting_single_course_overview_before_widgets', $course );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of3',
			'icon' => 'users',
			'id' => 'llms-reporting-course-total-enrollments',
			'data' => $course->get_student_count(),
			'text' => __( 'Currently enrolled students', 'lifterlms' ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of3',
			'icon' => 'line-chart',
			'id' => 'llms-reporting-course-avg-progress',
			'data' => $course->get( 'average_progress' ),
			'data_type' => 'percentage',
			'text' => __( 'Current average progress', 'lifterlms' ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of3',
			'icon' => 'graduation-cap',
			'id' => 'llms-reporting-course-avg-grade',
			'data' => $course->get( 'average_grade' ),
			'data_type' => 'percentage',
			'text' => __( 'Current average grade', 'lifterlms' ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'icon' => 'shopping-cart',
			'id' => 'llms-reporting-course-orders',
			'data' => $data->get_orders( 'current' ),
			'data_compare' => $data->get_orders( 'previous' ),
			'text' => sprintf( __( 'New orders %s', 'lifterlms' ), $period_text ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'icon' => 'money',
			'id' => 'llms-reporting-course-revenue',
			'data' => $data->get_revenue( 'current' ),
			'data_compare' => $data->get_revenue( 'previous' ),
			'data_type' => 'monetary',
			'text' => sprintf( __( 'Total sales %s', 'lifterlms' ), $period_text ),
		) );


		LLMS_Admin_Reporting::output_widget( array(
			'icon' => 'smile-o',
			'id' => 'llms-reporting-course-enrollments',
			'data' => $data->get_enrollments( 'current' ),
			'data_compare' => $data->get_enrollments( 'previous' ),
			'text' => sprintf( __( 'New enrollments %s', 'lifterlms' ), $period_text ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'icon' => 'frown-o',
			'id' => 'llms-reporting-course-unenrollments',
			'data' => $data->get_unenrollments( 'current' ),
			'data_compare' => $data->get_unenrollments( 'previous' ),
			'text' => sprintf( __( 'Unenrollments %s', 'lifterlms' ), $period_text ),
			'impact' => 'negative',
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'icon' => 'check-circle',
			'id' => 'llms-reporting-course-lessons-completed',
			'data' => $data->get_lesson_completions( 'current' ),
			'data_compare' => $data->get_lesson_completions( 'previous' ),
			'text' => sprintf( __( 'Lessons completed %s', 'lifterlms' ), $period_text ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'icon' => 'flag-checkered',
			'id' => 'llms-reporting-course-course-completions',
			'data' => $data->get_completions( 'current' ),
			'data_compare' => $data->get_completions( 'previous' ),
			'text' => sprintf( __( 'Course completions %s', 'lifterlms' ), $period_text ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of3',
			'icon' => 'trophy',
			'id' => 'llms-reporting-course-achievements',
			'data' => $data->get_engagements( 'achievement_earned', 'current' ),
			'data_compare' => $data->get_engagements( 'achievement_earned', 'previous' ),
			'text' => sprintf( __( 'Achievements earned %s', 'lifterlms' ), $period_text ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of3',
			'icon' => 'certificate',
			'id' => 'llms-reporting-course-certificates',
			'data' => $data->get_engagements( 'certificate_earned', 'current' ),
			'data_compare' => $data->get_engagements( 'certificate_earned', 'previous' ),
			'text' => sprintf( __( 'Certificates earned %s', 'lifterlms' ), $period_text ),
		) );

		LLMS_Admin_Reporting::output_widget( array(
			'cols' => 'd-1of3',
			'icon' => 'envelope',
			'id' => 'llms-reporting-course-email',
			'data' => $data->get_engagements( 'email_sent', 'current' ),
			'data_compare' => $data->get_engagements( 'email_sent', 'previous' ),
			'text' => sprintf( __( 'Emails sent %s', 'lifterlms' ), $period_text ),
		) );

		do_action( 'llms_reporting_single_course_overview_after_widgets', $course ); ?>

	</section>

	<aside class="llms-reporting-tab-side">

		<h3><i class="fa fa-bolt" aria-hidden="true"></i> <?php _e( 'Recent events', 'lifterlms' ); ?></h3>

		<?php foreach ( $data->recent_events() as $event ) : ?>
			<?php LLMS_Admin_Reporting::output_event( $event, 'course' ); ?>
		<?php endforeach; ?>

	</aside>

</div>
