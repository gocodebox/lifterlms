<?php
/**
 * Tests for LifterLMS Student Functions
 *
 * @group quizzes
 * @group student_quizzes
 * @group LLMS_Student
 *
 * @since 3.9.0
 */
class LLMS_Test_Student_Quizzes extends LLMS_UnitTestCase {

	/**
	 * Assert that two quiz attempts are deeply equal
	 *
	 * @since 4.21.2
	 *
	 * @param LLMS_Quiz_Attempt $expected Expected attempt object.
	 * @param LLMS_Quiz_Attempt $actual   Actual attempt object.
	 * @return void
	 */
	private function assertAttemptsAreEqual( $expected, $actual ) {

		$props = array(
			'id',
			'student_id',
			'quiz_id',
			'lesson_id',
			'start_date',
			'update_date',
			'end_date',
			'status',
			'attempt',
			'grade',
		);

		foreach ( $props as $prop ) {
			$this->assertEquals( $expected->get( $prop ), $actual->get( $prop ), $prop );
		}

	}

	/**
	 * Create a student with sample quizzes.
	 *
	 * @since Unknown
	 *
	 * @return LLMS_Student
	 */
	private function get_student_with_quizzes( $attempts = 3 ) {

		$uid = $this->factory->user->create();
		$student = llms_get_student( $uid );
		$courses = $this->generate_mock_courses( $attempts, 1, 1, 1 );
		$this->complete_courses_for_student( $uid, $courses );
		return $student;

	}

	/**
	 * Retrieve a quiz attempt for a given student.
	 *
	 * @since 4.21.2
	 *
	 * @param LLMS_Student $student Student object.
	 * @return LLMS_Quiz_Attempt
	 */
	private function get_attempt( $student ) {

		$course  = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 1 )[0] );
		$lesson  = $course->get_lessons()[0];
		$quiz    = $lesson->get_quiz();

		$attempt = LLMS_Quiz_Attempt::init( $quiz->get( 'id' ), $lesson->get( 'id' ), absint( $student->get( 'id' ) ) );

		$attempt->save();

		return new LLMS_Quiz_Attempt( $attempt->get( 'id' ) );

	}

	/**
	 * Test delete_attempt()
	 *
	 * @since 3.9.0
	 * @since 3.16.11 Unknown.
	 * @since 4.21.2 Only users who can view_grades can delete attempts.
	 *
	 * @return void
	 */
	public function test_delete_attempt() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$i = 1;
		while ( $i <= 5 ) {

			$student  = $this->get_student_with_quizzes();
			$attempts = $student->quizzes()->get_all();
			$id       = rand( 0, count( $attempts ) - 1 );
			$attempt  = $attempts[ $id ];

			$this->assertTrue( $student->quizzes()->delete_attempt( $attempt->get( 'id' ) ) );
			$this->assertFalse( $attempt->exists() );

			$i++;

		}

	}

	/**
	 * Test get_all()
	 *
	 * @since 4.21.2
	 *
	 * @return void
	 */
	public function test_get_all() {

		$student = $this->get_student_with_quizzes( 10 );

		$attempts = $student->quizzes()->get_all();

		foreach ( $attempts as $attempt ) {

			$this->assertTrue( $attempt instanceof LLMS_Quiz_Attempt );
			$this->assertEquals( $student->get( 'id' ), absint( $attempt->get( 'student_id' ) ) );

		}

	}

	/**
	 * Test get_attempt_by_id() and get_attempt_by_key()
	 *
	 * @since 4.21.2
	 * @since [version] Don't use an invalid fake hash length.
	 *
	 * @return void
	 */
	public function test_attempt_getters() {

		$student = llms_get_student( $this->factory->user->create() );
		$attempt = $this->get_attempt( $student );

		wp_set_current_user( $student->get( 'id' ) );

		// Get by ID.
		$this->assertAttemptsAreEqual( $attempt, $student->quizzes()->get_attempt_by_id( $attempt->get( 'id' ) ) );

		// Get by Key.
		$this->assertAttemptsAreEqual( $attempt, $student->quizzes()->get_attempt_by_key( $attempt->get_key() ) );

		// ID Doesn't exit.
		$this->assertFalse( $student->quizzes()->get_attempt_by_id( absint( $attempt->get( 'id' ) ) + 1 ) );

		// Key doesn't exist.
		$this->assertFalse( $student->quizzes()->get_attempt_by_key( 'FAKE' ) );

		// ID exists but Wrong student.
		wp_set_current_user( $this->factory->user->create() );
		$this->assertFalse( $student->quizzes()->get_attempt_by_id( $attempt->get( 'id' ) ) );

		// Admin can view.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertAttemptsAreEqual( $attempt, $student->quizzes()->get_attempt_by_id( $attempt->get( 'id' ) ) );

	}

}
