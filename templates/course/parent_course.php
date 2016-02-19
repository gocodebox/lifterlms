<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post;

$lesson = new LLMS_Lesson( $post->ID );

printf( __( '<p class="llms-parent-course-link">Back to: <a class="llms-lesson-link" href="%s">%s</a></p>', 'lifterlms' ), get_permalink( $lesson->parent_course ), get_the_title( $lesson->parent_course ) );
?>
