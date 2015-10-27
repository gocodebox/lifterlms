<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

$post_id = $post->ID;

?>

<div class="llms-syllabus-wrapper">

<?php

$array = array();
$lessons = array();

$syllabus = $course->get_syllabus();

if ($syllabus ) {
	
	foreach($syllabus as $key => $value ) :

		array_push($array, $syllabus[$key]['section_id']);

	endforeach;
}
else {
	echo __( 'This course does not have any lessons.', 'lifterlms' );
}


$the_stuff = get_section_data($array);

$i = 0;
foreach($the_stuff as $key => $value) :

	$lessons_array = array();

	echo '<h3 class="llms-h3 llms-section-title">' . $value->post_title . '</h3>';


	foreach( $syllabus[$i]['lessons'] as $key => $value) :

		array_push($lessons_array, $syllabus[$i]['lessons'][$key]['lesson_id']);

	endforeach;

	$the_lessons = get_lesson_data($lessons_array);


	$lesson_i = 1; // lesson iterator
	$total_lessons = count($the_lessons);
	foreach($the_lessons as $key => $value) :

		if ( $value->ID == $post_id ){
			//echo $value->post_title;
		}
		else {
			elseif ( llms_is_user_enrolled( get_current_user_id(), $course->id ) ) {
				echo '
					<div class="llms-lesson-preview">
						<a class="llms-lesson-link" href="' . get_permalink( $value->ID ) . '">
							<h5 class="llms-h5 llms-lesson-title">' . $value->post_title . '</h5>
							<span class="llms-lesson-counter">'.$lesson_i.__('of','lifterlms').$total_lessons.'</span>
							<p>'.$value->post_excerpt.'</p>
						</a>
					</div>
				';
			}
			else {
				echo '
					<div class="llms-lesson-preview">
						<a class="llms-lesson-link" href="' . $course->get_checkout_url() . '">
							<h5 class="llms-h5 llms-lesson-title">' . $value->post_title . '</h5>
							<span class="llms-lesson-counter">'.$lesson_i.__('of','lifterlms').$total_lessons.'</span>
							<p>'.$value->post_excerpt.'</p>
						</a>
					</div>
				';
			}
		}
	$lesson_i++;
	endforeach;

	$i++;

endforeach;

?>

</div>