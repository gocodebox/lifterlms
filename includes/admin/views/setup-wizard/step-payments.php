<?php
/**
 * Setup Wizard step: Payments
 *
 * @package LifterLMS/Views/Admin/SetupWizard
 *
 * @since 4.4.4
 * @since 4.8.0 Unknown.
 * @since 7.4.0 Escape output.
 * @version 7.4.0
 *
 * @property LLMS_Admin_Setup_Wizard $this Setup wizard class instance.
 */

defined( 'ABSPATH' ) || exit;

$country  = get_lifterlms_country();
$currency = get_lifterlms_currency();
$payments = get_option( 'llms_gateway_manual_enabled', 'no' );

?>
<h1><?php esc_html_e( 'Payments', 'lifterlms' ); ?></h1>

<table>
	<tr>
		<td colspan="2">
			<p><label for="llms_country"><?php esc_html_e( 'Which country should be used as the default for student registrations?', 'lifterlms' ); ?></label></p>
			<p>
				<select id="llms_country" name="country" class="llms-select2">
				<?php foreach ( get_lifterlms_countries() as $code => $name ) : ?>
					<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $code, $country ); ?>>
						<?php echo esc_html( $name . ' (' . $code . ')' ); ?>
					</option>
				<?php endforeach; ?>
				</select>
			</p>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<p><label for="llms_currency"><?php esc_html_e( 'Which currency should be used for payment processing?', 'lifterlms' ); ?></label></p>
			<p>
				<select id="llms_currency" name="currency" class="llms-select2">
				<?php foreach ( get_lifterlms_currencies() as $code => $name ) : ?>
					<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $code, $currency ); ?>><?php echo esc_html( $name ); ?> (<?php echo esc_html( get_lifterlms_currency_symbol( $code ) ); ?>)</option>
				<?php endforeach; ?>
				</select>
				<i><?php printf( esc_html__( 'If your currency is not listed you can %1$sadd it later%2$s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/how-can-i-add-my-currency-to-lifterlms/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Wizard&utm_content=LifterLMS%20Add%20Currency" target="_blank">', '</a>' ); ?></i>
			</p>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<p><?php printf( esc_html__( 'With LifterLMS you can accept both online and offline payments. Be sure to install a %1$spayment gateway%2$s to accept online payments.', 'lifterlms' ), '<a href="https://lifterlms.com/product-category/plugins/payment-gateways/?utm_source=LifterLMS%20Plugin&utm_campaign=Plugin%20to%20Sale&utm_medium=Wizard&utm_content=LifterLMS%20Payment%20Add-ons" target="_blank">', '</a>' ); ?></p>
			<p><label for="llms_manual"><input id="llms_manual" name="manual_payments" type="checkbox" value="yes"<?php checked( 'yes', $payments ); ?>> <?php esc_html_e( 'Enable Offline Payments', 'lifterlms' ); ?></label></p>
		</td>
	</tr>
</table>
