<?php
/**
 * Single Student View: Courses Tab: Single Course View
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since Unknown
 * @since 3.35.0 Access `$_GET` data via `llms_filter_input()`.
 * @since 3.36.2 Upgrade UI to utilize reporting widgets.
 *               Add edit link tooltip and update icon.
 *               Add a link to view full course reporting screen.
 * @version 3.36.2
 */

defined( 'ABSPATH' ) || exit;
is_admin() || exit;

$course_id = llms_filter_input( INPUT_GET, 'course_id', FILTER_SANITIZE_NUMBER_INT );
$course    = llms_get_post( $course_id );
$table     = new LLMS_Table_Student_Course();
$table->get_results(
	array(
		'course_id' => $course_id,
		'student'   => $student,
	)
);
?>

<?php do_action( 'llms_reporting_student_single_course_before_content' ); ?>

<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>
			<h3>
				<?php
					// Translators: %s = Course title.
					printf( __( 'Course: %s', 'lifterlms' ), $course->get( 'title' ) );
				?>
				<?php
					echo $table->get_post_link(
						$course->get( 'id' ),
						'<span class="tip--top-right" data-tip="' . esc_attr__( 'Edit course', 'lifterlms' ) . '"><i class="fa fa-pencil" aria-hidden="true"></i></span>'
					);
					?>
				<a href="
				<?php
				echo LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'tab'       => 'courses',
						'course_id' => $course_id,
					)
				);
				?>
				">
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'View course reports', 'lifterlms' ); ?>">
						<i class="fa fa-pie-chart" aria-hidden="true"></i>
					</span>
				</a>
			</h3>
		</header>
		<?php

		do_action( 'llms_reporting_single_student_course_before_widgets', $student );

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of5',
				'icon'      => 'calendar',
				'id'        => 'llms-reporting-student-course-enrollment-date',
				'data'      => $student->get_enrollment_date( $course_id, 'enrolled' ),
				'data_type' => 'date',
				'text'      => __( 'Enrollment Date', 'lifterlms' ),
			)
		);

		$enrollment_status = $student->get_enrollment_status( $course_id );
		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of5',
				'icon'      => 'enrolled' === $enrollment_status ? 'check-circle' : 'exclamation-triangle',
				'id'        => 'llms-reporting-student-course-enrollment-status',
				'data'      => llms_get_enrollment_status_name( $enrollment_status ),
				'data_type' => 'text',
				'text'      => __( 'Enrollment Status', 'lifterlms' ),
			)
		);

		$is_complete = $student->is_complete( $course_id, 'course' );
		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of5',
				'icon'      => 'calendar',
				'id'        => 'llms-reporting-student-course-completed-date',
				'data'      => $is_complete ? $student->get_completion_date( $course_id ) : $student->get_enrollment_date( $course_id, 'updated' ),
				'data_type' => 'date',
				'text'      => $is_complete ? __( 'Completed Date', 'lifterlms' ) : __( 'Last Activity Date', 'lifterlms' ),
			)
		);

		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of5',
				'icon'      => 'line-chart',
				'id'        => 'llms-reporting-student-course-progress',
				'data'      => $student->get_progress( $course_id, 'course' ),
				'data_type' => 'percentage',
				'text'      => __( 'Progress', 'lifterlms' ),
			)
		);

		$grade = $student->get_grade( $course_id );
		LLMS_Admin_Reporting::output_widget(
			array(
				'cols'      => 'd-1of5',
				'icon'      => 'graduation-cap',
				'id'        => 'llms-reporting-student-course-grade',
				'data'      => $grade,
				'data_type' => is_numeric( $grade ) ? 'percentage' : 'text',
				'text'      => __( 'Grade', 'lifterlms' ),
			)
		);

		do_action( 'llms_reporting_single_student_course_after_widgets', $student );
		?>

		<?php echo $table->get_table_html(); ?>

	</section>

</div>

<?php do_action( 'llms_reporting_student_single_course_after_content' ); ?>
