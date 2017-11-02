<?php
/**
 * The Template for displaying all single memberships.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
global $lifterlms_loop, $product;

if ( empty( $lifterlms_loop['loop'] ) ) {
	$lifterlms_loop['loop'] = 0; }
if ( empty( $lifterlms_loop['columns'] ) ) {
	$lifterlms_loop['columns'] = apply_filters( 'loop_memberships_columns', 4 ); }
$lifterlms_loop['loop']++;
$classes = array();
if ( 0 == ( $lifterlms_loop['loop'] - 1 ) % $lifterlms_loop['columns'] || 1 == $lifterlms_loop['columns'] ) {
	$classes[] = 'first'; }
if ( 0 == $lifterlms_loop['loop'] % $lifterlms_loop['columns'] ) {
	$classes[] = 'last'; }
?>
<li <?php post_class( $classes ); ?>>

	<?php do_action( 'lifterlms_before_memberships_loop_item' ); ?>

<?php
	// check to see if auto-redirect option is set
if ( get_option( 'redirect_to_checkout' ) == 'yes' ) {

	// create product object and get prices from the object
	$product = new LLMS_Product( get_the_ID() );
	$single_price = $product->get_single_price();
	$rec_price = $product->get_recurring_price();

	// check to see if user is logged in
	// if not, redirect to login / registration page
	if ( ! is_user_logged_in() ) {
		$account_url = get_permalink( llms_get_page_id( 'myaccount' ) );
		$account_redirect = add_query_arg( 'product-id', get_the_ID(), $account_url );
		?>
		<a class="llms-membership-link" href="<?php echo $account_redirect; ?>">
		<?php
	} // End if().
	elseif ( ! llms_is_user_enrolled( get_current_user_id(), get_the_ID() ) ) {
		// if price is greater than 0 redirect to checkout page
		if ( $single_price > 0 || $rec_price > 0 ) {
			?>
			<a class="llms-membership-link" href="<?php echo $product->get_checkout_url(); ?>">
			<?php
		} // End if().
		else {
			?>
			<form action="" method="post" id="hiddenform">
				<input type="hidden" name="payment_option" value="none_0" />
				<input type="hidden" name="product_id" value="<?php echo $product->id; ?>" />
			  	<input type="hidden" name="product_price" value="<?php echo $product->get_price(); ?>" />
			  	<input type="hidden" name="product_sku" value="<?php echo $product->get_sku(); ?>" />
			  	<input type="hidden" name="product_title" value="<?php echo $post->post_title; ?>" />

				<input id="payment_method_<?php echo 'none' ?>" type="hidden" name="payment_method" value="none" <?php //checked( $gateway->chosen, true ); ?> />
				<?php wp_nonce_field( 'create_order_details' ); ?>
				<input type="hidden" name="action" value="create_order_details" />
			</form>
			<a class="llms-membership-link" onclick="document.getElementById('hiddenform').submit();" href="#">
			<?php
		}
	}
} else { ?>
	<a class="llms-membership-link" href="<?php the_permalink(); ?>">
<?php }// End if().
	?>
		<?php

			do_action( 'lifterlms_before_memberships_loop_item_title' );

		?>

		<h3 class="llms-title"><?php the_title(); ?></h3>

		<?php

			do_action( 'lifterlms_after_memberships_loop_item_title' );

		?>

	</a>

	<?php do_action( 'lifterlms_after_memberships_loop_item' ); ?>

</li>
