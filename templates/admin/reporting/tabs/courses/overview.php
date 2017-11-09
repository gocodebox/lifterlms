<?php
/**
 * Single Course Tab: Overview Subtab
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

include LLMS_PLUGIN_DIR . 'includes/class.llms.course.data.php';

$data = new LLMS_Course_Data( $course->get( 'id' ) );
$period = isset( $_GET['period'] ) ? $_GET['period'] : 'today';
$data->set_period( $period );
$periods = array(
	'today' => esc_attr__( 'Today', 'lifterlms' ),
	'yesterday' => esc_attr__( 'Yesterday', 'lifterlms' ),
	'week' => esc_attr__( 'This Week', 'lifterlms' ),
	'last_week' => esc_attr__( 'Last Week', 'lifterlms' ),
	'month' => esc_attr__( 'This Month', 'lifterlms' ),
	'last_month' => esc_attr__( 'Last Month', 'lifterlms' ),
	'year' => esc_attr__( 'This Year', 'lifterlms' ),
	'last_year' => esc_attr__( 'Last Year', 'lifterlms' ),
);
$period_text = strtolower( $periods[ $period ] );
$now = current_time( 'timestamp' );
?>

<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>

			<div class="llms-reporting-tab-filter">
				<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" method="GET">
					<select class="llms-select2" name="period" onchange="this.form.submit();">
						<?php foreach ( $periods as $val => $text ) : ?>
							<option value="<?php echo $val; ?>"<?php selected( $val, $period ); ?>><?php echo $text; ?></option>
						<?php endforeach; ?>
					</select>
					<input type="hidden" name="page" value="llms-reporting">
					<input type="hidden" name="tab" value="courses">
					<input type="hidden" name="course_id" value="<?php echo $course->get( 'id' ); ?>">
				</form>
			</div>

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

		<?php foreach ( $data->recent_events() as $event ) :
			$student = llms_get_student( $event->user_id );
			$url = LLMS_Admin_Reporting::get_current_tab_url( array(
				'course_id' => $course->get( 'id' ),
				'stab' => 'courses',
				'student_id' => $event->user_id,
				'tab' => 'students',
			) );
			switch ( $event->meta_key ) {
				case '_is_complete':
					$verb = __( 'completed', 'lifterlms' );
				break;
				case '_status':
					$verb = strtolower( llms_get_enrollment_status_name( $event->meta_value ) );
				break;
				default:
					$verb = $event->meta_key;
			}
			?>

			<div class="llms-reporting-event <?php echo $event->meta_key; ?> <?php echo $event->meta_value; ?>">

				<a href="<?php echo esc_url( $url ); ?>">
					<?php echo $student->get_avatar( 24 ); ?>
					<?php printf( '%1$s %2$s %3$s', $student->get( 'display_name' ), $verb, get_the_title( $event->post_id ) ); ?>
					<time datetime="<?php echo $event->updated_date; ?>"><?php echo llms_get_date_diff( current_time( 'timestamp' ), $event->updated_date, 1 ); ?></time>
				</a>

			</div>

		<?php endforeach; ?>

	</aside>

</div>
