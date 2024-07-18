<?php
/**
 * Redeem vouchers
 *
 * @package LifterLMS/Templates
 *
 * @since 2.0.0
 * @since 4.12.0 Updated the label `for` attribute and added an `id` to the input element.
 * @version 4.12.0
 */

defined( 'ABSPATH' ) || exit;
?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_my_account_navigation' ); ?>

<?php do_action( 'lifterlms_before_redeem_voucher' ); ?>

<form action="" method="POST">

	<p class="form-row form-row-first">
		<label for="llms-voucher-code"><?php esc_html_e( 'Voucher Code', 'lifterlms' ); ?></label>
		<input id="llms-voucher-code" type="text" placeholder="<?php esc_html_e( 'Voucher Code', 'lifterlms' ); ?>" name="llms_voucher_code" required="required">
	</p>

	<button id="llms-redeem-voucher-submit" type="submit"><?php echo esc_html_x( 'Submit', 'Voucher Code', 'lifterlms' ); ?></button>

	<?php wp_nonce_field( 'lifterlms_voucher_check', 'lifterlms_voucher_nonce' ); ?>

</form>

<?php do_action( 'lifterlms_after_redeem_voucher' ); ?>
