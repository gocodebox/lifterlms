<?php
/**
 * Lessons count in a Course.
 *
 * @package LifterLMS/Templates
 *
 * @since [version]
 * @version [version]
 *
 * @author      LifterLMS
 * @package     LifterLMS/Templates
 */

defined( 'ABSPATH' ) || exit;

global $post;

$course = new LLMS_Course( $post );

// Get Total number of lessons.
$sections = $course->get_sections();
$lessons  = array();
foreach ( $sections as $section ) {
	$lessons = array_merge( $lessons, $section->get_lessons() );
}
$lessons_count = count( $lessons );
?>

<div class="llms-meta llms-lessons-count">
	<p><?php printf( __( 'Number of Lessons: <span class="lessons-count">%u</span>', 'lifterlms' ), $lessons_count ); ?></p>
</div>
