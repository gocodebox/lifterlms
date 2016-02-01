<?php
/**
 * Redeem vouchers
 * @since  2.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_my_account_navigation' ); ?>

<?php do_action( 'lifterlms_before_redeem_voucher' ); ?>

<form method="POST">

	<p class="form-row form-row-first">
		<label for="account_first_name">Voucher Code</label>
		<input type="text" placeholder="Voucher Code" name="llms_voucher_code" required="required">
	</p>

	<button type="submit">Submit</button>

	<?php wp_nonce_field('lifterlms_voucher_check', 'lifterlms_voucher_nonce'); ?>

</form>

<?php do_action( 'lifterlms_after_redeem_voucher' ); ?>
