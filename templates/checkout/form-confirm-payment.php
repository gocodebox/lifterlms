<?php
/**
 * Checkout Form
 *
 * @package  LifterLMS/Templates
 *
 * @since 1.0.0
 * @since 3.34.4 Added filter `llms_order_can_be_confirmed`.
 * @since 3.34.5 Fixed logic error in `llms_order_can_be_confirmed` conditional.
 * @version 3.34.5
 */

$order_key  = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING );
$order      = llms_get_order_by_key( $order_key );
$gateway_id = $selected_gateway->get_id();

defined( 'ABSPATH' ) || exit;
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

	<form action="" class="llms-checkout llms-confirm llms-checkout-cols-<?php echo $cols; ?>" method="POST" id="llms-product-purchase-confirm-form">

		<?php do_action( 'lifterlms_before_checkout_confirm_form' ); ?>

		<div class="llms-checkout-col llms-col-1">

			<section class="llms-checkout-section">

				<h4 class="llms-form-heading"><?php _e( 'Billing Information', 'lifterlms' ); ?></h4>

				<div class="llms-checkout-section-content">
					<?php do_action( 'lifterlms_checkout_confirm_before_billing_info' ); ?>
					<?php foreach ( LLMS_Person_Handler::get_available_fields( 'checkout', $field_data ) as $field ) : ?>
						<span class="llms-field-display <?php echo $field['id']; ?>"><?php echo ! empty( $field['value'] ) ? $field['value'] : ''; ?></span><?php echo $field['last_column'] ? '<br>' : ' '; ?>
					<?php endforeach; ?>
					<?php do_action( 'lifterlms_checkout_confirm_after_billing_info' ); ?>
				</div>

			</section>

		</div>

		<div class="llms-checkout-col llms-col-2">

			<section class="llms-checkout-section">

				<h4 class="llms-form-heading"><?php _e( 'Order Summary', 'lifterlms' ); ?></h4>

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

				<h4 class="llms-form-heading"><?php _e( 'Payment Details', 'lifterlms' ); ?></h4>
				<div class="llms-checkout-section-content llms-form-fields">

					<div class="llms-payment-method">
						<?php do_action( 'lifterlms_checkout_confirm_before_payment_method', $gateway_id ); ?>
						<span class="llms-gateway-title"><span class="llms-label"><?php _e( 'Payment Method:', 'lifterlms' ); ?></span> <?php echo $selected_gateway->get_title(); ?></span>
						<?php if ( $selected_gateway->get_icon() ) : ?>
							<span class="llms-gateway-icon"><?php echo $selected_gateway->get_icon(); ?></span>
						<?php endif; ?>
						<?php if ( $selected_gateway->get_description() ) : ?>
							<div class="llms-gateway-description"><?php echo wpautop( wptexturize( $selected_gateway->get_description() ) ); ?></div>
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

						<input id="llms-payment-gateway" type="hidden" readonly="readonly" value="<?php echo $gateway_id; ?>">

					</footer>

				</div>

			</section>

		</div>

		<?php wp_nonce_field( 'confirm_pending_order' ); ?>
		<input name="action" type="hidden" value="confirm_pending_order">
		<input name="llms_order_key" type="hidden" value="<?php echo $order_key; ?>">

		<?php do_action( 'lifterlms_after_checkout_confirm_form' ); ?>

	</form>
<?php endif; ?>
