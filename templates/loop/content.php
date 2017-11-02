<?php
/**
 * The Template for displaying all single courses.
 *
 * @author 		codeBOX
 * @package 	LifterLMS/Templates
 * @since       1.0.0
 * @version     3.14.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<li <?php post_class( 'llms-loop-item' ); ?>>
	<div class="llms-loop-item-content">

	<?php
		/**
		 * lifterlms_before_loop_item
		 * @hooked lifterlms_loop_featured_video - 8
		 * @hooked lifterlms_loop_link_start - 10
		 */
		do_action( 'lifterlms_before_loop_item' );
	?>

	<?php
		/**
		 * lifterlms_before_loop_item_title
		 * @hooked lifterlms_template_loop_thumbnail - 10
		 * @hooked lifterlms_template_loop_progress - 15
		 */
		do_action( 'lifterlms_before_loop_item_title' );
	?>

	<h4 class="llms-loop-title"><?php the_title(); ?></h4>

	<footer class="llms-loop-item-footer">
		<?php
			/**
			 * lifterlms_after_loop_item_title
			 * @hooked lifterlms_template_loop_author - 10
			 * @hooked lifterlms_template_loop_length - 15
			 * @hooked lifterlms_template_loop_difficulty - 20
			 *
			 * On Student Dashboard & "Mine" Courses Shortcode
			 * @hooked lifterlms_template_loop_enroll_status - 25
			 * @hooked lifterlms_template_loop_enroll_date - 30
			 */
			do_action( 'lifterlms_after_loop_item_title' );
		?>
	</footer>

	<?php
		/**
		 * lifterlms_after_loop_item
		 * @hooked lifterlms_loop_link_end - 5
		 */
		do_action( 'lifterlms_after_loop_item' );
	?>

	</div><!-- .llms-loop-item-content -->
</li><!-- .llms-loop-item -->
