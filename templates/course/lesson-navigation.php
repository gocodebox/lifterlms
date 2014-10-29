<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $post, $course, $lesson, $lifterlms;
?>

<nav class="llms-course-navigation">

<?php
if ( $lesson->get_previous_lesson() ) {
	$previous_lesson_id = $lesson->get_previous_lesson();
	$previous_lesson_link = get_permalink ( $previous_lesson_id );
?>

	<div class="llms-lesson-preview prev-lesson">
		<a class="llms-lesson-link" href="<?php echo $previous_lesson_link; ?>" alt="<?php echo __('Previous Lesson', 'lifterlms'); ?>">
			<span class="llms-span"><?php echo __('Previous Lesson', 'lifterlms'); ?>:</span>
			<h5 class="llms-h5"><?php echo get_the_title( $previous_lesson_id ) ?></h5>
		</a>
	</div>

<?php }


$parent_course_id = $lesson->get_parent_course();
$parent_course_link = get_permalink ( $parent_course_id );
?>
	<div class="llms-lesson-preview parent-course">
		<a class="llms-lesson-link" href="<?php echo $parent_course_link; ?>" alt="<?php echo __('Back to Course', 'lifterlms'); ?>">
			<span class="llms-span"><?php echo __('Back to Course', 'lifterlms'); ?>:</span>
			<h5 class="llms-h5"><?php echo get_the_title( $parent_course_id ) ?></h5>
		</a>
	</div>
<?php

if ( $lesson->get_next_lesson() ) {
	$next_lesson_id = $lesson->get_next_lesson();
	$next_lesson_link = get_permalink ( $next_lesson_id );
?>

	<div class="llms-lesson-preview next-lesson">
		<a class="llms-lesson-link" href="<?php echo $next_lesson_link; ?>" alt="<?php echo __('Next Lesson', 'lifterlms'); ?>">
			<span class="llms-span"><?php echo __('Next Lesson', 'lifterlms'); ?>:</span>
			<h5 class="llms-h5"><?php echo get_the_title( $next_lesson_id ) ?></h5>
			<p><?php echo get_the_excerpt( $next_lesson_id ) ?></p>
		</a>
	</div>

<?php } ?>

</nav>