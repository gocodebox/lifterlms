<?php
/**
 * LifterLMS Loop Enrollment Date
 *
 * @since   3.14.0
 * @version 3.14.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$student = llms_get_student();
if ( ! $student ) {
	return;
}

?>
<div class="llms-meta llms-enroll-date">
	<p><?php printf( __( 'Enrolled: %s', 'lifterlms' ), $student->get_enrollment_date( get_the_ID() ) ); ?></p>
</div>
