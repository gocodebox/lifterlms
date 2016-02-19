<?php
/**
 * Checkout Form
 *
 * @author 		lifterLMS
 * @package 	lifterLMS/Templates
 */

global $lifterlms;

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
	foreach ($subs as $id => $sub) {
		if ($session->payment_option_id == $id) {
			$recurring_html_price = $product_obj->get_subscription_price_html( $sub );
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

				<?php
				if ( 'recurring' == $session->payment_option ) {
					echo '<label>Payment Terms:</label> <strong>';
					echo $recurring_html_price;
					echo '</strong>';
				} elseif ( 'single' == $session->payment_option ) {
					echo '<label>Price:</label></strong> ';
					echo sprintf( __( apply_filters( 'lifterlms_single_payment_text','Single payment of %s' ), 'lifterlms' ), $product_obj->get_price_html() );
					echo '</strong>';

				} else {
					/**
					 * Allow themes / plugins / extensions to create custom confirmation messages
					 */
					do_action( 'lifterlms_checkout_confirm_html_'.$session->payment_option, $session );
				}
				?>
			<br />
			<label><?php echo __( 'Payment Method', 'lifterlms' ); ?>:</label>
			<strong><?php echo apply_filters( 'lifterlms_confirm_payment_method_text', $session->payment_type ); ?></strong>
		</div>
	</div>

	<form action="" method="post">
		<div class="llms-clear-box llms-center-content">
			<input type="submit" class="button llms-button" name="process_order" value="<?php _e( 'Confirm Purchase', 'lifterlms' ); ?>" />
		</div>
		<?php wp_nonce_field( 'process_order' ); ?>
		 <input type="hidden" name="action" value="process_order" />
	</form>
	<?php do_action( 'lifterlms_after_checkout_login_form' ); ?>
	</div>
</div>

