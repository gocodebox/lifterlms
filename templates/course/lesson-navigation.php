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
	$previous_lesson_link = get_permalink ( $lesson->get_previous_lesson() );

?>

	<div class="llms-lesson-preview prev-lesson">
		<a class="llms-lesson-link" href="<?php echo $previous_lesson_link; ?>" alt="<?php echo __('Previous Lesson', 'lifterlms'); ?>">
			<?php echo __('Previous Lesson', 'lifterlms'); ?>
		</a>
	</div>

<?php }



if ( $lesson->get_next_lesson() ) {
	$next_lesson_link = get_permalink ( $lesson->get_next_lesson() );
?>

	<div class="llms-lesson-preview next-lesson">
		<a class="llms-lesson-link" href="<?php echo $next_lesson_link; ?>" alt="<?php echo __('Next Lesson', 'lifterlms'); ?>">
			<?php echo __('Next Lesson', 'lifterlms'); ?>
		</a>
	</div>

<?php } ?>

</nav>