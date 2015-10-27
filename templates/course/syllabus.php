<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

if ( ! $course || !is_object($course) ) {

	$course = new LLMS_Course( $post->ID );
	
}

$html = '';
$html .= '<div class="clear"></div>';
$html .= '<div class="llms-lesson-tooltip"id="lockedTooltip"></div>';
$html .= '<div class="llms-syllabus-wrapper">';

	//get section data
	$sections = $course->get_children_sections();
	if ( !$sections ) {

		$html .= LLMS_Language::output('This course does not have any sections.');

	} else {

		foreach ( $sections as $section_child ) {
			$section = new LLMS_Section( $section_child->ID);

			$html .= '<h3 class="llms-h3 llms-section-title">' . $section->post->post_title . '</h3>';

			//get lesson data
			$lessons = $section->get_children_lessons();

			if ( !$lessons ) {

				$html .= LLMS_Language::output('This section does not have any lessons.');

			} else {

				foreach( $lessons as $lesson_child ) {
					$lesson = new LLMS_Lesson($lesson_child->ID);

					//determine if lesson is complete to show complete icon
					if( $lesson->is_complete() ) {
						$check = '<span class="llms-lesson-complete"><i class="fa fa-check-circle"></i></span>';
						$complete = ' is-complete';
					} 
					elseif ($lesson->get_is_free())
					{
						$check = LLMS_Svg::get_icon('llms-icon-free', '', '', 'llms-free-lesson-svg');
						$complete = ' is-complete';
					} else {
						$complete = $check = '';
					}

					//set permalink
					$permalink = 'javascript:void(0)';
					$page_restricted = llms_page_restricted($course->id);
					$title = '';
					$linkclass = '';

					if ( ! $page_restricted['is_restricted'] || $lesson->get_is_free()) {
					 	$permalink = get_permalink( $lesson->id );	
					 	$linkclass = 'llms-lesson-link';
					}
					else {
						$title = LLMS_Language::output( 'Take this course to unlock this lesson' );
						$linkclass = 'llms-lesson-link-locked';
					}			

					$html .= '<div class="llms-lesson-preview' . $complete . '">';
					
					$html .= '<a class="' . $linkclass . '" title = "'. $title . '" href="' . $permalink . '">';
					$html .= $check;
					$html .= '<div class="lesson-information">';
					$html .= '<h5 class="llms-h5 llms-lesson-title">' . $lesson->post->post_title . '</h5>';
					$html .= '<span class="llms-lesson-counter">' . $lesson->get_order() . __('of','lifterlms') . count($lessons) . '</span>';
					$html .= '<p class="llms-lesson-excerpt">'.llms_get_excerpt($lesson->id).'</p>';
					$html .= '</div>';
					$html .= '</a>';

					$html .= '</div>'; //end lesson-preview
					
					//$html .= '</div>';

		
				}


			}

		}

	}
$html .= '<div class="clear"></div>';
$html .= '</div>';

if (get_option('lifterlms_course_display_outline') === 'yes') {
	echo $html;
}

?>

