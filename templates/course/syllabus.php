<?php
/**
 * Template for the Course Syllabus Displayed on individual course pages
 *
 * @author LifterLMS
 * @package LifterLMS/Templates
 *
 * @since 1.0.0
 * @since 3.24.0 Unknown.
 * @since 4.4.0 Pass the progressive lesson order value to the lesson-preview template.
 * @since 7.1.3 Add paragraph tag to wrap message when sections or lessons are empty.
 * @version 7.1.3
 */
defined( 'ABSPATH' ) || exit;
global $post;
$course   = new LLMS_Course( $post );
$sections = $course->get_sections();
?>

<div class="clear"></div>

<div class="llms-syllabus-wrapper">

	<?php if ( ! $sections ) : ?>

		<p><?php esc_html_e( 'This course does not have any sections.', 'lifterlms' ); ?></p>

	<?php else : ?>

		<?php foreach ( $sections as $section ) : ?>

			<?php $lesson_order = 0; ?>

			<?php if ( apply_filters( 'llms_display_outline_section_titles', true ) ) : ?>
				<h3 class="llms-h3 llms-section-title"><?php echo esc_html( get_the_title( $section->get( 'id' ) ) ); ?></h3>
			<?php endif; ?>

			<?php $lessons = $section->get_lessons(); ?>
			<?php if ( $lessons ) : ?>

				<?php foreach ( $lessons as $lesson ) : ?>

					<?php
					llms_get_template(
						'course/lesson-preview.php',
						array(
							'lesson'        => $lesson,
							'total_lessons' => count( $lessons ),
							'order'         => ++$lesson_order,
						)
					);
					?>

				<?php endforeach; ?>

			<?php else : ?>

				<p><?php esc_html_e( 'This section does not have any lessons.', 'lifterlms' ); ?></p>

			<?php endif; ?>

		<?php endforeach; ?>

	<?php endif; ?>

	<div class="clear"></div>

</div>
