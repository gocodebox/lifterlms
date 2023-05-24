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
$lessons_count = $course->get_lessons_count();
?>

<div class="llms-meta llms-lessons-count">
	<p><?php printf( __( 'Number of Lessons: <span class="lessons-count">%u</span>', 'lifterlms' ), $lessons_count ); ?></p>
</div>
