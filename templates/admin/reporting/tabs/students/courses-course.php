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
 * @since 6.0.0 Provide existing hooks with more information and add a new hook.
 * @version 6.0.0
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

/**
 * Action run prior to content on the student course reporting screen.
 *
 * @since Unknown
 * @since 6.0.0 Added the `$student` and `$course` parameters.
 *
 * @param LLMS_Student $student Current student.
 * @param LLMS_course  $course  Current course.
 */
do_action( 'llms_reporting_student_single_course_before_content', $student, $course );
?>
<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>
			<h3>
				<?php
					// Translators: %s = Course title.
					echo esc_html( sprintf( __( 'Course: %s', 'lifterlms' ), $course->get( 'title' ) ) );
				?>
				<?php
					echo wp_kses_post(
						$table->get_post_link(
							$course->get( 'id' ),
							'<span class="tip--top-right" data-tip="' . esc_attr__( 'Edit course', 'lifterlms' ) . '"><i class="fa fa-pencil" aria-hidden="true"></i></span>'
						)
					);
					?>
				<a href="
				<?php
				echo esc_url(
					LLMS_Admin_Reporting::get_current_tab_url(
						array(
							'tab'       => 'courses',
							'course_id' => $course_id,
						)
					)
				);
				?>
				">
					<span class="tip--top-right" data-tip="<?php esc_attr_e( 'View course reports', 'lifterlms' ); ?>">
						<i class="fa fa-pie-chart" aria-hidden="true"></i>
					</span>
				</a>
				<?php
					/**
					 * Action run after default action buttons on the student course reporting screen.
					 *
					 * @since 6.0.0
					 *
					 * @param LLMS_Student $student Current student.
					 * @param LLMS_course  $course  Current course.
					 */
					do_action( 'llms_reporting_single_student_course_actions', $student, $course );
				?>
			</h3>

		</header>
		<?php
		/**
		 * Action run before the default widgets on the student course reporting screen.
		 *
		 * @since Unknown
		 * @since 6.0.0 Added the `$course` parameter.
		 *
		 * @param LLMS_Student $student Current student.
		 * @param LLMS_course  $course  Current course.
		 */
		do_action( 'llms_reporting_single_student_course_before_widgets', $student, $course );

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

		/**
		 * Action run after the default widgets on the student course reporting screen.
		 *
		 * @since Unknown
		 * @since 6.0.0 Added the `$course` parameter.
		 *
		 * @param LLMS_Student $student Current student.
		 * @param LLMS_course  $course  Current course.
		 */
		do_action( 'llms_reporting_single_student_course_after_widgets', $student, $course );
		?>

		<?php $table->output_table_html(); ?>

	</section>

</div>

<?php
/**
 * Action run after the content on the student course reporting screen.
 *
 * @since Unknown
 * @since 6.0.0 Added the `$student` and `$course` parameters.
 *
 * @param LLMS_Student $student Current student.
 * @param LLMS_course  $course  Current course.
 */
do_action( 'llms_reporting_student_single_course_after_content', $student, $course );
