<?php
/**
 * My Account page
 *
 * @author 		codeBOX
 * @package 	lifterlMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

llms_print_notices(); ?>

<nav class="account-links">
	<?php

	printf(
		__( '<a href="%1$s">Sign out</a>  &middot;  ', 'lifterlms' ) . ' ',
		wp_logout_url( get_permalink( llms_get_page_id( 'myaccount' ) ) )
	);


	printf( __( '<a href="%s">Account Settings</a>.', 'lifterlms' ),
		llms_person_edit_account_url()
	);

	?>
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

<?php do_action( 'lifterlms_after_my_account' ); ?>
