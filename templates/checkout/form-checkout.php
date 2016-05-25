<?php
/**
 * Checkout Form
 *
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

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
	llms_print_notices();
	return;

}

$coupon = null;
if ( isset( $_POST['coupon_code'] ) ) {
	$coupon = new LLMS_Coupon( $_POST['coupon_code'] );
}

llms_print_notices();

$current_user = wp_get_current_user();
$product_obj = new LLMS_Product( $product );
$available_gateways = LLMS()->payment_gateways()->get_available_payment_gateways();
?>

<form action="" method="POST" id="llms-product-purchase-form">
	<div class="llms-checkout-wrapper">
		<div class="llms-checkout">

			<h4 class="llms-checkout-title"><?php echo apply_filters( 'lifterlms_checkout_form_title', __( 'Confirm Purchase', 'lifterlms' ) ); ?></h4>

			<?php if ( llms_is_alternative_checkout_enabled() && ! is_user_logged_in() ) : ?>

				<?php llms_get_template( 'checkout/form-login-register.php' ); ?>

			<?php elseif ( llms_is_alternative_checkout_enabled() && is_user_logged_in() ) : ?>

				<div class="llms-form-wrapper">
					<div class="llms-notice-box">
						<?php printf( _x( 'You are logged in as %s', 'Identify the current user by email address', 'lifterlms' ), $current_user->user_email ); ?>
						<a href="<?php echo wp_logout_url( add_query_arg( $_GET, get_permalink() ) ); ?>" title="<?php _e( 'Logout?', 'lifterlms' ); ?>"><?php _e( 'Logout', 'lifterlms' ); ?></a>
					</div>
				</div>

			<?php endif; ?>

			<div class="llms-title-wrapper">
				<h4 class="llms-title"><span><?php _e( 'Purchasing:', 'lifterlms' ); ?> </span><?php echo $product->post_title; ?></h4>
			</div>

			<?php do_action( 'lifterlms_after_checkout_form_title' ); ?>

			<?php llms_get_template( 'checkout/form-pricing.php', array( 'product' => $product_obj ) ); ?>

			<?php llms_get_template( 'checkout/form-coupon.php', array( 'coupon' => $coupon ) ); ?>

			<!-- display the final price -->
			<div class="llms-final-price-wrapper llms-clear-box">
				<h3 class="llms-price"><span class="llms-price-label"><?php _e( 'You Pay:', 'lifterlms' ); ?></span><span class="llms-final-price"></span></h3>
			</div>

			<input id="llms-product-id" type="hidden" name="product_id" value="<?php echo $product->ID; ?>" />

			<div class="llms-price-wrapper">

				<div class="llms-payment-methods llms-notice-box">

					<?php if ( ! $available_gateways ) : ?>
						<p><?php _e( 'Payment processing is currently disabled.', 'lifterlms' ); ?></p>
					<?php else : $ii = 0; current( $available_gateways )->set_current(); ?>
						<?php foreach ( $available_gateways as $gateway ) : $ii++; ?>
							<?php
							$checked = '';
							if ( ! empty( $_POST['payment_method'] ) ) {
								if ( ( $gateway->payment_type . '_' . esc_attr( $gateway->id ) ) == $_POST['payment_method'] ) {
									$checked = ' checked="checked"';
								}
							} elseif ( 1 == $ii ) {
								$checked = ' checked="checked"';
							}
							?>
							<p class="payment_method_<?php echo $gateway->id; ?> llms-option">
								<input class="input-radio" data-payment-type="<?php echo $gateway->payment_type; ?>" id="payment_method_<?php echo $gateway->id; ?>" name="payment_method" type="radio" value="<?php echo $gateway->payment_type; ?>_<?php echo esc_attr( $gateway->id ); ?>"<?php echo $checked; ?>>
								<label for="payment_method_<?php echo $gateway->id; ?>">
									<span class="llms-radio"></span>
									<?php echo $gateway->get_title(); ?>
								</label>
							</p>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>

				<?php apply_filters( 'lifterlms_form_checkout_cc', llms_get_template( 'checkout/form-checkout-cc.php', 'lifterlms' ) ); ?>

				<div class="llms-clear-box llms-center-content">
					<?php if ( count( $available_gateways ) ) : ?>
						<input class="button llms-button" name="create_pending_order" type="submit" value="<?php _e( 'Buy Now', 'lifterlms' ); ?>"<?php echo (is_user_logged_in() || llms_is_alternative_checkout_enabled() ? '' : ' disabled="disabled"'); ?>>
					<?php endif; ?>
				</div>

				<?php wp_nonce_field( 'create_pending_order' ); ?>
				<input type="hidden" name="action" value="create_pending_order" />
				<?php do_action( 'lifterlms_after_checkout_form' ); ?>
				</div>
		</div><!-- .llms-checkout -->
	</div><!-- .llms-checkout-wrapper -->
</form>

