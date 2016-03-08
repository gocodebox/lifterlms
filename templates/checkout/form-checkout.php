<?php
/**
 * Checkout Form
 *
 * @author 		lifterLMS
 * @package 	lifterLMS/Templates
 */

global $lifterlms;

if ( ! defined( 'ABSPATH' ) ) { exit; }

llms_print_notices();



// moved above login to enable more performance friendly translation of i18n strings on this page
do_action( 'lifterlms_before_checkout_form' );

if ( get_query_var( 'product-id' ) ) {
	$product = get_post( get_query_var( 'product-id' ) );
} elseif ( get_query_var( 'product-id' ) ) {
	$product = get_post( get_query_var( 'product-id' ) );
} elseif ( LLMS()->session->get( 'llms_order', array() ) ) {
	$session = LLMS()->session->get( 'llms_order', array() );
	$product_id = $session->product_id;
	$product = get_post( $product_id );
} else {
	llms_add_notice( __( 'Product not found.', 'lifterlms' ) );
}

$product_obj = new LLMS_Product( $product );

$payment_options = $product_obj->get_payment_options();

$coupon_session = LLMS()->session->get( 'llms_coupon', array() );

if ($coupon_session) {

	$info_message = sprintf( __( 'Coupon code "%s" has been applied to your order', 'lifterlms' ), $coupon_session->coupon_code );

	$savings = ( 'percent' == $coupon_session->type ) ? $coupon_session->amount.'%' : '$'.$coupon_session->amount;

	$info_message .= ' '.sprintf( __( '(%s off)', 'lifterlms' ), $savings );
} else {
	$info_message = apply_filters( 'lifterlms_checkout_coupon_message', __( 'Have a coupon?', 'lifterlms' ) );
	$info_message .= ' <a href="#" id="show-coupon">' . __( 'Click here to enter your code', 'lifterlms' ) . '</a>';
}

?>

