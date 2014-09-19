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

function get_section_data ($sections) {
	global $post; 
	$html = '';
	$args = array(
	    'post_type' => 'section',
	);

	$query = get_posts( $args );

	$array = array();

	foreach($sections as $key => $value) :
		
		foreach($query as $post) : 
			
			if ($value == $post->ID) {
				$array[$post->ID] = $post;
			}

		endforeach;

	endforeach;

	return $array; 

}

function get_lesson_data ($lessons) {
	global $post; 
	$html = '';
	$args = array(
	    'post_type' => 'lesson',
	);

	$query = get_posts( $args );

	$array = array();


	foreach($lessons as $key => $value) :

		foreach($query as $post) :

			if ($value == $post->ID) {
				$array[$value] = $post;
			}
		endforeach;	

	endforeach;

	return $array; 
}


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

	echo '<h3>' . $value->post_title . '</h3>';


	foreach( $syllabus[$i]['lessons'] as $key => $value) :

		array_push($lessons_array, $syllabus[$i]['lessons'][$key]['lesson_id']);

	endforeach;

	$the_lessons = get_lesson_data($lessons_array);


	foreach($the_lessons as $key => $value) :

		echo '<p>' . $value->post_title . '</p>';

	endforeach;

	$i++;

endforeach;

?>

</div>