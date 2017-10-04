<?php
/**
 * LifterLMS Loop Enrollment Status
 *
 * @since   [version]
 * @version [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$student = llms_get_student();
if ( ! $student ) {
	return;
}
?>

<div class="llms-meta llms-enroll-status">
	<p><?php printf( __( 'Status: %s', 'lifterlms' ), llms_get_enrollment_status_name( $student->get_enrollment_status( get_the_ID() ) ) ); ?></p>
</div>
