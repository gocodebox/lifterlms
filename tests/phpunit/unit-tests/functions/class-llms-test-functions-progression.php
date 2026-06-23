<?php
/**
 * Test course and lesson progression functions.
 *
 * @group functions
 * @group progression_functions
 * @package  LifterLMS/Tests/Functions
 * @since    3.29.0
 * @version  3.29.0
 */
class LLMS_Test_Functions_Progression extends LLMS_Unit_Test_Case {

	/**
	 * Test the llms_allow_lesson_completion() method.
	 *
	 * @return  void
	 * @since   3.29.0
	 * @version 3.29.0
	 */
	public function test_llms_allow_lesson_completion() {

		$student = $this->factory->student->create_and_get();
		$course = $this->factory->course->create_and_get();
		$lesson_id = $course->get_lessons( 'ids' )[0];

		// progression is okay with no intervention.
		$this->assertTrue( llms_allow_lesson_completion( $student->get( 'id' ), $lesson_id ) );

		// something somewhere prevents progression.
		add_filter( 'llms_allow_lesson_completion', '__return_false' );
		$this->assertFalse( llms_allow_lesson_completion( $student->get( 'id' ), $lesson_id ) );

		// remove the filter so we don't potentially break other tests.
		remove_filter( 'llms_allow_lesson_completion', '__return_false' );

	}

