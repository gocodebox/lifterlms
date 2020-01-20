<?php
/**
 * Tests for LLMS_Post_Instructors model & functions
 *
 * @group post_relationships
 *
 * @since 3.16.12
 * @since [version] Add tests to remove quiz attempts upon quiz deletion.
 */
class LLMS_Test_Post_Relationships extends LLMS_UnitTestCase {

	/**
	 * When deleting lessons
	 *
	 * 		A) Any lesson which has this lesson as a prereq should have that prereq removed
	 * 		   And the has_prereq metavalue should be unset returning "no"
	 * 		B) Any quiz attached to this lesson should be detached (making it an orphan)
	 *
	 * @version  3.16.12
	 * @return void
	 */
	private function delete_lesson() {

		$courses = $this->generate_mock_courses( 1, 1, 4, 3, 1 );
		$lessons = llms_get_post( $courses[0] )->get_lessons();

		// add prereqs to all the lessons except the first
		foreach ( $lessons as $i => $lesson ) {

			if ( 0 === $i ) {
				continue;
			}

			$prev = $lessons[ $i - 1 ];

			$lesson->set( 'has_prerequisite', 'yes' );
			$lesson->set( 'prerequisite', $prev->get( 'id' ) );

		}

		// delete posts and run tests
		foreach ( $lessons as $i => $lesson ) {

			$quiz = $lesson->get_quiz();

			wp_delete_post( $lesson->get( 'id' ) );

			// quizzes attached to the lesson should now be orphaned
			if ( $quiz ) {
				$this->assertTrue( $quiz->is_orphan() );
			}

			if ( $i === count( $lessons ) - 1 ) {
				continue;
			}
			$next = $lessons[ $i + 1 ];

			// prereqs should be removed
			$this->assertEquals( 'no', $next->get( 'has_prerequisite' ) );
			$this->assertEquals( 0, $next->get( 'prerequisite' ) );
			$this->assertFalse( $next->has_prerequisite() );

		}

	}

	/**
	 * When a quiz is deleted, all the child questions should be deleted too
	 * Lesson should switch quiz_enabled to "no"
	 *
	 * All student attempts for the quiz should be deleted.
	 *
	 * @since 3.16.12
	 * @since [version] Add tests to remove quiz attempts upon quiz deletion.
	 *
	 * @return void
	 */
	private function delete_quiz() {

		$courses = $this->generate_mock_courses( 1, 1, 1, 1, 20 );
		$lesson = llms_get_post( llms_get_post( $courses[0] )->get_lessons( 'ids' )[0] );
		$quiz = $lesson->get_quiz();
		$quiz_id = $quiz->get( 'id' );

		$student_1 = $this->factory->student->create();
		$attempt_1 = $this->take_quiz( $quiz_id, $student_1 );
		$student_2 = $this->factory->student->create();
		$attempt_2 = $this->take_quiz( $quiz_id, $student_2, 50 );

		$questions = $quiz->get_questions( 'ids' );

		wp_delete_post( $quiz->get( 'id' ), true );

		// All question posts should be deleted.
		foreach ( $questions as $question_id ) {
			$this->assertNull( get_post( $question_id ) );
		}

		// The quiz will be disabled on the lesson because metadata is unset.
		$this->assertFalse( $lesson->is_quiz_enabled() );

		// Quiz attempts should be deleted.
		$this->assertFalse( $attempt_1->exists() );
		$this->assertFalse( $attempt_2->exists() );

		// Query for quiz attempts should return nothing.
		$query = new LLMS_Query_Quiz_Attempt(
			array(
				'quiz_id'  => $quiz_id,
				'per_page' => 1,
			)
		);
		$this->assertEquals( 0, $query->found_results );

	}

	/**
	 * Test all relationships based on post types
	 *
	 * @since    3.16.12
	 *
	 * @return   void
	 */
	public function test_maybe_update_relationships() {

		$funcs = array(
			'delete_quiz',
			'delete_lesson',
		);
		foreach ( $funcs as $func ) {

			call_user_func( array( $this, $func ) );

		}

	}

}
