<?php
/**
 * The Template for displaying all memberships (archive).
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;
get_header( 'memberships' ); ?>

	<?php

		do_action( 'lifterlms_before_main_content' );
	?>
		<?php if ( apply_filters( 'lifterlms_show_page_title', true ) ) : ?>

			<h1 class="page-title"><?php lifterlms_page_title(); ?></h1>

		<?php endif; ?>

		<?php do_action( 'lifterlms_archive_description' ); ?>

		<?php if ( have_posts() ) : ?>

			<?php

				do_action( 'lifterlms_before_memberships_loop' );

			?>

			<?php lifterlms_membership_loop_start(); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php llms_get_template_part( 'content', 'llms_membership' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php lifterlms_membership_loop_end(); ?>

			<?php

				do_action( 'lifterlms_after_memberships_loop' );

			?>

		<?php elseif ( ! lifterlms_membership_subcategories( array( 'before' => lifterlms_membership_loop_start( false ), 'after' => lifterlms_membership_loop_end( false ) ) ) ) : ?>

			<?php llms_get_template( 'loop/no-memberships-found.php' ); ?>

		<?php endif; ?>

	<?php

		do_action( 'lifterlms_after_main_content' );

	?>

	<?php

		do_action( 'lifterlms_sidebar' );

	?>

<?php get_sidebar( 'shop' ) ?>
<?php get_footer( 'shop' ); ?>