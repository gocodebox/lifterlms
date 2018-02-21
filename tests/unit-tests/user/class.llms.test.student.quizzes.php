<?php
/**
 * Tests for LifterLMS Student Functions
 * @group    quizzes
 * @group    LLMS_Student
 * @since    3.9.0
 * @version  [version]
 */
class LLMS_Test_Student_Quizzes extends LLMS_UnitTestCase {

	private function get_student_with_quizzes() {

		$uid = $this->factory->user->create();
		$student = llms_get_student( $uid );
		$courses = $this->generate_mock_courses( 3, 1, 1, 1 );
		$this->complete_courses_for_student( $uid, $courses );
		return $student;

	}

	/**
	 * test delet_attempt method
	 * @return   void
	 * @since    3.9.0
	 * @version  [version]
	 */
	public function test_delete_attempt() {

		$i = 1;
		while ( $i <= 5 ) {

			$student = $this->get_student_with_quizzes();
			$attempts = $student->quizzes()->get_all();
			$id = rand( 0, count( $attempts ) - 1 );
			$attempt = $attempts[ $id ];
			$this->assertTrue( $student->quizzes()->delete_attempt( $attempt->get( 'id' ) ) );
			$this->assertFalse( $attempt->exists() );

			$i++;

		}

	}

}
