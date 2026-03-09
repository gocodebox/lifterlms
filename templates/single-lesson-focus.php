<?php
/**
 * The Template for displaying all single lessons in focus mode.
 *
 * @package LifterLMS/Templates
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

// We do not load get_header() to prevent theme styles from fully taking over,
// but we must load wp_head() to ensure scripts and styles are enqueued.
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'llms-focus-mode' ); ?>>

<?php wp_body_open(); ?>

<div class="llms-focus-mode-wrapper">
	
	<header class="llms-focus-mode-header">
		<div class="llms-focus-mode-header-left">
			<?php lifterlms_template_single_parent_course(); ?>
		</div>
		<div class="llms-focus-mode-header-center">
			<h1 class="llms-focus-mode-title"><?php the_title(); ?></h1>
		</div>
		<div class="llms-focus-mode-header-right">
			<?php
			// Optional: User profile or progress could go here.
			?>
		</div>
	</header>

	<div class="llms-focus-mode-main">
		
		<main class="llms-focus-mode-content">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<div class="llms-lesson-content">
					<?php the_content(); ?>
				</div>
				<?php
			endwhile; // end of the loop.
			?>
		</main>

		<aside class="llms-focus-mode-sidebar">
			<div class="llms-focus-mode-sidebar-inner">
				<?php
				$lesson    = llms_get_post( get_the_ID() );
				$course_id = $lesson ? $lesson->get( 'parent_course' ) : 0;
				if ( $course_id ) {
					echo '<h3>' . esc_html__( 'Course Syllabus', 'lifterlms' ) . '</h3>';
					echo do_shortcode( '[lifterlms_course_outline course_id="' . intval( $course_id ) . '"]' );
				}
				?>
			</div>
		</aside>

	</div>

</div>

<?php wp_footer(); ?>

</body>
</html>
