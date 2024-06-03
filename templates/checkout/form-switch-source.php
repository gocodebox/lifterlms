<?php
/**
 * User form used to switch the payment source for recurring payment orders.
 *
 * Included on single order view pages via Student Dashboard.
 *
 * @package LifterLMS/Templates
 *
 * @since 3.10.0
 * @since 7.0.0 Use {@see LLMS_Order::get_switch_source_action()} to determine the switch source action input value.
 * @since 7.5.0 Pass the `LLMS_Order` instance to the form-gateways template.
 * @version 7.0.0
 *
 * @var string     $confirm The ID of the payment gateway when confirming a switch.
 * @var LLMS_Order $order   The order object.
 */
defined( 'ABSPATH' ) || exit;

$status  = $order->get( 'status' );
$gateway = llms()->payment_gateways()->get_gateway_by_id( $confirm );
$plan    = llms_get_post( $order->get( 'plan_id' ) );
if ( ! $plan ) {
	return;
}
if ( 'llms-active' === $status ) {
	$submit_text = __( 'Save Payment Method', 'lifterlms' );
} elseif ( 'llms-pending-cancel' === $status ) {
	$submit_text = __( 'Reactivate Subscription', 'lifterlms' );
} else {
	$submit_text = __( 'Save and Pay Now', 'lifterlms' );
}
?>

<form action="" class="llms-switch-payment-source llms-checkout-wrapper" id="llms-product-purchase-form" method="POST">

	<?php
	llms_form_field(
		array(
			'columns'     => 12,
			'classes'     => 'llms-button-secondary',
			'id'          => 'llms_update_payment_method',
			'value'       => 'llms-pending-cancel' === $status ? __( 'Reactivate Subscription', 'lifterlms' ) : __( 'Update Payment Method', 'lifterlms' ),
			'last_column' => true,
			'required'    => false,
			'type'        => 'button',
		)
	);
	?>

	<div class="llms-switch-payment-source-main llms-checkout-section"<?php echo $confirm ? ' style="display:block;"' : ''; ?>>

		<?php if ( ! $confirm ) : ?>

			<?php
			llms_get_template(
				'checkout/form-gateways.php',
				array(
					'gateways'         => llms()->payment_gateways()->get_enabled_payment_gateways(),
					'selected_gateway' => $order->get( 'payment_gateway' ),
					'plan'             => null,
					'order'            => $order,
				)
			);
			?>

			<?php if ( ! in_array( $status, array( 'llms-active', 'llms-pending-cancel' ) ) ) : ?>
				<ul class="llms-order-summary">
					<li>
						<?php
						// Translators: %s = formatted price / amount due.
						echo wp_kses( sprintf( __( 'Due Now: %s', 'lifterlms' ), '<span class="price-regular">' . $order->get_price( 'total' ) . '</span>' ), LLMS_ALLOWED_HTML_PRICES );
						?>
					</li>
				</ul>
			<?php endif; ?>

		<?php elseif ( $confirm && $gateway ) : ?>

			<div class="llms-payment-method">
				<?php do_action( 'lifterlms_checkout_confirm_before_payment_method', $gateway->get_id(), 'switch' ); ?>
				<span class="llms-gateway-title"><span class="llms-label"><?php esc_html_e( 'Payment Method:', 'lifterlms' ); ?></span> <?php echo esc_html( $gateway->get_title() ); ?></span>
				<?php if ( $gateway->get_icon() ) : ?>
					<span class="llms-gateway-icon"><?php echo wp_kses_post( $gateway->get_icon() ); ?></span>
				<?php endif; ?>
				<?php if ( $gateway->get_description() ) : ?>
					<div class="llms-gateway-description"><?php echo wp_kses_post( wpautop( wptexturize( $gateway->get_description() ) ) ); ?></div>
				<?php endif; ?>
				<?php do_action( 'lifterlms_checkout_confirm_after_payment_method', $gateway->get_id(), 'switch' ); ?>
			</div>

			<input name="llms_payment_gateway" type="hidden" value="<?php echo esc_attr( $gateway->get_id() ); ?>">

		<?php endif; ?>

		<?php wp_nonce_field( 'llms_switch_order_source', '_switch_source_nonce' ); ?>
		<input name="order_id" type="hidden" value="<?php echo esc_attr( $order->get( 'id' ) ); ?>">
		<input name="llms_switch_action" type="hidden" value="<?php echo esc_attr( $order->get_switch_source_action() ); ?>">

		<?php
		llms_form_field(
			array(
				'columns'     => 12,
				'classes'     => 'llms-button-primary',
				'id'          => 'llms_save_payment_method',
				'value'       => $submit_text,
				'last_column' => true,
				'required'    => false,
				'type'        => 'submit',
			)
		);
		?>

	</div>

</form>
