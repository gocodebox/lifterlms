<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $product;

if ( ! $product ) {

	$product = new LLMS_Product( $post->ID );

}

$single_price = $product->get_single_price();
$rec_price = $product->get_recurring_price();
$memberships_required = get_post_meta( $product->id, '_llms_restricted_levels', true );

?>
<div class="llms-purchase-link-wrapper">
<?php

if ( ! is_user_logged_in() ) {
	$message = apply_filters( 'lifterlms_checkout_message', '' );

	if ( ! empty( $message ) ) {
	}

	//if membership required to take course
	if ($memberships_required) {

		//if there is more than 1 membership that can view the content then redirect to memberships page
		if (count( $memberships_required ) > 1) {
			$membership_url = get_permalink( llms_get_page_id( 'memberships' ) );
		} //if only 1 membership level is assigned take visitor to the membership page
		else {
			$membership_url = get_permalink( $memberships_required[0] );
		}
		?>
		<a href="<?php echo $membership_url; ?>" class="button llms-button llms-purchase-button"><?php echo _e( 'Sign Up', 'lifterlms' ); ?></a>	
			<?php
	} else {
		$account_url = get_permalink( llms_get_page_id( 'myaccount' ) );

		$account_redirect = add_query_arg( 'product-id', get_the_ID(), $account_url );
		?>
		<a href="<?php echo $account_redirect; ?>" class="button llms-button llms-purchase-button"><?php echo _e( 'Sign Up', 'lifterlms' ); ?></a>

			<?php
	}
} elseif ( ! llms_is_user_member( get_current_user_id(), $product->id ) ) {

	if ( $single_price > 0 || $rec_price > 0) {
		?>
		<a href="<?php echo $product->get_checkout_url(); ?>" class="button llms-button llms-purchase-button"><?php echo _e( 'Sign Up', 'lifterlms' ); ?></a>
		<?php
	} else { ?>

			<form action="" method="post">
				<input type="hidden" name="payment_option" value="none_0" />
				<input type="hidden" name="product_id" value="<?php echo $product->id; ?>" />
			  	<input type="hidden" name="product_price" value="<?php echo $product->get_price(); ?>" />
			  	<input type="hidden" name="product_sku" value="<?php echo $product->get_sku(); ?>" />
			  	<input type="hidden" name="product_title" value="<?php echo $post->post_title; ?>" />

				<input id="payment_method_<?php echo 'none' ?>" type="hidden" name="payment_method" value="none" <?php //checked( $gateway->chosen, true ); ?> />

				<p><input type="submit" class="button llms-button llms-purchase-button" name="create_order_details" value="<?php _e( 'Sign Up', 'lifterlms' ); ?>" /></p>

				<?php wp_nonce_field( 'create_order_details' ); ?>
				<input type="hidden" name="action" value="create_order_details" />
			</form>

		<?php }
}
?>
</div>
