<?php
/**
 * The Template for displaying all single memberships.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $lifterlms_loop, $product;

if ( empty( $lifterlms_loop['loop'] ) )
	$lifterlms_loop['loop'] = 0;
if ( empty( $lifterlms_loop['columns'] ) )
	$lifterlms_loop['columns'] = apply_filters( 'loop_memberships_columns', 4 );
$lifterlms_loop['loop']++;
$classes = array();
if ( 0 == ( $lifterlms_loop['loop'] - 1 ) % $lifterlms_loop['columns'] || 1 == $lifterlms_loop['columns'] )
	$classes[] = 'first';
if ( 0 == $lifterlms_loop['loop'] % $lifterlms_loop['columns'] )
	$classes[] = 'last';
?>
<li <?php post_class( $classes ); ?>>

	<?php do_action( 'lifterlms_before_memberships_loop_item' ); ?>
<!--BEGIN CUSTOM JUNK
Hey there ben. You should have an idea of what I'm trying to do from my comments in slack.
Really I just need yo to clean this up and test that it's doing what it's supposed to do then check
it into development.

It's not my favorite way right now but for now the logic can sit in the template (see above, it's UGLY! )
-->

<?php if (get_option('redirect_to_checkout')) { 

	$product = new LLMS_Product( get_the_ID() );

	if ( ! is_user_logged_in() ) {
	$account_url = get_permalink( llms_get_page_id( 'myaccount' ) );
	$account_redirect = add_query_arg( 'product-id', get_the_ID(), $account_url );
	?>
		<a class="llms-membership-link" href="<?php echo $account_redirect; ?>">
	<?php 
	} elseif ( ! llms_is_user_member( get_current_user_id(), get_the_ID() ) ) { ?>
		<a class="llms-membership-link" href="<?php echo $product->get_checkout_url(); ?>">
		
<?php }
} else { ?>
<!--BEGIN CUSTOM JUNK
this is the end of my customization. the rest of the page is standard stuff you don't need to mess with

-->
	<a class="llms-membership-link" href="<?php the_permalink(); ?>">
<?php } ?>
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