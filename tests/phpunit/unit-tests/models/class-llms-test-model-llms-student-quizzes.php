<?php
/**
 * Tests for LifterLMS Student Quizzes Model
 *
 * @group LLMS_Student_Quizzes
 *
 * @since 6.4.0
 * @version 6.4.0
 */
class LLMS_Test_LLMS_Student_Quizzes extends LLMS_UnitTestCase {

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
	 * Test get_attempts_remaining_for_quiz().
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function test_get_attempts_remaining_for_quiz() {

		$this->assertEquals(
			'Unlimited',
			$this->student->quizzes()->get_attempts_remaining_for_quiz( $this->quiz->get( 'id' ) )
		);

		$attempt = LLMS_Quiz_Attempt::init(
			$this->quiz->get( 'id' ),
			$this->lesson->get( 'id' ),
			$this->student->get( 'id' )
		);
		$attempt->save();

		$this->assertEquals(
			'Unlimited',
			$this->student->quizzes()->get_attempts_remaining_for_quiz( $this->quiz->get( 'id' ) )
		);

		// Limit quiz's attempts.
		$this->quiz->set( 'limit_attempts', 'yes' );
		$this->quiz->set( 'allowed_attempts', 2 );

		$this->assertEquals(
			1,
			$this->student->quizzes()->get_attempts_remaining_for_quiz( $this->quiz->get( 'id' ) )
		);

		$attempt = LLMS_Quiz_Attempt::init(
			$this->quiz->get( 'id' ),
			$this->lesson->get( 'id' ),
			$this->student->get( 'id' )
		);
		$attempt->save();

		$this->assertEquals(
			0,
			$this->student->quizzes()->get_attempts_remaining_for_quiz( $this->quiz->get( 'id' ) )
		);

		// Decrease attempts limit.
		$this->quiz->set( 'allowed_attempts', 1 );
		$this->assertEquals(
			0,
			$this->student->quizzes()->get_attempts_remaining_for_quiz( $this->quiz->get( 'id' ) )
		);
		$this->assertEquals( // Allow for negative values.
			-1,
			$this->student->quizzes()->get_attempts_remaining_for_quiz( $this->quiz->get( 'id' ), true )
		);

		// Reset attempts limit.
		$this->quiz->set( 'limit_attempts', 'no' );

	}

}
