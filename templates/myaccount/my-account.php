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

<?php do_action( 'lifterlms_my_account_navigation' ); ?>

	<?php
		printf(
			__( '<h3>Hello <strong>%1$s</strong></h3>', 'lifterlms' ) . ' ',
			$current_user->display_name
		);

		echo apply_filters( 'lifterlms_account_greeting', __( 'What would you like to learn today?', 'lifterlms' ) );

	?>

<?php do_action( 'lifterlms_before_my_account' ); ?>

<?php llms_get_template( 'myaccount/my-courses.php' ); ?>

<?php llms_get_template( 'myaccount/my-certificates.php' ); ?>

<?php llms_get_template( 'myaccount/my-achievements.php' ); ?>

<?php if ( get_option( 'lifterlms_enable_myaccount_memberships_list', 'no' ) === 'yes' ) : ?>

	<?php llms_get_template( 'myaccount/my-memberships.php' ); ?>

<?php endif; ?>

<?php do_action( 'lifterlms_after_my_account' ); ?>
