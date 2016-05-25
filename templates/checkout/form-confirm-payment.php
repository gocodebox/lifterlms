<?php
/**
 * Checkout Form
 *
 * @author 	LifterLMS
 * @package LifterLMS/Templates
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$session = LLMS()->session->get( 'llms_order' );
if ( $session ) {
	$order = llms_get_order_by_key( $session );
	if ( $order ) {
		$product = new LLMS_Product( $order->get_product_id() );
	}
}

if ( ! $product || ! $order ) {
	llms_add_notice( __( 'The order for this transaction could not be located.', 'lifterlms' ) );
	return;
}
?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_before_checkout_confirm_form' ); ?>

<div class="llms-checkout-wrapper">

	<div class="llms-checkout">

		<h4><?php _e( 'Confirm Purchase', 'lifterlms' ); ?></h4>

		<!-- Product information -->
		<div class="llms-title-wrapper">
			<p class="llms-title"><?php echo $product->get_title(); ?></p>
		</div>

		<?php do_action( 'lifterlms_checkout_confirm_after_title' ); ?>

		<!-- pricing options -->
		<div class="llms-price-wrapper">
			<div class="llms-payment-options llms-notice-box">

				<?php if ( 'recurring' == $order->get_type() ) : ?>

					<label><?php _e( 'Payment Terms:', 'lifterlms' ); ?></label>
					<strong><?php echo apply_filters( 'lifterlms_confirm_payment_get_recurring_price_html', ucfirst( $product->get_subscription_price_html( $order->get_product_subscription_array( false ), $order->get_coupon_id() ) ) ); ?></strong>

				<?php elseif ( 'single' == $order->get_type() ) : ?>

					<label><?php _e( 'Price:', 'lifterlms' ); ?></label>
					<strong><?php echo apply_filters( 'lifterlms_confirm_payment_get_single_price_html', ucfirst( $product->get_single_price_html( $order->get_coupon_id() ) ) ); ?></strong>

				<?php else : ?>
					<?php
					/**
					 * Allow themes / plugins / extensions to create custom confirmation messages
					 */
					do_action( 'lifterlms_checkout_confirm_html_' . $order->get_type(), $order );
					?>
				<?php endif; ?>

				<br />
				<label><?php echo __( 'Payment Method', 'lifterlms' ); ?>:</label>
				<strong><?php echo apply_filters( 'lifterlms_confirm_payment_method_text', $order->get_payment_gateway_title() ); ?></strong>

			</div>

		</div>

		<form action="" method="POST">

			<div class="llms-clear-box llms-center-content">
				<input type="submit" class="button llms-button" name="llms_confirm_order" value="<?php _e( 'Confirm Purchase', 'lifterlms' ); ?>" />
			</div>

			<?php wp_nonce_field( 'llms_confirm_order' ); ?>

			 <input type="hidden" name="action" value="llms_confirm_order" />

		</form>


	</div>


</div>

<?php do_action( 'lifterlms_after_checkout_confirm_form' ); ?>

