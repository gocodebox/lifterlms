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
/**
 * When the_post is called, put course data into a global.
 *
 * @param mixed $post
 * @return LLMS_Course
 */
function llms_setup_course_data( $post ) {
	unset( $GLOBALS['course'] );

	if ( is_int( $post ) )
		$post = get_post( $post );

	if ( empty( $post->post_type ) )
		return;

	$GLOBALS['course'] = get_course( $post );

	return $GLOBALS['course'];
}
add_action( 'the_post', 'llms_setup_course_data' );



function llms_price( $price, $args = array() ) {
	
	return $price;
}
