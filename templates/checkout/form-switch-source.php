<?php
/**
 * Recurring Payment Source Switching
 * Included on single order view pages via Student Dashboard
 *
 * @package   LifterLMS/Templates
 * @since     [version]
 * @version   [version]
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$gateways = LLMS()->payment_gateways()->get_enabled_payment_gateways();
$status = $order->get( 'status' );
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

	<div class="llms-switch-payment-source-main llms-checkout-section">

		<?php llms_get_template( 'checkout/form-gateways.php', array(
			'gateways' => $gateways,
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
