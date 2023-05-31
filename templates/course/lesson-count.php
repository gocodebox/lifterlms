<?php
/**
 * Template: Lessons count in a Course.
 *
 * @package LifterLMS/Templates/Course
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

global $post;

$course        = new LLMS_Course( $post );
$lessons_count = $course->get_lessons_count();
?>

<div class="llms-meta llms-lessons-count">
	<?php printf( '<p>%1$s<span class="lessons-count">%2$s</span></p>', esc_html__( 'Number of Lessons: ', 'lifterlms' ), $lessons_count ); ?>
</div>
