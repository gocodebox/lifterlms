<?php
/**
 * My Account page
 *
 * @author 		codeBOX
 * @package 	lifterlMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

llms_print_notices();
?>

<div class="llms-sd-tab dashboard">

	<?php do_action( 'lifterlms_before_student_dashboard_tab' ); ?>

	<?php printf( __( '<p>Hello <strong>%1$s</strong></p>', 'lifterlms' ), $current_user->display_name ); ?>

	<?php echo apply_filters( 'lifterlms_account_greeting', '' ); ?>

	<?php do_action( 'lifterlms_after_student_dashboard_greeting' ); ?>

	<?php llms_get_template( 'myaccount/my-courses.php', array(
		'courses' => $courses,
		'student' => $student,
	) ); ?>

	<?php llms_get_template( 'myaccount/my-certificates.php' ); ?>

	<?php llms_get_template( 'myaccount/my-achievements.php' ); ?>

	<?php llms_get_template( 'myaccount/my-memberships.php' ); ?>

	<?php do_action( 'lifterlms_after_student_dashboard_tab' ); ?>

</div>
