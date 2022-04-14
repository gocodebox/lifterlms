<?php
/**
 * Test Quizzes-related methods in the LLMS_AJAX_Handler class.
 *
 * @package LifterLMS/Tests/AJAX
 *
 * @group ajax_quizzes
 * @group ajax
 * @group quizzes
 *
 * @since [version]
 */
class LLMS_Test_AJAX_Handler_Quizzes extends LLMS_UnitTestCase {

	/**
	 * Student instance.
	 *
	 * @var LLMS_Student
	 */
	protected $student;

	/**
	 * Quiz's lesson instance.
	 *
	 * @var LLMS_Lesson
	 */
	protected $lesson;

	/**
	 * Quiz instance.
	 *
	 * @var LLMS_Quiz
	 */
	protected $quiz;

	/**
	 * Setup test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		$this->student = $this->get_mock_student();
		// Create new course with quiz.
		$courses      = $this->generate_mock_courses( 1, 1, 1, 1, 1 );
		$course       = llms_get_post( $courses[0] );
		$this->lesson = $course->get_lessons()[0];
		$this->quiz   = $this->lesson->get_quiz();

	}

	public test_quiz_start() {

	}

}
