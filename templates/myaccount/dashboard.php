<?php
/**
 * My Account page
 * @since    1.0.0
 * @version  3.14.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

llms_print_notices();
?>

<div class="llms-sd-tab dashboard">

	<?php

		do_action( 'lifterlms_before_student_dashboard_tab' );

		/**
		 * lifterlms_student_dashboard_index
		 * @hooked lifterlms_template_student_dashboard_my_courses - 10
		 * @hooked lifterlms_template_student_dashboard_my_achievements - 20
		 * @hooked lifterlms_template_student_dashboard_my_certificates - 30
		 * @hooked lifterlms_template_student_dashboard_my_memberships - 40
		 */
		do_action( 'lifterlms_student_dashboard_index', true );

		do_action( 'lifterlms_after_student_dashboard_tab' );

	?>

</div>

<?php return; ?>


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
