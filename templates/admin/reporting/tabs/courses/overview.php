<?php
/**
 * Single Course Tab: Overview Subtab
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! is_admin() ) { exit; }

include LLMS_PLUGIN_DIR . 'includes/class.llms.course.data.php';

$data = new LLMS_Course_Data( $course->get( 'id' ) );
$today = current_time( 'Y-m-d' );
$yest = current_time( 'timestamp' ) - DAY_IN_SECONDS;

?>

<div class="llms-reporting-widget">

	<h5><?php _e( 'Today', 'lifterlms' ); ?></h5>

	New Students: <?php echo $data->enrollments_on_date( $today ); ?>
	Lessons Completed: <?php echo $data->lessons_completed_on_date( $today ); ?>
	Course Completions: <?php echo $data->course_completed_on_date( $today ); ?>

</div>

<div class="llms-reporting-widget">

	<h5><?php _e( 'Yesterday', 'lifterlms' ); ?></h5>

	New Students: <?php echo $data->enrollments_on_date( $yest ); ?>
	Lessons Completed: <?php echo $data->lessons_completed_on_date( $yest ); ?>
	Course Completions: <?php echo $data->course_completed_on_date( $yest ); ?>

</div>
