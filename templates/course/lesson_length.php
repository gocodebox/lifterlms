<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

?>

<div class="llms-length-wrapper">

	<p class="llms-lesson_length"><?php printf( __( 'Estimated Time: <span class="length">%s</span>', 'lifterlms' ), $course->get_lesson_length() ); ?></p> 

</div>