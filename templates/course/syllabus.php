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
 * @since [version] Filter lessons by the student's selected course stream.
 * @version [version]
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

			<?php
			$lesson_order = 0;
			$lessons      = llms_filter_lessons_by_stream( $section->get_lessons(), $course );
			if ( ! $lessons ) {
				continue;
			}
			?>

			<?php if ( apply_filters( 'llms_display_outline_section_titles', true ) ) : ?>
				<h3 class="llms-h3 llms-section-title"><?php echo esc_html( get_the_title( $section->get( 'id' ) ) ); ?></h3>
			<?php endif; ?>

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

		<?php endforeach; ?>

	<?php endif; ?>

	<div class="clear"></div>

</div>
