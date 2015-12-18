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

<nav class="account-links">
	<?php do_action( 'lifterlms_before_my_account_navigation' ); ?>

	<?php
	printf(
		__( '<a class="llms-nav-link signout" href="%1$s">Sign out</a>  &middot;  ', 'lifterlms' ) . ' ',
		wp_logout_url( get_permalink( llms_get_page_id( 'myaccount' ) ) )
	);

	printf( __( '<a class="llms-nav-link account-settings" href="%s">Account Settings</a>', 'lifterlms' ),
		llms_person_edit_account_url()
	);

	?>

	<?php do_action( 'lifterlms_after_my_account_navigation' ); ?>
</nav>
	<?php

		printf(
		__( '<h3>Hello <strong>%1$s</strong></h3>', 'lifterlms' ) . ' ',
		$current_user->display_name
	);

		echo __( 'What would you like to learn today?', 'lifterlms' );

	?>

<?php do_action( 'lifterlms_before_my_account' ); ?>

<?php llms_get_template( 'myaccount/my-courses.php' ); ?>

<?php llms_get_template( 'myaccount/my-certificates.php' ); ?>

<?php llms_get_template( 'myaccount/my-achievements.php' ); ?>

<?php if( get_option( 'lifterlms_enable_myaccount_memberships_list', 'no' ) === 'yes' ): ?>

	<?php llms_get_template( 'myaccount/my-memberships.php' ); ?>

<?php endif; ?>

<?php do_action( 'lifterlms_after_my_account' ); ?>
