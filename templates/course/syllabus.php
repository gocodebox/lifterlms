<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

if ( ! $course ) {

	$course = new LLMS_Course( $post->ID );
	
}

$post_id = $post->ID;
$course = new LLMS_Course($post->ID);
?>
<div class="clear"></div>
<div class="llms-lesson-tooltip"id="lockedTooltip"></div>
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

if($syllabus[$i]['lessons']) {

	foreach( $syllabus[$i]['lessons'] as $key => $value) :

		array_push($lessons_array, $syllabus[$i]['lessons'][$key]['lesson_id']);

	endforeach;
}

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
			$page_restricted = llms_page_restricted($post_id);
			$title = '';
			$linkclass = '';

			if ( ! $page_restricted['is_restricted'] ) {
			 	$permalink = get_permalink( $value->ID );	
			 	$linkclass = 'llms-lesson-link';
			}
			else {
				$title = 'Take this course to unlock this lesson';
				$linkclass = 'llms-lesson-link-locked';
			}			

			echo '
				<div class="llms-lesson-preview' . $complete . '">
					<a class="' . $linkclass . '" title = "'. $title . '" href="' . $permalink . '">
						' . $check . '
						<div class="lesson-information">
							<h5 class="llms-h5 llms-lesson-title">' . $value->post_title . '</h5>
							<span class="llms-lesson-counter">' . $lesson_i . ' of ' . $total_lessons . '</span>
							<p class="llms-lesson-excerpt">'.llms_get_excerpt($value->ID).'</p>
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