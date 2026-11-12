<?php
/**
 * Test LLMS_Controller_Admin_Quiz_Attempts
 *
 * @package LifterLMS/Tests/Controllers
 *
 * @group controllers
 * @group quizzes
 * @group controller_admin_quiz_attempts
 *
 * @since [version]
 */
class LLMS_Test_Controller_Admin_Quiz_Attempts extends LLMS_UnitTestCase {

	/**
	 * Mock a late penalty form submission and run the controller.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz_Attempt $attempt Quiz attempt.
	 * @param array             $vars    Additional posted fields.
	 * @return LLMS_Quiz_Attempt The attempt, reloaded from the database.
	 */
	private function submit_penalty( $attempt, $vars ) {

		$this->mockPostRequest(
			array_merge(
				array(
					'_llms_quiz_attempt_nonce' => wp_create_nonce( 'llms_quiz_attempt_actions' ),
					'llms_quiz_attempt_action' => 'llms_attempt_late_penalty',
					'llms_attempt_id'          => $attempt->get( 'id' ),
				),
				$vars
			)
		);
		( new LLMS_Controller_Admin_Quiz_Attempts() )->maybe_run_actions();

		return new LLMS_Quiz_Attempt( $attempt->get( 'id' ) );
	}

	/**
	 * Test that saving and removing a late penalty regrades the attempt and runs the same actions as a review.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_late_penalty_regrades_attempt() {

		$course  = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 1, 5 )[0] );
		$quiz_id = $course->get_lessons()[0]->get( 'quiz' );
		update_post_meta( $quiz_id, '_llms_passing_percent', 65 );

		$attempt = $this->take_quiz( $quiz_id, $this->factory->student->create(), 80 );
		$this->assertEquals( 'pass', $attempt->get( 'status' ) );

		// A student can't add a penalty.
		wp_set_current_user( $attempt->get( 'student_id' ) );
		$attempt = $this->submit_penalty( $attempt, array( 'llms_late_penalty' => '20' ) );
		$this->assertSame( 0.0, $attempt->get_late_penalty() );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// 80 - 20 = 60, below the 65% passing grade: the attempt fails and the quiz failed actions run.
		$failed  = did_action( 'lifterlms_quiz_failed' );
		$graded  = did_action( 'llms_quiz_graded' );
		$attempt = $this->submit_penalty(
			$attempt,
			array(
				'llms_late_penalty'      => '20',
				'llms_late_penalty_note' => 'Submitted late.',
			)
		);
		$this->assertEquals( 60, $attempt->get( 'grade' ) );
		$this->assertEquals( 80, $attempt->get_score() );
		$this->assertEquals( 'fail', $attempt->get( 'status' ) );
		$this->assertEquals( 'Submitted late.', $attempt->get( 'late_penalty_note' ) );
		$this->assertSame( $failed + 1, did_action( 'lifterlms_quiz_failed' ) );
		$this->assertSame( $graded + 1, did_action( 'llms_quiz_graded' ) );

		// Saving the same penalty again changes nothing and runs no actions.
		$attempt = $this->submit_penalty(
			$attempt,
			array(
				'llms_late_penalty'      => '20',
				'llms_late_penalty_note' => 'Submitted late.',
			)
		);
		$this->assertSame( $graded + 1, did_action( 'llms_quiz_graded' ) );

		// Removing it restores the grade, passes the attempt again and clears the note.
		$passed  = did_action( 'lifterlms_quiz_passed' );
		$attempt = $this->submit_penalty( $attempt, array( 'llms_quiz_attempt_action' => 'llms_attempt_late_penalty_remove' ) );
		$this->assertEquals( 80, $attempt->get( 'grade' ) );
		$this->assertEquals( 'pass', $attempt->get( 'status' ) );
		$this->assertSame( 0.0, $attempt->get_late_penalty() );
		$this->assertSame( '', (string) $attempt->get( 'late_penalty_note' ) );
		$this->assertSame( $passed + 1, did_action( 'lifterlms_quiz_passed' ) );
	}
}
