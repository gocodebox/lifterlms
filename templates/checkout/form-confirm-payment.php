<?php
/**
 * Checkout Form
 *
 * @author 	LifterLMS
 * @package LifterLMS/Templates
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( get_query_var( 'product' ) ) {

	$product = get_post( get_query_var( 'product' ) );

} elseif ( LLMS()->session->get( 'llms_order', array() ) ) {

	$session = LLMS()->session->get( 'llms_order', array() );
	$product_id = $session->product_id;
	$product = get_post( $product_id );

} else {

	llms_add_notice( __( 'Product not found.', 'lifterlms' ) );

}

$product_obj = new LLMS_Product( $product );

$subs = $product_obj->get_subscriptions();
if ( $subs ) {
	foreach ( $subs as $id => $sub ) {
		if ( $session->payment_option_id == $id ) {
			$recurring_html_price = $product_obj->get_subscription_price_html( $sub );
			break;
		}
	}
}
?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_before_checkout_confirm_form' ); ?>

<div class="llms-checkout-wrapper">

	<div class="llms-checkout">

		<h4><?php _e( 'Confirm Purchase', 'lifterlms' ); ?></h4>

		<!-- Product information -->
		<div class="llms-title-wrapper">
			<p class="llms-title"><?php echo $product->post_title; ?></p>
		</div>

		<?php do_action( 'lifterlms_checkout_confirm_after_title' ); ?>

		<!-- pricing options -->
		<div class="llms-price-wrapper">
			<div class="llms-payment-options llms-notice-box">

				<?php if ( 'recurring' == $session->payment_option ) : ?>

					<label><?php _e( 'Payment Terms:', 'lifterlms' ); ?></label>
					<strong><?php echo apply_filters( 'lifterlms_confirm_payment_get_recurring_price_html', ucfirst( $recurring_html_price ) ); ?></strong>

				<?php elseif ( 'single' == $session->payment_option ) : ?>

					<label><?php _e( 'Price:', 'lifterlms' ); ?></label>
					<strong><?php echo apply_filters( 'lifterlms_confirm_payment_get_single_price_html', ucfirst( $product_obj->get_price_html() ) ); ?></strong>

				<?php else : ?>
					<?php
					/**
					 * Allow themes / plugins / extensions to create custom confirmation messages
					 */
					do_action( 'lifterlms_checkout_confirm_html_'.$session->payment_option, $session );
					?>
				<?php endif; ?>

				<br />
				<label><?php echo __( 'Payment Method', 'lifterlms' ); ?>:</label>
				<strong><?php echo apply_filters( 'lifterlms_confirm_payment_method_text', $session->payment_type ); ?></strong>

			</div>

		</div>

		<form action="" method="POST">

			<div class="llms-clear-box llms-center-content">
				<input type="submit" class="button llms-button" name="process_order" value="<?php _e( 'Confirm Purchase', 'lifterlms' ); ?>" />
			</div>

			<?php wp_nonce_field( 'process_order' ); ?>

			 <input type="hidden" name="action" value="process_order" />

		</form>


	</div>


</div>

<?php do_action( 'lifterlms_after_checkout_confirm_form' ); ?>

