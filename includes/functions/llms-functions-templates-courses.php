<?php
/**
 * Course template functions
 *
 * @package LifterLMS/Functions
 *
 * @since 4.11.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lifterlms_template_course_author' ) ) {
	/**
	 * Get single post author template
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
	function lifterlms_template_course_author() {
		llms_get_template( 'course/author.php' );
	}
}

if ( ! function_exists( 'lifterlms_template_course_stream_selector' ) ) {
	/**
	 * Output the course stream selector above the syllabus.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	function lifterlms_template_course_stream_selector() {

		global $post;
		$course = llms_get_post( $post );
		if ( ! $course || ! is_a( $course, 'LLMS_Course' ) || ! llms_course_streams_enabled( $course ) ) {
			return;
		}

		if ( ! get_current_user_id() || ! llms_is_user_enrolled( get_current_user_id(), $course->get( 'id' ) ) ) {
			return;
		}

		llms_get_template(
			'course/stream-selector.php',
			array(
				'course'  => $course,
				'streams' => llms_get_course_streams( $course ),
				'current' => llms_get_student_stream( get_current_user_id(), $course ),
			)
		);
	}
}
