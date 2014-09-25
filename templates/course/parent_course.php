<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

global $post, $course, $lesson;

$courseid = get_post( $lesson->get_parent_course() );
$parent_course = get_post($courseid);
$parent_course_link = get_permalink( $parent_course->ID );

echo '<a class="llms-lesson-link" href="' . get_permalink( $course->id ) . '">' . get_the_title( $course->id ) . '</a>';

?>


	
	