<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

global $post, $course, $lesson;

$courseid = get_post_meta( $post->ID, '_parent_course');
$parent_course = get_post($courseid[0]);
$parent_course_link = get_permalink( $parent_course->ID );
echo '<a class="llms-lesson-link" href="' . get_permalink( $parent_course->ID ) . '">' . $parent_course->post_title . '</a>';
echo '<br />';
var_dump( $lesson);
//var_dump( $parent_course );

//if ( ! $post->post_content ) return;
//echo $parent_course->post_title;
?>


	
	