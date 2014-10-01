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

<form action="" method="post">

<p><input type="submit" class="button" name="process_order" value="<?php _e( 'Confirm Purchase', 'lifterlms' ); ?>" /></p>

<?php wp_nonce_field( 'process_order' ); ?>
 <input type="hidden" name="action" value="process_order" />
</form>

<?php do_action( 'lifterlms_after_checkout_login_form' ); ?>