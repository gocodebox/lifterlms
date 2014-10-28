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

	LLMS_log($session);
}
else {
	llms_add_notice( __( 'Product not found.', 'lifterlms' ) );
}

$product_obj = new LLMS_Product($product);

$course = get_course($product);
$single_html_price = sprintf( __( 'Single payment of %s', 'lifterlms' ), $course->get_price_html() ); 
$recurring_html_price = $product_obj->get_recurring_price_html();
?>

<?php llms_print_notices(); ?>

<?php do_action( 'lifterlms_before_checkout_login_form' ); ?>
<div class="llms-checkout-wrapper">
	<div class="llms-checkout">
		<h4><?php _e( 'Confirm Purchase', 'lifterlms' ); ?></h4>
		<!-- Product information -->
		<div class="llms-title-wrapper">
			<p class="llms-title"><?php echo $product->post_title; ?></p>
		</div>

		<!-- pricing options -->
		<div class="llms-price-wrapper">
			<div class="llms-payment-options llms-notice-box">
		
				<?php 
				if ($session->payment_option == 'recurring') {
					echo '<label>Payment Terms:</label> <strong>';
					echo $recurring_html_price;
					echo '</strong>';
				}
				else {
					echo '<label>Price:</label>echo </strong>';
					echo $single_html_price;
					echo '</strong>';
				}
				?>
			<br />
			<label>Payment Method:</label>
			<strong><?php echo $session->payment_method; ?></strong>
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

