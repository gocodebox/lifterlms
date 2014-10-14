<?php
/**
 * The Template for displaying all single courses.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $course, $lifterlms_loop;

// Store loop count we're currently on
if ( empty( $lifterlms_loop['loop'] ) )
	$lifterlms_loop['loop'] = 0;

// Store column count for displaying the grid
if ( empty( $lifterlms_loop['columns'] ) )
	$lifterlms_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );

// Ensure visibility
if ( ! $course || ! $course->is_visible() )
	return;

// Increase loop count
$lifterlms_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 == ( $lifterlms_loop['loop'] - 1 ) % $lifterlms_loop['columns'] || 1 == $lifterlms_loop['columns'] )
	$classes[] = 'first';
if ( 0 == $lifterlms_loop['loop'] % $lifterlms_loop['columns'] )
	$classes[] = 'last';
?>
<li <?php post_class( $classes ); ?>>

	<?php do_action( 'lifterlms_before_shop_loop_item' ); ?>

	<a class="llms-course-link" href="<?php the_permalink(); ?>">

		<?php

			do_action( 'lifterlms_before_shop_loop_item_title' );

		?>

		<h3 class="llms-title"><?php the_title(); ?></h3>

		<?php

			do_action( 'lifterlms_after_shop_loop_item_title' );

		?>

	</a>

	<?php do_action( 'lifterlms_after_shop_loop_item' ); ?>

</li>