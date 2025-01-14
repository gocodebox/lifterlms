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
 * @since 6.4.0
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
	 * @since 6.4.0
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

	/**
	 * Test quiz_start() when no student logged in.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function test_quiz_start_no_student() {

		wp_set_current_user( 0 );

		$res = LLMS_AJAX_Handler::quiz_start(
			array()
		);

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 400, $res );
		$this->assertWPErrorMessageEquals( 'You must be logged in to take quizzes.', $res );

	}

	/**
	 * Test attempts limit check in quiz_start().
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function test_quiz_start_test_attempts_limit() {

		wp_set_current_user( $this->student->get( 'id' ) );

		// Limit quiz's attempts.
		$this->quiz->set( 'limit_attempts', 'yes' );
		$this->quiz->set( 'allowed_attempts', 1 );

		// Record an attempt so to reach the limit.
		$attempt = LLMS_Quiz_Attempt::init(
			$this->quiz->get( 'id' ),
			$this->lesson->get( 'id' ),
			$this->student->get( 'id' )
		);
		$attempt->save();

		$res = LLMS_AJAX_Handler::quiz_start(
			array(
				'quiz_id' => $this->quiz->get( 'id' ),
			)
		);

		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 400, $res );
		$this->assertWPErrorMessageEquals( "You've reached the maximum number of attempts for this quiz.", $res );

		// Increase the limit.
		$this->quiz->set( 'allowed_attempts', 2 );
		$res = LLMS_AJAX_Handler::quiz_start(
			array(
				'quiz_id'   => $this->quiz->get( 'id' ),
				'lesson_id' => $this->lesson->get( 'id' ),
			)
		);
		// Attempt recorded.
		$this->assertArrayHasKey(
			'attempt_key',
			$res
		);

		// Reset.
		$this->quiz->set( 'limit_attempts', 'no' );
		wp_set_current_user( 0 );

	}

	/**
	 * Test attempts limit check in quiz_answer_question().
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function test_quiz_answer_question_test_attempts_limit() {

		wp_set_current_user( $this->student->get( 'id' ) );

		// Start a quiz first.
		$res = LLMS_AJAX_Handler::quiz_start(
			array(
				'quiz_id'   => $this->quiz->get( 'id' ),
				'lesson_id' => $this->lesson->get( 'id' ),
			)
		);

		$attempt_key = $res['attempt_key'];
		$student_quizzes = $this->student->quizzes();

		// Limit quiz's attempts so that this attempt is the last possible.
		$this->quiz->set( 'limit_attempts', 'yes' );
		$this->quiz->set( 'allowed_attempts', $student_quizzes->count_attempts_by_quiz( $this->quiz->get( 'id' ) ) );

		$question = $this->quiz->get_questions()[0];

		$res = LLMS_AJAX_Handler::quiz_answer_question(
			array(
				'question_id'   => $this->quiz->get( 'id' ),
				'attempt_key'   => $attempt_key,
				'question_id'   => $question->get( 'id' ),
				'question_type' => $question->get( 'type' ),
			)
		);
		// Quiz completed, expect an array with 'redirect' key.
		$this->assertArrayHasKey(
			'redirect',
			$res
		);

		// Now increase the limit.
		$this->quiz->set( 'allowed_attempts', $this->quiz->get( 'allowed_attempts' ) + 1 );
		// Start the quiz again.
		$res = LLMS_AJAX_Handler::quiz_start(
			array(
				'quiz_id'   => $this->quiz->get( 'id' ),
				'lesson_id' => $this->lesson->get( 'id' ),
			)
		);
		// Decrease the limit.
		$this->quiz->set( 'allowed_attempts', $this->quiz->get( 'allowed_attempts' ) - 1 );

		$res = LLMS_AJAX_Handler::quiz_answer_question(
			array(
				'question_id'   => $this->quiz->get( 'id' ),
				'attempt_key'   => $attempt_key,
				'question_id'   => $question->get( 'id' ),
				'question_type' => $question->get( 'type' ),
			)
		);

		// The status is "pass" so it won't be possible to answer the question again anyway (500 vs 400).
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 500, $res );
		$this->assertWPErrorMessageEquals( "There was an error recording your answer. Please return to the lesson and begin again.", $res );

		// Reset.
		$this->quiz->set( 'limit_attempts', 'no' );
		wp_set_current_user( 0 );

	}

}
