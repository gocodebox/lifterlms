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
	 * Output the course stream selector above the syllabus / outline.
	 *
	 * Resolves the parent course when the current post is a lesson or quiz
	 * (focus mode and outline shortcodes).
	 *
	 * @since [version]
	 *
	 * @param LLMS_Course|LLMS_Post_Model|WP_Post|int|null $course Optional. Course or a child post used to resolve it. Default `null` (current post).
	 * @return void
	 */
	function lifterlms_template_course_stream_selector( $course = null ) {

		if ( ! ( $course instanceof LLMS_Course ) ) {
			if ( is_object( $course ) && is_callable( array( $course, 'get_course' ) ) ) {
				$course = $course->get_course();
			} else {
				$post_id = ( $course instanceof WP_Post ) ? $course->ID : ( is_numeric( $course ) ? absint( $course ) : get_the_ID() );
				$post    = $post_id ? llms_get_post( $post_id ) : false;
				if ( $post instanceof LLMS_Course ) {
					$course = $post;
				} elseif ( $post_id ) {
					$course = llms_get_post_parent_course( $post_id );
				} else {
					$course = false;
				}
			}
		}

		if ( ! ( $course instanceof LLMS_Course ) || ! llms_course_streams_enabled( $course ) ) {
			return;
		}

		if ( ! get_current_user_id() || ! llms_is_user_enrolled( get_current_user_id(), $course->get( 'id' ) ) ) {
			return;
		}

		llms_get_template(
			'course/stream-selector.php',
			array(
				'course'   => $course,
				'streams'  => llms_get_course_streams( $course ),
				'current'  => llms_get_student_stream( get_current_user_id(), $course ),
				'redirect' => get_permalink(),
			)
		);
	}
}
