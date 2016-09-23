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

	<?php printf( __( '<p>Hello <strong>%1$s</strong></p>', 'lifterlms' ), $current_user->display_name ); ?>

	<?php echo apply_filters( 'lifterlms_account_greeting', '' ); ?>

	<?php
	llms_get_template( 'myaccount/my-courses.php', array(
		'courses' => $courses,
		'student' => $student,
	) );
	?>

	<?php llms_get_template( 'myaccount/my-certificates.php' ); ?>

	<?php llms_get_template( 'myaccount/my-achievements.php' ); ?>

	<?php llms_get_template( 'myaccount/my-memberships.php' ); ?>

</div>
