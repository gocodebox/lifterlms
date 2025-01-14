<?php
/**
 * LifterLMS Loop Enrollment Status
 *
 * @package LifterLMS/Templates
 *
 * @since   3.14.0
 * @version 3.14.0
 */

defined( 'ABSPATH' ) || exit;

$student = llms_get_student();
if ( ! $student ) {
	return;
}
?>

<div class="llms-meta llms-enroll-status">
	<p>
	<?php
	printf(
		// Translators: %s = enrollment status.
		esc_html__( 'Status: %s', 'lifterlms' ),
		esc_html( llms_get_enrollment_status_name( $student->get_enrollment_status( get_the_ID() ) ) )
	);
	?>
	</p>
</div>
