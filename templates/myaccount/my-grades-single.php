<?php
/**
 * My Grades Single Course Template
 * @since    3.24.0
 * @version  3.24.0
 */
defined( 'ABSPATH' ) || exit;
llms_print_notices();
?>

<?php if ( $course ) : ?>

	<?php do_action( 'llms_before_my_grades_content', $course, $student ); ?>

	<section class="llms-sd-widgets">

		<?php
		do_action( 'llms_before_my_grades_widgets', $course, $student );

		llms_sd_dashboard_donut_widget(
			__( 'Progress', 'lifterlms' ),
			$student->get_progress( $course->get( 'id' ) ),
			__( 'Complete', 'lifterlms' )
		);
		llms_sd_dashboard_donut_widget(
			__( 'Grade', 'lifterlms' ),
			$student->get_grade( $course->get( 'id' ) ),
			__( 'Overall Grade', 'lifterlms' )
		);
		llms_sd_dashboard_date_widget(
			__( 'Enrollment Date', 'lifterlms' ),
			$student->get_enrollment_date( $course->get( 'id' ), 'enrolled', 'U' )
		);
		llms_sd_dashboard_widget(
			__( 'Latest Achievement', 'lifterlms' ),
			$latest_achievement ? llms_get_achievement( $latest_achievement ) : '', __( 'No achievements', 'lifterlms' )
		);
		llms_sd_dashboard_date_widget(
			__( 'Last Activity', 'lifterlms' ),
			$last_activity, __( 'No activity', 'lifterlms' )
		);

		do_action( 'llms_after_my_grades_widgets', $course, $student );
		?>

	</section>

	<?php
		/**
		 * Hook: llms_my_grades_course_table.
		 *
		 * @hooked lifterlms_template_student_dashboard_my_grades_table - 10
		 */
		do_action( 'llms_my_grades_course_table', $course, $student );
	?>

<?php else : ?>

	<p><?php _e( 'Invalid course.', 'lifterlms' ); ?>

<?php endif; ?>
