<?php
/**
 * LifterLMS Course Functions
 *
 * @package LifterLMS/Functions
 *
 * @since Unknown
 * @version 3.37.13
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get course object
 *
 * @since Unknown
 * @since 3.37.13 Use `LLMS_Course` in favor of the deprecated `LLMS_Course_Factory::get_course()` method.
 *
 * @param WP_Post|int|false $the_course Course post object or id. If `false` uses the global `$post` object.
 * @param array             $args       Arguments to pass to the LLMS_Course Constructor.
 * @return LLMS_Course
 */
function get_course( $the_course = false, $args = array() ) {

	if ( ! $the_course ) {
		global $post;
		$the_course = $post;
	}

	return new LLMS_Course( $the_course, $args );

}

/**
 * Get lesson object
 *
 * @since Unknown
 * @since 3.37.13 Use `LLMS_Lesson` in favor of the deprecated `LLMS_Course_Factory::get_lesson()` method.
 *
 * @param WP_Post|int|false $the_lesson Lesson post object or id. If `false` uses the global `$post` object.
 * @param array             $args        Arguments to pass to the LLMS_Lesson Constructor.
 * @return LLMS_Lesson
 */
function get_lesson( $the_lesson = false, $args = array() ) {

	if ( ! $the_lesson ) {
		global $post;
		$the_lesson = $post;
	}

	return new LLMS_Lesson( $the_lesson, $args );

}
