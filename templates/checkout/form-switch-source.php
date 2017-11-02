<?php
/**
 * Recurring Payment Source Switching
 * Included on single order view pages via Student Dashboard
 *
 * @package   LifterLMS/Templates
 * @since     3.10.0
 * @version   3.10.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$status = $order->get( 'status' );
$gateway = LLMS()->payment_gateways()->get_gateway_by_id( $confirm );
?>

<form action="" class="llms-switch-payment-source llms-checkout-wrapper" id="llms-product-purchase-form" method="POST">

	<?php llms_form_field( array(
		'columns' => 12,
		'classes' => 'llms-button-secondary',
		'id' => 'llms_update_payment_method',
		'value' => __( 'Update Payment Method', 'lifterlms' ),
		'last_column' => true,
		'required' => false,
		'type'  => 'button',
	) ); ?>

	<div class="llms-switch-payment-source-main llms-checkout-section"<?php echo $confirm ? ' style="display:block;"' : ''; ?>>

		<?php if ( ! $confirm ) : ?>

			<?php llms_get_template( 'checkout/form-gateways.php', array(
				'gateways' => LLMS()->payment_gateways()->get_enabled_payment_gateways(),
				'selected_gateway' => $order->get( 'payment_gateway' ),
				'plan' => llms_get_post( $order->get( 'plan_id' ) ),
			) ); ?>

			<?php if ( 'llms-active' !== $status ) : ?>
				<ul class="llms-order-summary">
					<li>
						<?php printf( __( 'Due Now: %s', 'lifterlms' ), '<span class="price-regular">' . $order->get_price( 'total' ) . '</span>' ); ?>
					</li>
				</ul>
			<?php endif; ?>

		<?php elseif ( $confirm && $gateway ) : ?>

			<div class="llms-payment-method">
				<?php do_action( 'lifterlms_checkout_confirm_before_payment_method', $gateway->get_id(), 'switch' ); ?>
				<span class="llms-gateway-title"><span class="llms-label"><?php _e( 'Payment Method:', 'lifterlms' ); ?></span> <?php echo $gateway->get_title(); ?></span>
				<?php if ( $gateway->get_icon() ) : ?>
					<span class="llms-gateway-icon"><?php echo $gateway->get_icon(); ?></span>
				<?php endif; ?>
				<?php if ( $gateway->get_description() ) : ?>
					<div class="llms-gateway-description"><?php echo wpautop( wptexturize( $gateway->get_description() ) ); ?></div>
				<?php endif; ?>
				<?php do_action( 'lifterlms_checkout_confirm_after_payment_method', $gateway->get_id(), 'switch' ); ?>
			</div>

			<input name="llms_payment_gateway" type="hidden" value="<?php echo $gateway->get_id(); ?>">

		<?php endif; ?>

		<?php wp_nonce_field( 'llms_switch_order_source', '_switch_source_nonce' ); ?>
		<input name="order_id" type="hidden" value="<?php echo $order->get( 'id' ); ?>">
		<input name="llms_switch_action" type="hidden" value="<?php echo ( 'llms-active' === $status ) ? 'switch': 'pay'; ?>">

		<?php llms_form_field( array(
			'columns' => 12,
			'classes' => 'llms-button-primary',
			'id' => 'llms_save_payment_method',
			'value' => ( 'llms-active' === $status ) ? __( 'Save Payment Method', 'lifterlms' ) : __( 'Save and Pay Now', 'lifterlms' ),
			'last_column' => true,
			'required' => false,
			'type'  => 'submit',
		) ); ?>

	</div>

</form>
