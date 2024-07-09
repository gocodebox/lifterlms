<?php
/**
 * Lesson navigation template
 *
 * @package LifterLMS/Templates
 *
 * @since Unknown Introduced.
 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
 * @version 5.7.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$lesson = new LLMS_Lesson( $post->ID );

$prev_id = $lesson->get_previous_lesson();
$next_id = $lesson->get_next_lesson();
?>

<nav class="llms-course-navigation">

	<?php if ( $prev_id ) : ?>

		<div class="llms-course-nav llms-prev-lesson">
			<?php
			llms_get_template(
				'course/lesson-preview.php',
				array(
					'lesson'   => new LLMS_Lesson( $prev_id ),
					'pre_text' => __( 'Previous Lesson', 'lifterlms' ),
				)
			);
			?>
		</div>

	<?php endif; ?>

	<?php if ( ! $prev_id || ! $next_id ) : ?>
		<div class="llms-course-nav llms-back-to-course">
			<div class="llms-lesson-preview">
				<a class="llms-lesson-link" href="<?php echo esc_url( get_permalink( $lesson->get( 'parent_course' ) ) ); ?>">
					<section class="llms-main">
						<div class="llms-pre-text"><?php esc_html_e( 'Back to Course', 'lifterlms' ); ?></div>
						<div class="llms-lesson-title"><?php echo esc_html( get_the_title( $lesson->get( 'parent_course' ) ) ); ?></div>
					</section>
				</a>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $next_id ) : ?>

		<div class="llms-course-nav llms-next-lesson">
			<?php
			llms_get_template(
				'course/lesson-preview.php',
				array(
					'lesson'   => new LLMS_Lesson( $next_id ),
					'pre_text' => __( 'Next Lesson', 'lifterlms' ),
				)
			);
			?>
		</div>

	<?php endif; ?>



</nav>
<div class="clear"></div>
