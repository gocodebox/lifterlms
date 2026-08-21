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
	 * Test the llms_get_progress_cache_keys() function.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_llms_get_progress_cache_keys() {

		$course     = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 0 ) );
		$course_id  = $course->get( 'id' );
		$section_id = $course->get_sections( 'ids' )[0];
		$lesson_id  = $course->get_lessons( 'ids' )[0];

		$expected = array(
			sprintf( 'section_%d_progress', $section_id ),
			sprintf( 'course_%d_progress', $course_id ),
		);

		$this->assertEquals( $expected, llms_get_progress_cache_keys( $lesson_id, 'lesson' ) );

		// Type derived from the post when omitted.
		$this->assertEquals( $expected, llms_get_progress_cache_keys( $section_id ) );

		$this->assertEquals(
			array( sprintf( 'course_%d_progress', $course_id ) ),
			llms_get_progress_cache_keys( $course_id, 'course' )
		);

		$this->assertEquals( array(), llms_get_progress_cache_keys( 999999999 ) );

	}

	/**
	 * Test that trashing, untrashing, and deleting a lesson resets all students' cached progress.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_structural_changes_reset_progress_cache() {

		$course    = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 2, 'quizzes' => 0 ) );
		$course_id = $course->get( 'id' );
		$lessons   = $course->get_lessons( 'ids' );

		$student = $this->factory->student->create_and_get();
		$student->enroll( $course_id );

		llms_mark_complete( $student->get( 'id' ), $lessons[0], 'lesson' );

		// Prime the cache.
		$this->assertEquals( 50, $student->get_progress( $course_id, 'course' ) );

		// Trashing the incomplete lesson leaves only the completed one.
		wp_trash_post( $lessons[1] );
		$this->assertEquals( 100, $student->get_progress( $course_id, 'course' ) );

		// Untrashing restores it (re-publish to counter the untrash-to-draft default).
		wp_untrash_post( $lessons[1] );
		wp_publish_post( $lessons[1] );
		$this->assertEquals( 50, $student->get_progress( $course_id, 'course' ) );

		// Hard delete.
		wp_delete_post( $lessons[1], true );
		$this->assertEquals( 100, $student->get_progress( $course_id, 'course' ) );

	}

	/**
	 * Test that reparenting a lesson resets cached progress for both the old and new ancestor trees.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reparent_lesson_resets_progress_cache() {

		$course_a = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 2, 'quizzes' => 0 ) );
		$course_b = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 0 ) );

		$student = $this->factory->student->create_and_get();
		$student->enroll( $course_a->get( 'id' ) );
		$student->enroll( $course_b->get( 'id' ) );

		$completed_lesson_id = $course_a->get_lessons( 'ids' )[0];
		llms_mark_complete( $student->get( 'id' ), $completed_lesson_id, 'lesson' );

		// Prime both caches.
		$this->assertEquals( 50, $student->get_progress( $course_a->get( 'id' ), 'course' ) );
		$this->assertEquals( 0, $student->get_progress( $course_b->get( 'id' ), 'course' ) );

		// Move the completed lesson into course B's section.
		$lesson = llms_get_post( $completed_lesson_id );
		$lesson->set( 'parent_section', $course_b->get_sections( 'ids' )[0] );

		$this->assertEquals( 0, $student->get_progress( $course_a->get( 'id' ), 'course' ) );
		$this->assertEquals( 50, $student->get_progress( $course_b->get( 'id' ), 'course' ) );

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
