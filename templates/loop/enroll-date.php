<?php
/**
 * LifterLMS Loop Enrollment Date
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
<div class="llms-meta llms-enroll-date">
	<p><?php printf( __( 'Enrolled: %s', 'lifterlms' ), $student->get_enrollment_date( get_the_ID() ) ); ?></p>
</div>
