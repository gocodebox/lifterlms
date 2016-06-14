<?php
/**
 * Template for the Course Syllabus Displayed on individual course pages
 *
 * @author 		codeBOX
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// cease execution if the course outline is disabled
if ( get_option( 'lifterlms_course_display_outline' ) === 'no' ) {

	return;

}

global $post, $course;

// ensure that we haven't lost the global $course
if ( ! $course || ! is_object( $course ) ) {

	$course = new LLMS_Course( $post->ID );

}

// retrieve sections to use in the template
$sections = $course->get_children_sections();
?>

<div class="clear"></div>
<div class="llms-lesson-tooltip" id="lockedTooltip"></div>
<div class="llms-syllabus-wrapper">

	<?php if ( ! $sections ) : ?>

		<?php echo __( 'This course does not have any sections.', 'lifterlms' ); ?>

	<?php else : ?>

		<?php foreach ( $sections as $section_child ) : ?>
			<?php $section = new LLMS_Section( $section_child->ID ); ?>

			<?php if ( get_option( 'lifterlms_course_display_outline_titles', 'yes' ) === 'yes' ) : ?>
				<h3 class="llms-h3 llms-section-title"><?php echo $section->post->post_title; ?></h3>
			<?php endif; ?>

			<?php $lessons = $section->get_children_lessons(); ?>
			<?php if ( ! $lessons ) : ?>

				<?php echo __( 'This section does not have any lessons.', 'lifterlms' ); ?>

			<?php else : ?>
				<?php foreach ( $lessons as $lesson_child ) : ?>
					<?php
					$lesson = new LLMS_Lesson( $lesson_child->ID );

					/**
					 * @todo  refactor
					 */
					//determine if lesson is complete to show complete icon
					if ( $lesson->is_complete() ) {
						$check = '<span class="llms-lesson-complete"><i class="fa fa-' . apply_filters( 'lifterlms_lesson_complete_icon', 'check-circle' ) . '"></i></span>';
						$complete = ' is-complete has-icon';
					} elseif ( $course->is_user_enrolled( get_current_user_id() ) && get_option( 'lifterlms_display_lesson_complete_placeholders' ) === 'yes') {
						$complete = ' has-icon';
						$check = '<span class="llms-lesson-complete-placeholder"><i class="fa fa-' . apply_filters( 'lifterlms_lesson_complete_icon', 'check-circle' ) . '"></i></span>';
					} elseif ( $lesson->get_is_free() ) {
						$check = '<span class="llms-free-lesson">' . LLMS_Svg::get_icon( 'llms-icon-free', '', '', 'llms-free-lesson-svg' ) .'</span>';
						$complete = ' has-icon';
					} else {
						$complete = '';
						$check = '';
					}

					//set permalink
					$permalink = 'javascript:void(0)';
					$page_restricted = llms_page_restricted( $course->id );
					$title = '';
					$linkclass = '';

					if ( ! $page_restricted['is_restricted'] || $lesson->get_is_free()) {
						$permalink = get_permalink( $lesson->id );
						$linkclass = 'llms-lesson-link';
					} else {
						$title = __( 'Take this course to unlock this lesson', 'lifterlms' );
						$linkclass = 'llms-lesson-link-locked';
					}
					?>

					<div class="llms-lesson-preview<?php echo $complete; ?>">
						<a class="<?php echo $linkclass; ?>" title = "<?php echo $title; ?>" href="<?php echo $permalink; ?>">

							<?php  if ( get_option( 'lifterlms_course_display_outline_lesson_thumbnails', 'no' ) === 'yes' && get_the_post_thumbnail( $lesson->id ) ) : ?>
								<div class="llms-lesson-thumbnail"><?php echo get_the_post_thumbnail( $lesson->id ); ?></div>
							<?php endif; ?>

							<?php echo $check; ?>
							<div class="llms-lesson-information">
								<h5 class="llms-h5 llms-lesson-title"><?php echo $lesson->post->post_title; ?></h5>
								<span class="llms-lesson-counter"><?php echo $lesson->get_order(); ?> <?php _e( 'of' , 'lifterlms' ); ?> <?php echo count( $lessons ); ?></span>
								<p class="llms-lesson-excerpt"><?php echo llms_get_excerpt( $lesson->id ); ?></p>
							</div>
							<div class="clear"></div>
						</a>
					</div>

				<?php endforeach; ?>

			<?php endif; ?>

		<?php endforeach; ?>

	<?php endif; ?>

	<div class="clear"></div>
</div>
