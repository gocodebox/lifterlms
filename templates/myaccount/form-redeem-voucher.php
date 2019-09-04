<?php
/**
 * Redeem vouchers
 *
 * @package LifterLMS/Templates
 *
 * @since  2.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_my_account_navigation' ); ?>

<?php do_action( 'lifterlms_before_redeem_voucher' ); ?>

<form method="POST">

	<p class="form-row form-row-first">
		<label for="account_first_name"><?php _e( 'Voucher Code', 'lifterlms' ); ?></label>
		<input type="text" placeholder="<?php _e( 'Voucher Code', 'lifterlms' ); ?>" name="llms_voucher_code" required="required">
	</p>

	<button type="submit"><?php _ex( 'Submit', 'Voucher Code', 'lifterlms' ); ?></button>

	<?php wp_nonce_field( 'lifterlms_voucher_check', 'lifterlms_voucher_nonce' ); ?>

</form>

<?php do_action( 'lifterlms_after_redeem_voucher' ); ?>
