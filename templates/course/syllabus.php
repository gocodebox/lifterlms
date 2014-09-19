<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

?>

<div class="llms-syllabus-wrapper">

<?php

$array = array();
$lessons = array();

$syllabus = $course->get_syllabus();

foreach($syllabus as $key => $value ) :

	array_push($array, $syllabus[$key]['section_id']);

endforeach;


$the_stuff = get_section_data($array);
$i = 0;


foreach($the_stuff as $key => $value) :

	$lessons_array = array();

	echo '<h3 class="llms-h3">' . $value->post_title . '</h3>';


	foreach( $syllabus[$i]['lessons'] as $key => $value) :

		array_push($lessons_array, $syllabus[$i]['lessons'][$key]['lesson_id']);

	endforeach;

	$the_lessons = get_lesson_data($lessons_array);


	foreach($the_lessons as $key => $value) :

		echo '<a class="llms-lesson-link" href="' . get_permalink( $value->ID ) . '">' . $value->post_title . '</a>';
		echo '<br>';

	endforeach;

	$i++;

endforeach;

?>

</div>