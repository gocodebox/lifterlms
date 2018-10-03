<?php
/**
 * My Grades Template
 * @since    [version]
 * @version  [version]
 */
defined( 'ABSPATH' ) || exit;
llms_print_notices();
?>

<?php if ( $course ) : ?>

	<?php do_action( 'llms_before_my_grades_content', $course, $student ); ?>

	<section class="llms-sd-widgets">

		<?php do_action( 'llms_before_my_grades_widgets', $course, $student ); ?>

		<div class="llms-sd-widget">
			<h4 class="llms-sd-widget-title"><?php _e( 'Progress', 'lifterlms' ); ?></h4>
			<?php echo llms_get_donut( $student->get_progress( $course->get( 'id' ) ), __( 'Complete', 'lifterlms' ), 'medium' ); ?>
		</div>

		<div class="llms-sd-widget">
			<h4 class="llms-sd-widget-title"><?php _e( 'Grade', 'lifterlms' ); ?></h4>
			<?php echo llms_get_donut( $student->get_grade( $course->get( 'id' ) ), __( 'Overall Grade', 'lifterlms' ), 'medium' ); ?>
		</div>

		<div class="llms-sd-widget">
			<h4 class="llms-sd-widget-title"><?php _e( 'Enrollment Date', 'lifterlms' ); ?></h4>
			<div class="llms-sd-date">
				<span class="month"><?php echo $student->get_enrollment_date( $course->get( 'id' ), 'enrolled', 'F' ); ?></span>
				<span class="day"><?php echo $student->get_enrollment_date( $course->get( 'id' ), 'enrolled', 'j' ); ?></span>
				<span class="year"><?php echo $student->get_enrollment_date( $course->get( 'id' ), 'enrolled', 'Y' ); ?></span>
			</div>
		</div>

		<div class="llms-sd-widget">
			<h4 class="llms-sd-widget-title"></h4>

		</div>

		<?php do_action( 'llms_after_my_grades_widgets', $course, $student ); ?>

	</section>

	<?php do_action( 'llms_before_my_grades_before_table', $course, $student ); ?>

	<?php lifterlms_template_student_dashboard_my_grades_table( $course, $student ); ?>

	<?php do_action( 'llms_after_my_grades_content', $course, $student ); ?>

<?php else : ?>

	<p><?php _e( 'Invalid course.', 'lifterlms' ); ?>

<?php endif; ?>
