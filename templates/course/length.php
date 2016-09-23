<?php
/**
 * LifterLMS Course Length Meta Info
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course;

if ( ! $course  || ! is_object( $course ) ) {
	$course = new LLMS_Course( $post->ID );
}

if ( ! $course->get( 'length' ) ) {
	return;
}
?>

<div class="llms-meta llms-course-length">
	<p><?php printf( __( 'Estimated Time: <span class="length">%s</span>', 'lifterlms' ), $course->get( 'length' ) ); ?></p>
</div>