<form action="" method="post" id="llms-product-purchase-form">
	<div class="llms-checkout-wrapper">
		<div class="llms-checkout">
		<?php echo  '<h4>' .
			apply_filters( 'lifterlms_checkout_form_title', __( 'Confirm Purchase', 'lifterlms' ) ) . '</h4>'; ?>

		<!-- Product information -->
		<div class="llms-title-wrapper">
			<h4 class="llms-title"><?php echo $product->post_title; ?></h4>
		</div>

		<?php do_action( 'lifterlms_after_checkout_form_title' ); ?>

		<!-- pricing options -->
		<div class="llms-price-wrapper">
			<div class="llms-payment-options llms-notice-box">
				<?php do_action( 'lifterlms_checkout_form_before_payment_options' ); ?>
				<?php
				$i = 0;
				$checked = false;
				foreach ($payment_options as $key => $value) :
					if ( 'single' == $value ) :
						$i++;
					?>
						<p class="llms-payment-option llms-option">
							<input id="llms-payment-option_<?php echo $value; ?>"
								class="llms-price-option-radio"
								type="radio"
								name="payment_option"
								value="<?php echo $value . '_' . $key; ?>"
								<?php if ( 1 == $i ) { echo 'checked'; $checked = true; } ?>
							/>
							<label for="llms-payment-option_<?php echo $value; ?>">
								<span class="llms-radio"></span>
								<?php echo ucfirst( $product_obj->get_price_html() ); ?>
							</label>
						</p>
					<?php
					elseif ( 'recurring' == $value ) :
						$i++;
						$subs = $product_obj->get_subscriptions();

						if ( ! empty( $subs )) :

							foreach ($subs as $id => $sub) : ?>
								<p class="llms-payment-option llms-option">
									<input id="llms-payment-option_<?php echo $value . '_' . $id; ?>"
										class="llms-price-option-radio"
										type="radio"
										name="payment_option"
										value="<?php echo $value . '_' . $id; ?>"
										<?php if ( 1 == $i && ! $checked) { echo 'checked'; $checked = true;} ?>
									/>
									<label for="llms-payment-option_<?php echo $value . '_' . $id; ?>">
										<span class="llms-radio"></span>
										<?php
											echo $product_obj->get_subscription_price_html( $sub );
										?>
									</label>
								</p>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php else : ?>
						<?php
						/**
						 * Allow addons / plugins / themes to define custom payment options
						 * This action will be called to allow them to output some custom html for the payment options
						 */
						?>
						<?php do_action( 'lifterlms_checkout_payment_option_'.$value, $product_obj, $value ); ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php do_action( 'lifterlms_checkout_form_after_payment_options' ); ?>
			</div>
		</form>

		<!-- Coupon code entry form -->
		<div class="llms-coupon-entry llms-notice-box">
			<?php if ( $coupon_session ) : ?>
				<form class="llms-remove-coupon" id="llms-remove-coupon" method="post">
					<div class="llms-center-content">
						<input type="submit" class="llms-button-text" name="llms_remove_coupon" value="[<?php _e( 'Remove', 'lifterlms' ); ?>]" />
						<input type="hidden" name="product_id" value="<?php echo $product->ID; ?>" />
					</div>
					<div class="clear"></div>
					<?php wp_nonce_field( 'llms-remove-coupon' ); ?>
				</form>
			<?php endif; ?>
			<?php llms_print_notice( $info_message, 'notice' ); ?>
			<form id="llms-checkout-coupon" method="post" style="display:none">
				<input type="text" name="coupon_code" class="llms-input-text" placeholder="<?php _e( 'Enter coupon code', 'lifterlms' ); ?>" id="coupon_code" value="" />
				<div class="llms-clear-box llms-center-content">
					<input type="submit" class="button llms-button" name="llms_apply_coupon" value="<?php _e( 'Apply Coupon', 'lifterlms' ); ?>" />
					<input type="hidden" name="product_id" value="<?php echo $product->ID; ?>" />
				</div>
				<div class="clear"></div>
				<?php wp_nonce_field( 'llms-checkout-coupon' ); ?>
			</form>
		</div>

		</div>
		<!-- display the final price -->
		<div class="llms-final-price-wrapper llms-clear-box">
			<h3 class="llms-price"><span class="llms-price-label"><?php _e( 'You Pay:', 'lifterlms' ); ?></span><span class="llms-final-price"></span></h3>
		</div>

		<input type="hidden" name="product_id" value="<?php echo $product->ID; ?>" />
	  	<input type="hidden" name="product_price" value="<?php echo $product_obj->get_price(); ?>" />
	  	<input type="hidden" name="product_sku" value="<?php echo $product_obj->get_sku(); ?>" />
	  	<input type="hidden" name="product_title" value="<?php echo $product->post_title; ?>" />

		<div class="llms-price-wrapper">
			<div class="llms-payment-methods llms-notice-box">

				<?php
				if ( $available_gateways = LLMS()->payment_gateways()->get_available_payment_gateways() ) {

					if ( count( $available_gateways ) ) :
						$ii = 0;
						current( $available_gateways )->set_current();

						foreach ( $available_gateways as $gateway ) :
							$ii++;
							echo ''
							?>
							<p class="payment_method_<?php echo $gateway->id; ?> llms-option">
								<input id="payment_method_<?php echo $gateway->id; ?>"
									type="radio" class="input-radio"
									name="payment_method"
									value="<?php echo $gateway->payment_type; ?>_<?php echo esc_attr( $gateway->id ); ?>"
									data-payment-type="<?php echo $gateway->payment_type; ?>"
									<?php if ( ! empty( $_POST['payment_method'] ) ) :
										if ( ( $gateway->payment_type . '_' . esc_attr( $gateway->id ) ) == $_POST['payment_method'] ) :
											echo 'CHECKED';
										 	endif;
									 	elseif ( 1 == $ii ) :
									 		echo 'CHECKED';

									endif; ?>
								/>
								<label for="payment_method_<?php echo $gateway->id; ?>">
									<span class="llms-radio"></span>
									<?php echo $gateway->get_title(); ?>
								</label>
							</p>
							<?php
						endforeach;
					endif;
				} else {
					echo '<p>' . __( 'Payment processing is currently disabled.', 'lifterlms' ) . '</p>';
				}
				?>
			</div>

			<?php apply_filters( 'lifterlms_form_checkout_cc', llms_get_template( 'checkout/form-checkout-cc.php', 'lifterlms' ) ); ?>

			<div class="llms-clear-box llms-center-content">
				<?php if ( count( $available_gateways ) ) : ?>
				<input class="llms-button"
					type="submit"
					class="button"
					name="create_order_details"
					<?php echo (is_user_logged_in() ? '' : 'disabled="disabled"'); ?>
					value="<?php _e( 'Buy Now', 'lifterlms' ); ?>" />
				<?php endif; ?>
			</div>

			<?php wp_nonce_field( 'create_order_details' ); ?>
			<input type="hidden" name="action" value="create_order_details" />
			<?php do_action( 'lifterlms_after_checkout_form' ); ?>
			</div>
		</div>
	</div>
</form>

