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

	foreach( $syllabus as $key => $value ) :

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
			echo $value->post_title;
		}
		else {

			$lesson = new LLMS_Lesson($value->ID);
			if( $lesson->is_complete() ) {
				$check = '<span class="llms-lesson-complete"><i class="fa fa-check-circle"></i></span>';
				$complete = ' is-complete';
			} else {
				$complete = $check = '';
			}

			//set permalink
			$permalink = 'javascript:void(0)';
			$page_restricted = llms_page_restricted($value->ID);

			if ( ! $page_restricted['is_restricted'] ) {
				
			// 	if ( llms_is_user_enrolled(get_current_user_id(), $course->id ) && ! lesson_start_date_in_future(get_current_user_id(), $lesson->id) ) {
			 		$permalink = get_permalink( $value->ID );	
			// 	}
			}
			

			echo '
				<div class="llms-lesson-preview' . $complete . '">
					<a class="llms-lesson-link" href="' . $permalink . '">
						' . $check . '
						<div class="lesson-information">
							<h5 class="llms-h5 llms-lesson-title">' . $value->post_title . '</h5>
							<span class="llms-lesson-counter">' . $lesson_i . ' of ' . $total_lessons . '</span>
							<p class="llms-lesson-excerpt">'.$value->post_excerpt.'</p>
						</div>
					</a>
				</div>
			';

		}
	$lesson_i++;
	endforeach;

	$i++;

endforeach;

?>

</div>