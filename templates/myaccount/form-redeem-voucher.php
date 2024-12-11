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

<form action="" method="POST" class="llms-voucher-form">

	<div class="llms-form-fields">
		<div class="form-row form-row-first llms-form-field type-text llms-cols-6 llms-is-required">
			<label for="llms-voucher-code"><?php esc_html_e( 'Voucher Code', 'lifterlms' ); ?></label>
			<input id="llms-voucher-code" type="text" placeholder="<?php esc_attr_e( 'Voucher Code', 'lifterlms' ); ?>" name="llms_voucher_code" class="llms-field-input" required="required">
		</div>
	</div>

	<footer class="llms-form-fields">
		<div class="llms-form-field type-submit llms-cols-3 llms-cols-last"">
			<button id="llms-redeem-voucher-submit" type="submit" class="llms-field-button llms-button-action"><?php echo esc_html_x( 'Submit', 'Voucher Code', 'lifterlms' ); ?></button>
		</div>
		<?php wp_nonce_field( 'lifterlms_voucher_check', 'lifterlms_voucher_nonce' ); ?>
	</footer>

</form>

<?php do_action( 'lifterlms_after_redeem_voucher' ); ?>
