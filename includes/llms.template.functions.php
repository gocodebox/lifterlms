<?php
/**
 * @author 		codeBOX
 * @category 	Admin
 * @package 	LifterLMS/Functions
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'lifterlms_template_single_title' ) ) {

	function lifterlms_template_single_title() {

		llms_get_template( 'course/title.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_short_description' ) ) {

	function lifterlms_template_single_short_description() {

		llms_get_template( 'course/short-description.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_full_description' ) ) {

	function lifterlms_template_single_full_description() {

		llms_get_template( 'course/full-description.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_price' ) ) {

	function lifterlms_template_single_price() {

		llms_get_template( 'course/price.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_lesson_length' ) ) {

	function lifterlms_template_single_lesson_length() {

		llms_get_template( 'course/lesson_length.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_video' ) ) {

	function lifterlms_template_single_video() {

		llms_get_template( 'course/video.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_difficulty' ) ) {

	function lifterlms_template_single_difficulty() {

		llms_get_template( 'course/difficulty.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_syllabus' ) ) {

	function lifterlms_template_single_syllabus() {

		llms_get_template( 'course/syllabus.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_single_parent_course' ) ) {

	function lifterlms_template_single_parent_course() {

		llms_get_template( 'course/parent_course.php' );
	}
}

/**
 * When the_post is called, put course data into a global.
 *
 * @param mixed $post
 * @return LLMS_Course
 */
function llms_setup_course_data( $post ) {
	
	if ($post->post_type == 'course') {
		unset( $GLOBALS['course'] );

		if ( is_int( $post ) )
			$post = get_post( $post );

		if ( empty( $post->post_type ) )
			return;

			$GLOBALS['course'] = get_course( $post );

			return $GLOBALS['course'];
		}



	if ($post->post_type == 'lesson') {
		unset( $GLOBALS['lesson'] );

		if ( is_int( $post ) )
			$post = get_post( $post );

		if ( empty( $post->post_type ) )
			return;

			$GLOBALS['lesson'] = get_lesson( $post );

			return $GLOBALS['lesson'];
		}

		if ($post->post_type == 'lesson') {

			$GLOBALS['lesson'] = get_lesson( $post );

		return $GLOBALS['lesson'];
	}

}
add_action( 'the_post', 'llms_setup_course_data' );



function llms_price( $price, $args = array() ) {
	
	return $price;
}

/**
 * Returns post array of data for sections associated with a course
 *
 * @param array
 * @return array
 */
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

/**
 * Returns post array of data for lessons associated with a course
 *
 * @param array
 * @return array
 */
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
