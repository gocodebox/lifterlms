<?php
/**
 * Course Factory Class
 *
 * Methods for instantiating objects.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Course_Factory
 *
 * @since 1.0.0
 * @deprecated 3.37.12
 */
class LLMS_Course_Factory {

	/**
	 * Get Course
	 *
	 * @since 1.0.0
	 * @deprecated 3.37.12
	 *
	 * @param mixed $the_course Course object.
	 * @param array $args       Arguments.
	 * @return LLMS_Course_Basic
	 */
	public function get_course( $the_course = false, $args = array() ) {

		llms_deprecated_function( 'LLMS_Course_Factory::get_course()', '3.37.12' );

		global $post;
		if ( empty( $the_course->post_type ) ) {
			$the_course = $post;
		}
		$classname = 'LLMS_Course_Basic';
		return new LLMS_Course_Basic( $the_course, $args );
	}

	/**
	 * Get Lesson
	 *
	 * @since 1.0.0
	 * @deprecated 3.37.12
	 *
	 * @param mixed $the_lesson Lesson object.
	 * @param array $args       Arguments.
	 * @return LLMS_Lesson_Basic
	 */
	public function get_lesson( $the_lesson = false, $args = array() ) {

		llms_deprecated_function( 'LLMS_Course_Factory::get_lesson()', '3.37.12' );

		global $post;
		$the_lesson = $post;
		$classname = 'LLMS_Lesson_Basic';
		return new LLMS_Lesson_Basic( $the_lesson, $args );
	}

	/**
	 * Get Product
	 *
	 * @since 1.0.0
	 * @deprecated 3.37.12
	 *
	 * @param mixed $the_lesson Product object.
	 * @param array $args       Arguments.
	 * @return LLMS_Product
	 */
	public function get_product( $the_product = false, $args = array() ) {

		llms_deprecated_function( 'LLMS_Course_Factory::get_product()', '3.37.12' );

		global $post;
		$the_product = $post;
		$classname = 'LLMS_Product';
		return new LLMS_Product( $the_product, $args );
	}

	/**
	 * Get Quiz
	 *
	 * @since 1.0.0
	 * @deprecated 3.37.12
	 *
	 * @param mixed $the_quiz Quiz object.
	 * @param array $args     Arguments.
	 * @return LLMS_Quiz_Legacy
	 */
	public function get_quiz( $the_quiz = false, $args = array() ) {

		llms_deprecated_function( 'LLMS_Course_Factory::get_quiz()', '3.37.12' );

		global $post;
		$the_quiz = $post;
		$classname = 'LLMS_Quiz';
		return new LLMS_Quiz_Legacy( $the_quiz, $args );
	}

	/**
	 * Get Question
	 *
	 * @since 1.0.0
	 * @deprecated 3.37.12
	 *
	 * @param mixed $the_question Question object.
	 * @param array $args         Arguments.
	 * @return LLMS_Question
	 */
	public function get_question( $the_question = false, $args = array() ) {

		llms_deprecated_function( 'LLMS_Course_Factory::get_question()', '3.37.12' );

		global $post;
		$the_question = $post;
		$classname = 'LLMS_Question';
		return new LLMS_Question( $the_question, $args );
	}

}
