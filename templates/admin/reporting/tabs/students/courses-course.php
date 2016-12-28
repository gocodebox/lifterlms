<?php
/**
 * Single Student View: Courses Tab: Single Course View
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

$course_id = absint( $_GET['course_id'] );
$course = new LLMS_Course( $course_id );
$table = new LLMS_Table_Student_Course();
$table->get_results( array(
	'course_id' => $course_id,
	'student' => $student,
) );
?>

<h2 class="llms-stab-title">
	<?php echo $course->get( 'title' ); ?>
	<?php echo $table->get_post_link( $course->get( 'id' ), '<span class="dashicons dashicons-admin-links"></span>' ); ?>
</h2>

<div class="llms-widget-row">

	<div class="llms-widget-1-5">
		<div class="llms-widget alt">
			<p class="llms-label"><?php _e( 'Enrollment Status', 'lifterlms' ); ?></p>
			<h2><?php echo llms_get_enrollment_status_name( $student->get_enrollment_status( $course_id ) ); ?></h2>
		</div>
	</div>

	<div class="llms-widget-1-5">
		<div class="llms-widget alt">
			<p class="llms-label"><?php _e( 'Progress', 'lifterlms' ); ?></p>
			<h2><?php echo $student->get_progress( $course_id, 'course' ); ?>%</h2>
		</div>
	</div>

	<div class="llms-widget-1-5">
		<div class="llms-widget alt">
			<p class="llms-label"><?php _e( 'Current Grade', 'lifterlms' ); ?></p>
			<?php $grade = $student->get_grade( $course_id ); ?>
			<h2><?php echo $grade; ?><?php echo is_numeric( $grade ) ? '%' : ''; ?></h2>
		</div>
	</div>

	<div class="llms-widget-1-5">
		<div class="llms-widget alt">
			<p class="llms-label"><?php _e( 'Enrollment Date', 'lifterlms' ); ?></p>
			<h2><?php echo $student->get_enrollment_date( $course_id, 'enrolled' ); ?></h2>
		</div>
	</div>

	<div class="llms-widget-1-5">
		<div class="llms-widget alt">
		<?php if ( $student->is_complete( $course_id, 'course' ) ) : ?>
			<p class="llms-label"><?php _e( 'Completion Date', 'lifterlms' ); ?></p>
			<h2><?php echo $student->get_completion_date( $course_id, 'M d, Y' ); ?></h2>
		<?php else : ?>
			<p class="llms-label"><?php _e( 'Last Activity', 'lifterlms' ); ?></p>
			<h2><?php echo $student->get_enrollment_date( $course_id, 'updated' ); ?></h2>
		<?php endif; ?>
		</div>
	</div>

</div>

<?php echo $table->get_table_html(); ?>
<?php return; ?>
<p>
	<?php printf( __( 'Progress: %s', 'lifterlms' ), LLMS_Admin_Reporting::get_course_data( $course, $student, 'progress' ) ); ?> |
	<?php printf( __( 'Grade: %s', 'lifterlms' ), LLMS_Admin_Reporting::get_course_data( $course, $student, 'grade' ) ); ?> |
	<?php printf( __( 'Completed: %s', 'lifterlms' ), LLMS_Admin_Reporting::get_course_data( $course, $student, 'completed' ) ); ?>
</p>
