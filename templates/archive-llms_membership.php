<?php
/**
 * The Template for displaying all memberships (archive).
 *
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header( 'llms_shop' ); ?>

<main class="content llms-content" role="main">
<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php do_action( 'lifterlms_archive_description' ); ?>

	<?php if ( apply_filters( 'lifterlms_show_page_title', true ) ) : ?>

		<h1 class="page-title"><?php lifterlms_page_title(); ?></h1>

	<?php endif; ?>

	<?php if ( have_posts() ) : ?>

		<?php do_action( 'lifterlms_before_memberships_loop' ); ?>

		<?php lifterlms_membership_loop_start(); ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<?php llms_get_template_part( 'content', 'llms_membership' ); ?>

			<?php endwhile; // end of the loop. ?>

		<?php lifterlms_membership_loop_end(); ?>

		<?php do_action( 'lifterlms_after_memberships_loop' ); ?>

		<?php llms_get_template_part( 'course', 'pagination' ); ?>

	<?php else : ?>

		<?php llms_get_template( 'loop/no-courses-found.php' ); ?>

	<?php endif; ?>

</div></main>

<?php do_action( 'lifterlms_sidebar' ); ?>

<?php get_footer(); ?>