	/**
	 * Test the llms_can_user_complete_lesson() function.
	 *
	 * @since 10.0.7
	 *
	 * @return void
	 */
	public function test_llms_can_user_complete_lesson() {

		$course    = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 0 ) );
		$lesson    = $course->get_lessons()[0];
		$lesson_id = $lesson->get( 'id' );

		$student = $this->factory->student->create_and_get();
		wp_set_current_user( $student->get( 'id' ) );

		// Invalid lesson.
		$this->assertFalse( llms_can_user_complete_lesson( $student->get( 'id' ), 0 ) );

		// Not enrolled.
		$this->assertFalse( llms_can_user_complete_lesson( $student->get( 'id' ), $lesson_id ) );

		// Enrolled and available (accepts both an ID and an object).
		$student->enroll( $course->get( 'id' ) );
		$this->assertTrue( llms_can_user_complete_lesson( $student->get( 'id' ), $lesson_id ) );
		$this->assertTrue( llms_can_user_complete_lesson( $student->get( 'id' ), $lesson ) );

		// Enrolled but lesson is not yet available (dripped).
		$lesson->set( 'drip_method', 'date' );
		$lesson->set( 'date_available', date( 'm/d/Y', strtotime( '+1 year' ) ) );
		$this->assertFalse( llms_can_user_complete_lesson( $student->get( 'id' ), $lesson_id ) );

		$lesson->set( 'drip_method', '' );

		// An instructor/admin who can edit the lesson is always allowed, even when not enrolled.
		$admin = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin );
		$this->assertTrue( llms_can_user_complete_lesson( $admin, $lesson_id ) );

		wp_set_current_user( 0 );

	}

	/**
	 * Test the llms_show_mark_complete_button() method.
	 *
	 * @return  void
	 * @since   3.29.0
	 * @version 3.29.0
	 */
	public function test_llms_show_mark_complete_button() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 3, 'quizzes' => 2 ) );
		$no_quiz = $course->get_lessons()[0];
		$has_quiz = $course->get_lessons()[1];

		$has_unpublished_quiz = $course->get_lessons()[2];
		$has_unpublished_quiz->get_quiz()->set( 'status', 'draft' );

		$this->assertTrue( llms_show_mark_complete_button( $no_quiz ) );
		$this->assertFalse( llms_show_mark_complete_button( $has_quiz ) );
		$this->assertTrue( llms_show_mark_complete_button( $has_unpublished_quiz ) );

	}

	/**
	 * Test llms_show_mark_complete_button() when quiz has not been attempted.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function test_llms_show_mark_complete_button_quiz_not_attempted() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 1 ) );
		$lesson = $course->get_lessons()[0];
		$student = $this->factory->student->create_and_get();

		wp_set_current_user( $student->get( 'id' ) );
		$student->enroll( $course->get( 'id' ) );

		// Quiz exists but no attempts - should not show mark complete.
		$this->assertFalse( llms_show_mark_complete_button( $lesson ) );

	}

	/**
	 * Test llms_show_mark_complete_button() when quiz has been passed.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function test_llms_show_mark_complete_button_quiz_passed() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 1 ) );
		$lesson = $course->get_lessons()[0];
		$quiz   = $lesson->get_quiz();
		$student = $this->factory->student->create_and_get();

		wp_set_current_user( $student->get( 'id' ) );
		$student->enroll( $course->get( 'id' ) );

		// Set passing grade requirement.
		$lesson->set( 'require_passing_grade', 'yes' );
		$quiz->set( 'passing_percent', 50 );

		// Simulate a passing quiz attempt.
		$attempt = LLMS_Quiz_Attempt::init( $quiz->get( 'id' ), $lesson->get( 'id' ), $student->get( 'id' ) );
		$attempt->start();

		// Get all questions and answer them correctly (100% score).
		$questions = $attempt->get_questions();
		foreach ( $questions as $key => $question ) {
			$questions[ $key ]['answer'] = 'correct_answer';
			$questions[ $key ]['earned'] = $questions[ $key ]['points'];
		}
		$attempt->set_questions( $questions, true );
		$attempt->end();

		// Quiz passed - should show mark complete button.
		$this->assertTrue( llms_show_mark_complete_button( $lesson ) );

	}

	/**
	 * Test llms_show_mark_complete_button() when quiz failed and passing is required.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function test_llms_show_mark_complete_button_quiz_failed_passing_required() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 1 ) );
		$lesson = $course->get_lessons()[0];
		$quiz   = $lesson->get_quiz();
		$student = $this->factory->student->create_and_get();

		wp_set_current_user( $student->get( 'id' ) );
		$student->enroll( $course->get( 'id' ) );

		// Set passing grade requirement.
		$lesson->set( 'require_passing_grade', 'yes' );
		$quiz->set( 'passing_percent', 80 );

		// Simulate a failing quiz attempt (0% score).
		$attempt = LLMS_Quiz_Attempt::init( $quiz->get( 'id' ), $lesson->get( 'id' ), $student->get( 'id' ) );
		$attempt->start();

		// Get all questions and answer them incorrectly (0% score).
		$questions = $attempt->get_questions();
		foreach ( $questions as $key => $question ) {
			$questions[ $key ]['answer'] = 'wrong_answer';
			$questions[ $key ]['earned'] = 0;
		}
		$attempt->set_questions( $questions, true );
		$attempt->end();

		// Quiz failed and passing required - should NOT show mark complete button.
		$this->assertFalse( llms_show_mark_complete_button( $lesson ) );

	}

	/**
	 * Test llms_show_mark_complete_button() when quiz failed but passing is NOT required.
	 *
	 * @since 10.0.0
	 *
	 * @return void
	 */
	public function test_llms_show_mark_complete_button_quiz_failed_passing_not_required() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 1 ) );
		$lesson = $course->get_lessons()[0];
		$quiz   = $lesson->get_quiz();
		$student = $this->factory->student->create_and_get();

		wp_set_current_user( $student->get( 'id' ) );
		$student->enroll( $course->get( 'id' ) );

		// Passing grade is NOT required.
		$lesson->set( 'require_passing_grade', 'no' );
		$quiz->set( 'passing_percent', 80 );

		// Simulate a failing quiz attempt (0% score).
		$attempt = LLMS_Quiz_Attempt::init( $quiz->get( 'id' ), $lesson->get( 'id' ), $student->get( 'id' ) );
		$attempt->start();

		// Get all questions and answer them incorrectly (0% score).
		$questions = $attempt->get_questions();
		foreach ( $questions as $key => $question ) {
			$questions[ $key ]['answer'] = 'wrong_answer';
			$questions[ $key ]['earned'] = 0;
		}
		$attempt->set_questions( $questions, true );
		$attempt->end();

		// Quiz failed but passing NOT required - should show mark complete button.
		$this->assertTrue( llms_show_mark_complete_button( $lesson ) );

	}

	/**
	 * Test the llms_show_take_quiz_button()
	 * @return  void
	 * @since   3.29.0
	 * @version 3.29.0
	 */
	public function test_llms_show_take_quiz_button() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 3, 'quizzes' => 2 ) );
		$no_quiz = $course->get_lessons()[0];
		$has_quiz = $course->get_lessons()[1];

		$has_unpublished_quiz = $course->get_lessons()[2];
		$has_unpublished_quiz->get_quiz()->set( 'status', 'draft' );

		$this->assertFalse( llms_show_take_quiz_button( $no_quiz ) );
		$this->assertTrue( llms_show_take_quiz_button( $has_quiz ) );
		$this->assertFalse( llms_show_take_quiz_button( $has_unpublished_quiz ) );

	}

}
