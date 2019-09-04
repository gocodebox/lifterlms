<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Course Factory Class
 *
 * Methods for instantiating objects.
 *
 * @version 1.0
 * @author codeBOX
 * @project lifterLMS
 */
class LLMS_Course_Factory {

	/**
	 * Get Course
	 *
	 * @param mixed $the_course = false, $args = array()
	 * @return void
	 */
	public function get_course( $the_course = false, $args = array() ) {
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
	 * @param mixed $the_lesson = false, $args = array()
	 * @return void
	 */
	public function get_lesson( $the_lesson = false, $args = array() ) {
		global $post;

		$the_lesson = $post;

		$classname = 'LLMS_Lesson_Basic';

		return new LLMS_Lesson_Basic( $the_lesson, $args );
	}

	/**
	 * Get Product
	 *
	 * @param mixed $the_lesson = false, $args = array()
	 * @return void
	 */
	public function get_product( $the_product = false, $args = array() ) {
		global $post;

		$the_product = $post;

		$classname = 'LLMS_Product';

		return new LLMS_Product( $the_product, $args );
	}

	/**
	 * Get Quiz
	 *
	 * @param mixed $the_quiz = false, $args = array()
	 * @return void
	 */
	public function get_quiz( $the_quiz = false, $args = array() ) {
		global $post;

		$the_quiz = $post;

		$classname = 'LLMS_Quiz';

		return new LLMS_Quiz_Legacy( $the_quiz, $args );
	}

	/**
	 * Get Question
	 *
	 * @param mixed $the_question = false, $args = array()
	 * @return void
	 */
	public function get_question( $the_question = false, $args = array() ) {
		global $post;

		$the_question = $post;

		$classname = 'LLMS_Question';

		return new LLMS_Question( $the_question, $args );
	}

}
