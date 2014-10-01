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
}
elseif ( LLMS()->session->get( 'llms_order', array() ) ) {

	$session = LLMS()->session->get( 'llms_order', array() );
	$product_id = $session->product_id;
	$product = get_post( $product_id );

}
else {

	llms_add_notice( __( 'Product not found.', 'lifterlms' ) );
}

$course = get_course($product);

$info_message = apply_filters( 'lifterlms_checkout_coupon_message', __( 'Have a coupon?', 'lifterlms' ) );
$info_message .= ' <a href="#" id="show-coupon">' . __( 'Click here to enter your code', 'lifterlms' ) . '</a>';


?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_before_checkout_login_form' ); ?>

<h3><?php _e( 'Confirm Purchase', 'lifterlms' ); ?></h3>

<!-- Product information -->
<div class="llms-title-wrapper">

	<p class="llms-title"><?php echo $product->post_title; ?></p>

</div>

<div class="llms-price-wrapper">

	<p class="llms-price"><?php echo $course->get_price_html(); ?></p> 

</div>

<!-- Coupon code entry form -->
<?php llms_print_notice( $info_message, 'notice' ); ?>
<form id="llms-checkout-coupon" method="post" style="display:none">

	<input type="text" name="coupon_code" class="input-text" placeholder="<?php _e( 'Enter coupon code', 'lifterlms' ); ?>" id="coupon_code" value="" />

	<input type="submit" class="button" name="apply_coupon" value="<?php _e( 'Apply', 'lifterlms' ); ?>" />

	<div class="clear"></div>

</form>

<!-- display the final price -->
<div class="llms-final-price-wrapper">

	<p class="llms-price"><?php echo $course->get_price_html(); ?></p> 

</div>

<form action="" method="post">

	<input type="hidden" name="product_id" value="<?php echo $course->id; ?>" />
  	<input type="hidden" name="product_price" value="<?php echo $course->get_price(); ?>" />
  	<input type="hidden" name="product_sku" value="<?php echo $course->get_sku(); ?>" />
  	<input type="hidden" name="product_title" value="<?php echo $product->post_title; ?>" />

<ul class="payment_methods methods">

	<?php 
		if ( $available_gateways = LLMS()->payment_gateways()->get_available_payment_gateways() ) {
	
			if ( count( $available_gateways ) ) 

				current( $available_gateways )->set_current();

				foreach ( $available_gateways as $gateway ) {
				?>
					<li class="payment_method_<?php echo $gateway->id; ?>">
						<input id="payment_method_<?php echo $gateway->id; ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php //checked( $gateway->chosen, true ); ?> />
						<label for="payment_method_<?php echo $gateway->id; ?>"><?php echo $gateway->get_title(); ?></label>
					</li>
				<?php
				}
			} else {

			echo '<p>' . __( 'Payment processing is currently disabled.', 'lifterlms' ) . '</p>';

		}
	?>
	</ul>

	<p><input type="submit" class="button" name="create_order_details" value="<?php _e( 'Buy Now', 'lifterlms' ); ?>" /></p>

	<?php wp_nonce_field( 'create_order_details' ); ?>
	<input type="hidden" name="action" value="create_order_details" />
</form>

<?php do_action( 'lifterlms_after_checkout_login_form' ); ?>