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
LLMS_log($product);
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

	<a class="llms-membership-link" href="<?php the_permalink(); ?>">

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