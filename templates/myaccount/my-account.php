<?php
/**
 * My Account page
 *
 * @author 		codeBOX
 * @package 	lifterlMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

llms_print_notices(); ?>

<p class="myaccount_user">
	<?php

	printf(
		__( '<a href="%1$s">Sign out</a>.', 'lifterlms' ) . ' ',
		wp_logout_url( get_permalink( llms_get_page_id( 'myaccount' ) ) )
	);
	echo '<br /><br />';

	printf(
		__( 'Hello <strong>%1$s</strong>', 'lifterlms' ) . ' ',
		$current_user->display_name
	);

	echo '<br />';

	printf( __( 'From your account dashboard you can view courses and <a href="%s">edit your account information</a>.', 'lifterlms' ),
		llms_person_edit_account_url()
	);
	?>
</p>

<?php do_action( 'lifterlms_before_my_account' ); ?>

<?php do_action( 'lifterlms_after_my_account' ); ?>
