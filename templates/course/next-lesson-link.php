<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;
global $course, $lesson;
$next_lesson_link = get_permalink ( $course->get_next_lesson() );
?>
<a href="<?php echo $next_lesson_link; ?>" alt="<?php echo __('next lesson', 'lifterlms'); ?>">
	<?php echo __('Next Lesson', 'lifterlms'); ?>
</a>
