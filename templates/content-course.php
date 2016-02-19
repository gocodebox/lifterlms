<?php
/**
 * The Template for displaying all single courses.
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course, $lifterlms_loop;

if ( ! $course ) {
	$course = new LLMS_Course( $post->ID );
}

// Store loop count we're currently on
if ( empty( $lifterlms_loop['loop'] ) ) {
	$lifterlms_loop['loop'] = 0; }

// Store column count for displaying the grid
if ( empty( $lifterlms_loop['columns'] ) ) {
	$lifterlms_loop['columns'] = apply_filters( 'loop_shop_columns', 4 ); }

// Increase loop count
$lifterlms_loop['loop']++;

// Extra post classes
$classes = array();
// check if course is complete so we can add a completed class to the link element
if ( $course->get_percent_complete() == 100) {
	$classes[] = 'llms-course-complete'; }


if ( 0 == ( $lifterlms_loop['loop'] - 1 ) % $lifterlms_loop['columns'] || 1 == $lifterlms_loop['columns'] ) {
	$classes[] = 'first'; }
if ( 0 == $lifterlms_loop['loop'] % $lifterlms_loop['columns'] ) {
	$classes[] = 'last'; }
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
