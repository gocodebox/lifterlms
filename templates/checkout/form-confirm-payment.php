<?php
/**
 * Payment gateways area of the checkout form
 *
 * @package LifterLMS/Templates/Checkout
 *
 * @since Unknown
 * @since 5.0.0 Update form field to utilize "checked" attribute of "selected" and removed superfluous values.
 * @since 7.0.0 Disable data-source loading for gateway radio fields.
 * @version 7.0.0
 *
 * @param LLMS_Payment_Gateway[] $gateways         Array of enabled payment gateway instances.
 * @param string                 $selected_gateway ID of the currently selected/default payment gateway.
 * @param LLMS_Coupon|false      $coupon           Coupon currently applied to the session or `false` when none found.
 * @param LLMS_Access_Plan       $plan             Access plan object.
 */
defined( 'ABSPATH' ) || exit;

$order_key  = llms_filter_input_sanitize_string( INPUT_GET, 'order' );
$order      = llms_get_order_by_key( $order_key );
$gateway_id = $selected_gateway->get_id();
$fields     = LLMS_Forms::instance()->get_form_fields( 'checkout', array( 'plan' => $plan ) );
?>

<?php if ( ! apply_filters( 'llms_order_can_be_confirmed', ( 'llms-pending' === $order->get( 'status' ) ), $order, $gateway_id ) ) : ?>

	<?php
	llms_print_notice(
		sprintf(
			// Translators: %1$s = opening anchor tag; %2$s = closing anchor tag.
			__( 'Only pending orders can be confirmed. View your %1$sorder history%2$s for more information', 'lifterlms' ),
			'<a href="' . esc_url( llms_get_endpoint_url( 'orders', '', llms_get_page_url( 'myaccount' ) ) ) . '">',
			'</a>'
		),
		'error'
	);
	?>

<?php else : ?>

	<form action="" class="llms-checkout llms-confirm llms-checkout-cols-<?php echo esc_attr( $cols ); ?>" method="POST" id="llms-product-purchase-confirm-form">

		<?php do_action( 'lifterlms_before_checkout_confirm_form' ); ?>

		<div class="llms-checkout-col llms-col-1">

			<section class="llms-checkout-section">

				<h4 class="llms-form-heading"><?php echo esc_html( llms_get_form_title( 'checkout', array( 'plan' => $plan ) ) ); ?></h4>

				<div class="llms-checkout-section-content llms-form-fields">
					<?php do_action( 'lifterlms_checkout_confirm_before_billing_info' ); ?>
					<?php foreach ( $fields as $field ) : ?>
							<?php if ( ! empty( $field['value'] ) && ! empty( $field['label'] ) ) : ?>
								<div class="llms-form-field llms-field-display <?php echo esc_attr( $field['id'] ); ?>">
									<strong><?php echo esc_html( $field['label'] ); ?></strong>: <?php echo esc_html( $field['value'] ); ?>
								</div>
							<?php endif; ?>
					<?php endforeach; ?>
					<?php do_action( 'lifterlms_checkout_confirm_after_billing_info' ); ?>
				</div>

			</section>

		</div>

		<div class="llms-checkout-col llms-col-2">

			<section class="llms-checkout-section">

				<h4 class="llms-form-heading"><?php esc_html_e( 'Order Summary', 'lifterlms' ); ?></h4>

				<div class="llms-checkout-section-content">

					<?php
					llms_get_template(
						'checkout/form-summary.php',
						array(
							'coupon'  => $coupon,
							'plan'    => $plan,
							'product' => $product,
						)
					);
					?>

				</div>

			</section>

			<section class="llms-checkout-section">

				<h4 class="llms-form-heading"><?php esc_html_e( 'Payment Details', 'lifterlms' ); ?></h4>
				<div class="llms-checkout-section-content llms-form-fields">

					<div class="llms-payment-method">
						<?php do_action( 'lifterlms_checkout_confirm_before_payment_method', $gateway_id ); ?>
						<span class="llms-gateway-title"><span class="llms-label"><?php esc_html_e( 'Payment Method:', 'lifterlms' ); ?></span> <?php echo esc_html( $selected_gateway->get_title() ); ?></span>
						<?php if ( $selected_gateway->get_icon() ) : ?>
							<span class="llms-gateway-icon"><?php echo wp_kses_post( $selected_gateway->get_icon() ); ?></span>
						<?php endif; ?>
						<?php if ( $selected_gateway->get_description() ) : ?>
							<div class="llms-gateway-description"><?php echo wp_kses_post( wpautop( wptexturize( $selected_gateway->get_description() ) ) ); ?></div>
						<?php endif; ?>
						<?php do_action( 'lifterlms_checkout_confirm_after_payment_method', $gateway_id ); ?>
					</div>

					<footer class="llms-checkout-confirm llms-form-fields flush">

						<?php if ( apply_filters( 'llms_gateway_' . $gateway_id . '_show_confirm_order_button', true ) ) : ?>

							<?php
							llms_form_field(
								array(
									'columns'     => 12,
									'classes'     => 'llms-button-action',
									'id'          => 'llms_confirm_pending_order',
									'value'       => apply_filters( 'lifterlms_checkout_confirm_button_text', __( 'Confirm Payment', 'lifterlms' ) ),
									'last_column' => true,
									'required'    => false,
									'type'        => 'submit',
								)
							);
							?>

						<?php endif; ?>

						<input id="llms-payment-gateway" type="hidden" readonly="readonly" value="<?php echo esc_attr( $gateway_id ); ?>">

					</footer>

				</div>

			</section>

		</div>

		<?php wp_nonce_field( 'confirm_pending_order' ); ?>
		<input name="action" type="hidden" value="confirm_pending_order">
		<input name="llms_order_key" type="hidden" value="<?php echo esc_attr( $order_key ); ?>">

		<?php do_action( 'lifterlms_after_checkout_confirm_form' ); ?>

	</form>
<?php endif; ?>
