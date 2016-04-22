<?php
/**
 * My Account Navigation partial
 *
 * @author 		codeBOX
 * @package 	LifterLMS/Templates
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<nav class="account-links">

	<?php do_action( 'lifterlms_before_my_account_navigation' ); ?>

	<a class="llms-nav-link my-account" href="<?php echo get_permalink( llms_get_page_id( 'myaccount' ) ); ?>"><?php _e( 'My Account', 'lifterlms' ); ?></a>
	<?php echo apply_filters( 'lifterlms_my_account_navigation_link_separator', '&middot;' ); ?>

	<a class="llms-nav-link account-settings" href="<?php echo llms_person_edit_account_url(); ?>"><?php _e( 'Account Settings', 'lifterlms' ); ?></a>
	<?php echo apply_filters( 'lifterlms_my_account_navigation_link_separator', '&middot;' ); ?>

	<a class="llms-nav-link redeem-voucher" href="<?php echo llms_person_redeem_voucher_url(); ?>" ><?php _e( 'Redeem a Voucher', 'lifterlms' ); ?></a>
	<?php echo apply_filters( 'lifterlms_my_account_navigation_link_separator', '&middot;' ); ?>

	<a class="llms-nav-link signout" href="<?php echo wp_logout_url( get_permalink( llms_get_page_id( 'myaccount' ) ) ); ?>"><?php _e( 'Sign out', 'lifterlms' ); ?></a>

	<?php do_action( 'lifterlms_after_my_account_navigation' ); ?>
</nav>
