<?php
/**
 * The Template for displaying single lesson, quiz, or other focus-mode content (e.g. assignments via add-on).
 *
 * @package LifterLMS/Templates
 *
 * @since 10.0.0
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

$course    = llms_get_post_parent_course( get_the_ID() );
$course_id = $course ? $course->get( 'id' ) : 0;
$student   = llms_get_student();
$progress  = ( $student && $course_id ) ? $student->get_progress( $course_id, 'course' ) : 0;

$post_type = get_post_type();
$lesson    = null;
if ( 'lesson' === $post_type ) {
	$lesson = llms_get_post( get_the_ID() );
} else {
	$current_post = llms_get_post( get_the_ID() );
	if ( $current_post && is_callable( array( $current_post, 'get' ) ) ) {
		$lesson_id = $current_post->get( 'lesson_id' );
		if ( $lesson_id ) {
			$lesson = llms_get_post( $lesson_id );
		}
	}
}
$prev_id = ( $lesson && is_callable( array( $lesson, 'get_previous_lesson' ) ) ) ? $lesson->get_previous_lesson() : false;
$next_id = ( $lesson && is_callable( array( $lesson, 'get_next_lesson' ) ) ) ? $lesson->get_next_lesson() : false;

$prev_restricted = $prev_id ? llms_page_restricted( $prev_id, get_current_user_id() ) : array( 'is_restricted' => false );
$next_restricted = $next_id ? llms_page_restricted( $next_id, get_current_user_id() ) : array( 'is_restricted' => false );
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
			<?php if ( 'lesson' === get_post_type() ) : ?>
				<div class="llms-parent-course-link">
					<a class="llms-lesson-link" href="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><?php echo esc_html__( 'Back to Course', 'lifterlms' ); ?></a>
				</div>
			<?php elseif ( ( $current = llms_get_post( get_the_ID() ) ) && method_exists( $current, 'get' ) && $current->get( 'lesson_id' ) ) : ?>
				<?php lifterlms_template_quiz_return_link(); ?>
			<?php endif; ?>
		</div>
		<div class="llms-focus-mode-header-center">
			<?php echo lifterlms_course_progress_bar( $progress, false, false, false ); ?>
		</div>
		<div class="llms-focus-mode-header-nav">
			<?php if ( $prev_id ) : ?>
				<?php if ( $prev_restricted['is_restricted'] ) : ?>
					<span class="llms-focus-mode-nav-btn llms-focus-mode-nav-prev llms-lesson-locked" data-tooltip-msg="<?php echo esc_attr( wp_strip_all_tags( llms_get_restriction_message( $prev_restricted ) ) ); ?>" aria-label="<?php esc_attr_e( 'Previous Lesson', 'lifterlms' ); ?>">
				<?php else : ?>
					<a href="<?php echo esc_url( get_permalink( $prev_id ) ); ?>" class="llms-focus-mode-nav-btn llms-focus-mode-nav-prev" aria-label="<?php esc_attr_e( 'Previous Lesson', 'lifterlms' ); ?>">
				<?php endif; ?>
					<svg class="llms-focus-mode-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z"/></svg>
					<span class="llms-focus-mode-nav-label"><?php esc_html_e( 'Previous Lesson', 'lifterlms' ); ?></span>
				<?php echo $prev_restricted['is_restricted'] ? '</span>' : '</a>'; ?>
			<?php endif; ?>
			<?php if ( $next_id ) : ?>
				<?php if ( $next_restricted['is_restricted'] ) : ?>
					<span class="llms-focus-mode-nav-btn llms-focus-mode-nav-next llms-lesson-locked" data-tooltip-msg="<?php echo esc_attr( wp_strip_all_tags( llms_get_restriction_message( $next_restricted ) ) ); ?>" aria-label="<?php esc_attr_e( 'Next Lesson', 'lifterlms' ); ?>">
				<?php else : ?>
					<a href="<?php echo esc_url( get_permalink( $next_id ) ); ?>" class="llms-focus-mode-nav-btn llms-focus-mode-nav-next" aria-label="<?php esc_attr_e( 'Next Lesson', 'lifterlms' ); ?>">
				<?php endif; ?>
					<span class="llms-focus-mode-nav-label"><?php esc_html_e( 'Next Lesson', 'lifterlms' ); ?></span>
					<svg class="llms-focus-mode-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"/></svg>
				<?php echo $next_restricted['is_restricted'] ? '</span>' : '</a>'; ?>
			<?php endif; ?>
		</div>
	</header>

	<div class="llms-focus-mode-body">

		<aside class="llms-focus-mode-sidebar">
			<div class="llms-focus-mode-sidebar-header">
				<h3><?php esc_html_e( 'Lessons', 'lifterlms' ); ?></h3>
			</div>
			<div class="llms-focus-mode-sidebar-content">
				<?php
				if ( $course_id ) {
					echo do_shortcode( '[lifterlms_course_outline collapse="true" toggles="true" course_id="' . intval( $course_id ) . '"]' );
				}
				?>
			</div>
			<button class="llms-focus-mode-sidebar-toggle" type="button" aria-label="<?php esc_attr_e( 'Toggle sidebar', 'lifterlms' ); ?>">
				<svg class="llms-chevron-left" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z"/></svg>
				<svg class="llms-chevron-right" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"/></svg>
			</button>
		</aside>

		<div class="llms-focus-mode-main">
			<?php
				$content_classes = array( 'llms-focus-mode-content' );
				$content_classes = apply_filters( 'llms_focus_mode_content_classes', $content_classes );
			?>
			<main class="<?php echo esc_attr( implode( ' ', array_filter( $content_classes ) ) ); ?>">
				<?php
				while ( have_posts() ) :
					the_post();
					$lesson_content_classes = array( 'llms-lesson-content', 'entry-content', 'is-layout-constrained' );
					$lesson_content_classes = apply_filters( 'llms_focus_mode_lesson_content_classes', $lesson_content_classes );
					?>
					<h1 class="llms-focus-mode-title"><?php the_title(); ?></h1>
					<div class="<?php echo esc_attr( implode( ' ', array_filter( $lesson_content_classes ) ) ); ?>">
						<?php
						/**
						 * Renders the post content in focus mode.
						 *
						 * @since 10.0.0
						 *
						 * @see llms_focus_mode_render_content() Default handler.
						 */
						do_action( 'llms_focus_mode_the_content' );
						?>
					</div>
					<?php
				endwhile;
				?>
			</main>

			<?php if ( 'lesson' === $post_type && $lesson ) : ?>
				<footer class="llms-focus-mode-footer">
					<?php lifterlms_template_complete_lesson_link(); ?>
				</footer>
			<?php endif; ?>

		</div>

	</div>

	<?php if ( 'lesson' === $post_type && $lesson ) : ?>
		<footer class="llms-focus-mode-footer llms-focus-mode-footer--mobile">
			<?php lifterlms_template_complete_lesson_link(); ?>
		</footer>
	<?php endif; ?>

</div>

<?php wp_footer(); ?>

</body>
</html>
