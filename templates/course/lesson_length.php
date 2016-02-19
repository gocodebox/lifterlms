<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post, $course;

if ( ! $course  || ! is_object( $course ) ) {

	$course = new LLMS_Course( $post->ID );

}

?>
<?php if ( $length = $course->get_lesson_length() ) : ?>
<div class="llms-length-wrapper">
	<p class="llms-lesson_length"><?php printf( __( 'Estimated Time: <span class="length">%s</span>', 'lifterlms' ), $length ); ?></p> 
</div>
<?php endif; ?>
