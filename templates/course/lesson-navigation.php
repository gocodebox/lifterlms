<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $post, $course, $lesson, $lifterlms;

if ( ! empty( $lesson->get_previous_lesson() ) ) {
	$previous_lesson_link = get_permalink ( $lesson->get_previous_lesson() );
?>

	<a href="<?php echo $previous_lesson_link; ?>" alt="<?php echo __('Previous Lesson', 'lifterlms'); ?>">
		<?php echo __('Previous Lesson', 'lifterlms'); ?>
	</a>

<?php } 

if ( ! empty ( $lesson->get_next_lesson() ) ) {
	$next_lesson_link = get_permalink ( $lesson->get_next_lesson() );
?>

	<a href="<?php echo $next_lesson_link; ?>" alt="<?php echo __('Next Lesson', 'lifterlms'); ?>">
		<?php echo __('Next Lesson', 'lifterlms'); ?>
	</a>

<?php } ?>