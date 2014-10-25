<?php
/**
 * Checkout Form
 *
 * @author 		lifterLMS
 * @package 	lifterLMS/Templates
 */

global $lifterlms;



if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( get_query_var( 'course-id' ) ) {
	$product = get_post( get_query_var( 'course-id' ) );
}
elseif ( get_query_var( 'product-id' ) ) {
	$product = get_post( get_query_var( 'product-id' ) );
}
elseif ( LLMS()->session->get( 'llms_order', array() ) ) {

	$session = LLMS()->session->get( 'llms_order', array() );
	$product_id = $session->product_id;
	$product = get_post( $product_id );

}
else {

	llms_add_notice( __( 'Product not found.', 'lifterlms' ) );
}

$product_obj = new LLMS_Product($product);

$payment_options = $product_obj->get_payment_options();

$course = get_course($product);

$info_message = apply_filters( 'lifterlms_checkout_coupon_message', __( 'Have a coupon?', 'lifterlms' ) );
$info_message .= ' <a href="#" id="show-coupon">' . __( 'Click here to enter your code', 'lifterlms' ) . '</a>';

LLMS_log($product_obj->get_recurring_price_html());

$single_html_price = sprintf( __( 'Single payment of %s', 'lifterlms' ), $course->get_price_html() ); 
$recurring_html_price = $product_obj->get_recurring_price_html();
?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_before_checkout_login_form' ); ?>
<form action="" method="post">
<div class="llms-checkout-wrapper">
	<div class="llms-checkout">
	<?php echo  '<h4>' .__( 'Confirm Purchase', 'lifterlms' ) . '</h4>'; ?>

	<!-- Product information -->
	<div class="llms-title-wrapper">
		<p class="llms-title"><?php echo $product->post_title; ?></p>
	</div>


	<!-- pricing options -->
	<div class="llms-price-wrapper">
		<div class="llms-payment-options llms-notice-box">
			<?php 
			if ( in_array('recurring', $payment_options)  ) :
				$i = 0;

					foreach ($payment_options as $key => $value) :
						$i++;
					?>
						<p class="llms-payment-option llms-option">
							<input id="llms-payment-option_<?php echo $value; ?>" 
								class="llms-price-option-radio" 
								type="radio" 
								name="payment_option" 
								value="<?php echo $value; ?>"
								<?php if ($i == 1) { echo 'CHECKED'; } ?> 
							/>
							<label for="llms-payment-option_<?php echo $value; ?>">
								<span class="llms-radio"></span>
							
					<?php 
					if ($value == 'single') {
						echo $single_html_price;
					}
					if ($value == 'recurring') {
						echo $recurring_html_price;

					}
						?>
							</label>
						</p>
					<?php
					endforeach;
			endif;
			?>
		</div>
	</div>
</form>
	<!-- Coupon code entry form -->
	<?php llms_print_notice( $info_message, 'notice' ); ?>
		<form id="llms-checkout-coupon" method="post" style="display:none">

			<input type="text" name="coupon_code" class="llms-input-text" placeholder="<?php _e( 'Enter coupon code', 'lifterlms' ); ?>" id="coupon_code" value="" />
			<div class="llms-clear-box llms-center-content">
			<input type="submit" class="button llms-button" name="apply_coupon" value="<?php _e( 'Apply Coupon', 'lifterlms' ); ?>" />
			</div>
			<div class="clear"></div>
			</div>
		</form>
	<!-- display the final price -->
	<div class="llms-final-price-wrapper llms-clear-box">
		<h2 class="llms-price"><span class="llms-price-label">You Pay:</span><span class="llms-final-price"></span></p> 
	</div>



	

		<input type="hidden" name="product_id" value="<?php echo $course->id; ?>" />
	  	<input type="hidden" name="product_price" value="<?php echo $course->get_price(); ?>" />
	  	<input type="hidden" name="product_sku" value="<?php echo $course->get_sku(); ?>" />
	  	<input type="hidden" name="product_title" value="<?php echo $product->post_title; ?>" />

	<div class="llms-price-wrapper">
		<div class="llms-payment-methods llms-notice-box">

		<?php 
			if ( $available_gateways = LLMS()->payment_gateways()->get_available_payment_gateways() ) {
		
				if ( count( $available_gateways ) ) 
					$i = 0;

					current( $available_gateways )->set_current();

					foreach ( $available_gateways as $gateway ) {
						$i++
					?>
						<p class="payment_method_<?php echo $gateway->id; ?> llms-option">
							<input id="payment_method_<?php echo $gateway->id; ?>" 
								type="radio" class="input-radio" 
								name="payment_method" 
								value="<?php echo esc_attr( $gateway->id ); ?>"
								<?php if ($i == 1) { echo 'CHECKED'; } ?> 
							/>
							<label for="payment_method_<?php echo $gateway->id; ?>">
								<span class="llms-radio"></span>
								<?php echo $gateway->get_title(); ?>
							</label>
						</p>
					<?php
					}
				} else {

				echo '<p>' . __( 'Payment processing is currently disabled.', 'lifterlms' ) . '</p>';
			}
		?>
	</div>

		


		


		<div class="llms-clear-box llms-center-content">
			<input class="llms-button" type="submit" class="button" name="create_order_details" value="<?php _e( 'Buy Now', 'lifterlms' ); ?>" />
		</div>

		<?php wp_nonce_field( 'create_order_details' ); ?>
		<input type="hidden" name="action" value="create_order_details" />
	

	<?php do_action( 'lifterlms_after_checkout_login_form' ); ?>
	</div>
</div>
</form>
