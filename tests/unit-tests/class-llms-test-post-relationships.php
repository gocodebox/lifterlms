<?php
/**
 * Tests for LLMS_Post_Instructors model & functions
 * @group   post_relationships
 * @since   3.16.12
 * @version 3.16.12
 */
class LLMS_Test_Post_Relationships extends LLMS_UnitTestCase {

	/**
	 * When deleting lessons
	 * 		A) Any lesson which has this lesson as a prereq should have that prereq removed
	 * 		   And the has_prereq metavalue should be unset returning "no"
	 * 		B) Any quiz attached to this lesson should be detached (making it an orphan)
	 * @return   [type]
	 * @since    3.16.12
	 * @version  3.16.12
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
	 * @return   void
	 * @since    3.16.12
	 * @version  3.16.12
	 */
	private function delete_quiz() {

		$courses = $this->generate_mock_courses( 1, 1, 1, 1, 20 );
		$lesson = llms_get_post( llms_get_post( $courses[0] )->get_lessons( 'ids' )[0] );
		$quiz = $lesson->get_quiz();

		$questions = $quiz->get_questions( 'ids' );

		wp_delete_post( $quiz->get( 'id' ), true );

		foreach ( $questions as $question_id ) {

			$this->assertNull( get_post( $question_id ) );

		}

		$this->assertFalse( $lesson->is_quiz_enabled() );

	}

	/**
	 * Test all relationships based on post types
	 * @return   void
	 * @since    3.16.12
	 * @version  3.16.12
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
