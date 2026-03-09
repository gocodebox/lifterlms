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

$lesson    = llms_get_post( get_the_ID() );
$course_id = $lesson ? $lesson->get( 'parent_course' ) : 0;
$student   = llms_get_student();
$progress  = ( $student && $course_id ) ? $student->get_progress( $course_id, 'course' ) : 0;
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
		<div class="llms-focus-mode-header-right">
			<?php echo lifterlms_course_progress_bar( $progress, false, false, false ); ?>
		</div>
	</header>

	<div class="llms-focus-mode-body">

		<aside class="llms-focus-mode-sidebar">
			<div class="llms-focus-mode-sidebar-header">
				<h3><?php esc_html_e( 'Course Syllabus', 'lifterlms' ); ?></h3>
			</div>
			<div class="llms-focus-mode-sidebar-content">
				<?php
				if ( $course_id ) {
					echo do_shortcode( '[lifterlms_course_outline course_id="' . intval( $course_id ) . '"]' );
				}
				?>
			</div>
		</aside>

		<main class="llms-focus-mode-content">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<h1 class="llms-focus-mode-title"><?php the_title(); ?></h1>
				<div class="llms-lesson-content">
					<?php the_content(); ?>
				</div>
				<?php
			endwhile;
			?>
		</main>

	</div>

</div>

<?php wp_footer(); ?>

</body>
</html>
