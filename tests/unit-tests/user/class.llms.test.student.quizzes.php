<?php
/**
 * Tests for LifterLMS Student Functions
 * @group    quizzes
 * @since    3.9.0
 * @version  3.9.0
 */
class LLMS_Test_Student_Quizzes extends LLMS_UnitTestCase {

	private function get_student_with_quizzes() {

		$uid = $this->factory->user->create();
		$student = new LLMS_Student( $uid );
		$courses = $this->generate_mock_courses( 3, 1, 1, 1 );
		$this->complete_courses_for_student( $uid, $courses );
		return $student;

	}

	/**
	 * See if an array exists within another array
	 * Used to see if attempt data exists within the raw attempts array
	 * @param    array     $part   array to look for
	 * @param    array     $whole  array to look in
	 * @return   boolean
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	private function in_array( $part, $whole ) {
		$pos = strpos( serialize( $whole ), serialize( $part ) );
		return false !== $pos ? true : false;
	}

	/**
	 * test delet_attempt method
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_delete_attempt() {

		$i = 1;
		while ( $i <= 5 ) {

			$student = $this->get_student_with_quizzes();
			$attempts = $student->quizzes()->get_all();
			$id = rand( 0, count( $attempts ) - 1 );
			$attempt = $attempts[ $id ];
			$student->quizzes()->delete_attempt( $attempt['id'], $attempt['assoc_lesson'], $attempt['attempt'] );
			$this->assertFalse( $this->in_array( $attempt, $student->quizzes()->get_all() ) );

			$i++;

		}

	}

	/**
	 * Test get_all
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_get_all() {

		$student = $this->get_student_with_quizzes();
		$this->assertEquals( get_user_meta( $student->get_id(), 'llms_quiz_data', true ), $student->quizzes()->get_all() );

		// filter by quiz id
		foreach ( $student->quizzes()->get_all( 302 ) as $data ) {
			// all ids should be 302
			$this->assertEquals( 302, $data['id'] );
		}

		// filter by lesson id
		foreach ( $student->quizzes()->get_all( null, 2807 ) as $data ) {
			// all ids should be 2807
			$this->assertEquals( 2807, $data['assoc_lesson'] );
		}

		// filter by quiz and lesson
		foreach ( $student->quizzes()->get_all( 302, 2807 ) as $data ) {
			$this->assertTrue( ( 302 == $data['id'] && 2807 == $data['assoc_lesson'] ) );
		}

	}

	/**
	 * Test get_attempt() method
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_get_attempt() {

		$i = 1;
		while ( $i <= 5 ) {

			$student = $this->get_student_with_quizzes();
			$attempts = $student->quizzes()->get_all();
			$id = rand( 0, count( $attempts ) - 1 );
			$attempt = $attempts[ $id ];
			$got = $student->quizzes()->get_attempt( $attempt['id'], $attempt['assoc_lesson'], $attempt['attempt'] );
			$this->assertEquals( $attempt, $got->to_array() );

			$i++;

		}

	}

	public function test_get_best_attempt() {

		$student = $this->get_student_with_quizzes();

		// none found
		$this->assertFalse( $student->quizzes()->get_best_attempt( 1, 2 ) );

		// @todo need to figure out a way to test the intended sucessess return...


	}

	/**
	 * Test get_last_attempt method
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_get_last_attempt() {

		$student = $this->get_student_with_quizzes();
		$all = $student->quizzes()->get_all( 5207, 5206 );

		// expected last
		$last = $student->quizzes()->get_attempt( 5207, 5206, count( $all ) );

		$this->assertEquals( $last, $student->quizzes()->get_last_attempt( 5207, 5206 ) );

		// shouldnt exist
		$this->assertFalse( $student->quizzes()->get_last_attempt( 1, 2 ) );


	}

	/**
	 * test the save_attempt() method
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_save_attempt() {

		$i = 1;
		while ( $i <= 5 ) {

			$student = $this->get_student_with_quizzes();
			$attempts = $student->quizzes()->get_all();
			$id = rand( 0, count( $attempts ) - 1 );
			$attempt = $attempts[ $id ];
			$attempt['questions'][0]['answer'] = 'Yfu0a7JgLRZB0glUGME622efwoX4RbNCKLUw657O25wVBO05x3X7fq54RGhg';

			// update existing
			$this->assertFalse( $this->in_array( $attempt, $student->quizzes()->get_all() ) );
			$student->quizzes()->save_attempt( $attempt );
			$this->assertTrue( $this->in_array( $attempt, $student->quizzes()->get_all() ) );

			// create a new attempt
			$last = $student->quizzes()->get_last_attempt( $attempt['id'], $attempt['assoc_lesson'] )->to_array();
			$last['attempt']++;

			$total_attempts = count( $student->quizzes()->get_all() ) + 1;
			$this->assertFalse( $this->in_array( $last, $student->quizzes()->get_all() ) );
			$student->quizzes()->save_attempt( $last );
			$this->assertTrue( $this->in_array( $last, $student->quizzes()->get_all() ) );
			$this->assertEquals( $total_attempts, count( $student->quizzes()->get_all() ) );

			$i++;

		}

	}

}
