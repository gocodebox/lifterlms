<?php
/**
 * Coupon area of the checkout form
 *
 * @package LifterLMS/Templates/Checkout
 *
 * @since Unknown
 * @since 5.0.0 Update form field to utilize "checked" attribute of "selected" and removed superfluous values.
 * @since 7.0.0 Disable data-source loading for gateway radio fields.
 * @since 7.5.0 Added check on whether a gateway can or cannot process a plan, or an order's plan (source switching).
 *              Escaped localized strings.
 * @version 7.5.0
 *
 * @param LLMS_Payment_Gateway[] $gateways         Array of enabled payment gateway instances.
 * @param string                 $selected_gateway ID of the currently selected/default payment gateway.
 * @param LLMS_Coupon|false      $coupon           Coupon currently applied to the session or `false` when none found.
 * @param LLMS_Access_Plan|null  $plan             Access plan object.
 * @param LLMS_Order|null        $order            Order object.
 */
defined( 'ABSPATH' ) || exit;

$coupon        = isset( $coupon ) ? $coupon : false;
$show_gateways = true;

// Don't display for free plans or plans which do not require any payment.
if ( isset( $plan ) && ( $plan->is_free() || ! $plan->requires_payment( $coupon ) ) ) {
	$show_gateways = false;
} elseif ( isset( $plan ) ) {
	$supports = $plan->is_recurring() ? 'recurring_payments' : 'single_payments';
} elseif ( isset( $order ) ) { // Switching source.
	$supports = $order->is_recurring() ? 'recurring_payments' : 'single_payments';
}

$supporting_gateways = 0;
$gateways_array      = array_values( $gateways );
?>
<ul class="llms-payment-gateways">
	<?php if ( $show_gateways ) : ?>
		<?php if ( ! $gateways ) : ?>
			<li class="llms-payment-gateway-error"><?php esc_html_e( 'Payment processing is currently disabled.', 'lifterlms' ); ?></li>
		<?php else : ?>
			<?php foreach ( $gateways_array as $index => $gateway ) : ?>
				<?php if ( $gateway->supports( $supports ) ) : ?>
					<?php
					if ( ! $gateway->can_process_access_plan( $plan ?? null, $order ?? null ) ) {
						$selected_gateway = ( $gateway->get_id() === $selected_gateway && isset( $gateways_array[ $index + 1 ] ) ) ?
							$gateways_array[ $index + 1 ]->get_id() : $selected_gateway;

						/**
						 * Fired when a gateway cannot process a specific plan.
						 *
						 * @since 7.5.0
						 *
						 * @param LLMS_Payment_Gateway  $gateway Payment gateway instance.
						 * @param LLMS_Access_Plan|null $plan    Access plan object.
						 * @param LLMS_Order|null       $order   Order object.
						 */
						do_action( 'llms_checkout_form_gateway_cant_process_plan', $gateway, $plan ?? null, $order ?? null );
						continue;
					}
					?>
					<li class="llms-payment-gateway <?php echo esc_attr( $gateway->get_id() ); ?><?php echo ( $selected_gateway === $gateway->get_id() ) ? ' is-selected' : ''; ?>">
					<?php
					llms_form_field(
						array(
							'description'     => $gateway->get_icon(),
							'id'              => 'llms_payment_gateway_' . $gateway->get_id(),
							'label'           => $gateway->get_title(),
							'name'            => 'llms_payment_gateway',
							'checked'         => ( $selected_gateway === $gateway->get_id() ),
							'type'            => 'radio',
							'value'           => $gateway->get_id(),
							'wrapper_classes' => 'llms-payment-gateway-option',
							'data_store'      => false,
						)
					);
					?>
					<?php if ( $gateway->get_description() ) : ?>
						<div class="llms-gateway-description"><?php echo wp_kses_post( wpautop( wptexturize( $gateway->get_description() ) ) ); ?></div>
					<?php endif; ?>
					<?php if ( $gateway->supports( 'checkout_fields' ) ) : ?>
						<div class="llms-gateway-fields"><?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $gateway->get_fields();
						?></div>
					<?php endif; ?>
					</li>
					<?php $supporting_gateways++; ?>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php if ( ! $supporting_gateways ) : ?>
				<li class="llms-payment-gateway-error"><?php esc_html_e( 'There are no gateways enabled which can support the necessary transaction type for this access plan.', 'lifterlms' ); ?></li>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
</ul>
