<?php
/**
 * Tests for the LLMS_Install Class
 * @since    3.9.0
 * @version  3.9.2
 */
class LLMS_Test_Model_Quiz_Attempt extends LLMS_UnitTestCase {

	/**
	 * Get an initialized mock attempt
	 * @param    integer    $num_questions  number of questions to add to the quiz
	 * @return   obj
	 * @since    3.9.2
	 * @version  3.9.2
	 */
	private function get_mock_attempt( $num_questions = 5 ) {

		$uid = $this->factory->user->create();
		$courses = $this->generate_mock_courses( 1, 1, 1, 1, $num_questions );

		$course = llms_get_post( $courses[0] );
		$lesson = $course->get_lessons()[0];
		$lid = $lesson->get( 'id' );
		$qid = $lesson->get( 'assigned_quiz' );

		return LLMS_Quiz_Attempt::init( $qid, $lid, $uid )->save();

	}

	/**
	 * [take_a_quiz description]
	 * @param    [type]     $desired_grade    [description]
	 * @param    [type]     $passing_percent  [description]
	 * @param    integer    $num_questions    [description]
	 * @return   [type]                       [description]
	 * @since    3.9.2
	 * @version  3.9.2
	 */
	private function take_a_quiz( $desired_grade, $passing_percent, $num_questions = 15, $attempt = null ) {

		if ( ! $attempt ) {
			$attempt = $this->get_mock_attempt( $num_questions );
		}


		update_post_meta( $attempt->get( 'quiz_id' ), '_llms_passing_percent', $passing_percent );
		$to_answer_correctly = 0 === $desired_grade ? 0 : $desired_grade / 100 * $num_questions;

		$attempt->start();

		$current_question = 1;
		while ( $attempt->get_next_question() ) {

			$question_id = $attempt->get_next_question();
			$question = llms_get_post( $question_id );

			$answer_type = ( $current_question <= $to_answer_correctly );

			// answer correctly until we don't have to anymore
			foreach( $question->get_options() as $key => $data ) {
				if ( $answer_type === $data['correct_option'] ) {
					$attempt->answer_question( $question_id, $key );
					break;
				}
			}

			$current_question++;

		}

		$attempt->end();

		return $attempt;

	}




	public function test_grading_with_floats() {

		$attempt = $this->get_mock_attempt( 6 );

		$questions = $attempt->get( 'questions' );
		foreach ( $questions as $key => $data ) {
			$questions[ $key ]['points'] = 3.3333;
		}

		$attempt->set( 'questions', $questions );
		$attempt->save();

		$attempt = $this->take_a_quiz( 67, 65, 6, $attempt );
		$this->assertEquals( 66.67, $attempt->get( 'grade' ) );

	}


	/**
	 * Test counter functions
	 * @return   void
	 * @since    3.9.2
	 * @version  3.9.2
	 */
	public function test_get_count() {

		$i = 1;
		while ( $i <= 25 ) {

			$attempt = $this->get_mock_attempt( $i );

			// num of questions and num available points will both be the same given the default mock quiz data
			foreach ( array( 'available_points', 'questions' ) as $key ) {
				$this->assertEquals( $i, $attempt->get_count( $key ) );
			}

			// update each question to have a random number of points and ensure the available points from getter is correct
			$questions = $attempt->get( 'questions' );
			$total_points = 0;
			foreach( $questions as $key => $question ) {
				$add = rand( 1, 100 );
				$questions[ $key ]['points'] = $add;
				$total_points += $add;
			}

			$attempt->set( 'questions', $questions )->save();

			$this->assertEquals( $total_points, $attempt->get_count( 'available_points' ) );


			$i++;

		}


	}

	/**
	 * test get key function
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_get_key() {

		$attempt = $this->get_mock_attempt( 2 );

		$this->assertTrue( is_string( $attempt->get_key() ) );
		$got = $attempt->get_student()->quizzes()->get_attempt_by_key( $attempt->get_key() );
		$this->assertEquals( $attempt, $got );

	}

	/**
	 * test get status function
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_get_status() {

		$attempt = $this->get_mock_attempt( 2 );

		// newly initialized
		$this->assertEquals( 'new', $attempt->get_status() );

		// started & current quiz
		$attempt->set( 'start_date', current_time( 'mysql' ) );
		$this->assertEquals( 'in-progress', $attempt->get_status() );

		// no longer current but not completed
		$attempt->set( 'current', false );
		$this->assertEquals( 'incomplete', $attempt->get_status() );

		$attempt->set( 'end_date', current_time( 'mysql' ) );
		$this->assertEquals( 'complete', $attempt->get_status() );

	}

	/**
	 * test get student function
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_get_student() {

		$uid = $this->factory->user->create();
		$courses = $this->generate_mock_courses( 1, 1, 1, 1 );

		$course = llms_get_post( $courses[0] );
		$lesson = $course->get_lessons()[0];
		$lid = $lesson->get( 'id' );
		$qid = $lesson->get( 'assigned_quiz' );

		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid );

		$this->assertTrue( is_a( $attempt->get_student(), 'LLMS_Student' ) );
		$this->assertEquals( $uid, $attempt->get_student()->get_id() );

	}

	/**
	 * test getters and setters and save method
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.2
	 */
	public function test_getters_setters_and_save() {

		$uid = $this->factory->user->create();
		$courses = $this->generate_mock_courses( 1, 1, 1, 1 );

		$course = llms_get_post( $courses[0] );
		$lesson = $course->get_lessons()[0];
		$lid = $lesson->get( 'id' );
		$qid = $lesson->get( 'assigned_quiz' );

		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid );

		$data = array(
			'attempt' => 5,
			'current' => false,
			'end_date' => current_time( 'mysql' ),
			'grade' => 85.35,
			'passed' => true,
			'start_date' => current_time( 'mysql' ),
			'wpnonce' => 'MR7FOFZBul2i',
		);

		foreach ( $data as $key => $val ) {

			$attempt->set( $key, $val );
			$this->assertEquals( $data[ $key ], $attempt->get( $key ) );

		}

		foreach ( $attempt->get( 'questions' ) as $key => $question ) {

			$this->assertEquals( $key + 1, $attempt->get_question_order( $question['id'] ) );

		}

		// save the attempt again and ensure persistence works
		$attempt->save();

		$student = llms_get_student( $uid );
		$attempt = $student->quizzes()->get_attempt( $qid, $lid, $data['attempt'] );
		foreach ( $data as $key => $val ) {
			$this->assertEquals( $data[ $key ], $attempt->get( $key ) );
		}

	}

	/**
	 * test static init function
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_init() {

		$uid = $this->factory->user->create();
		$courses = $this->generate_mock_courses( 1, 1, 1, 1 );

		$course = llms_get_post( $courses[0] );
		$lesson = $course->get_lessons()[0];
		$lid = $lesson->get( 'id' );
		$qid = $lesson->get( 'assigned_quiz' );

		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid )->save();

		$att_num = $attempt->get( 'attempt' );
		$student = llms_get_student( $uid );

		// attempt saved successfully
		$this->assertEquals( $student->quizzes()->get_attempt( $qid, $lid, $att_num ), $attempt );

		// no user, attempt throws exception
		try {
			$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, null )->save();
		} catch ( Exception $exception ) {
			$this->assertTrue( is_a( $exception, 'Exception' ) );
		}

		// no user but a current user exists
		wp_set_current_user( $uid );
		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, null )->save();
		$att_num = $attempt->get( 'attempt' );
		$this->assertEquals( 1, $att_num ); // should not increment because the attempt already exists
		$this->assertEquals( $student->quizzes()->get_attempt( $qid, $lid, $att_num ), $attempt );

		// mark the new attempt as not-current
		$attempt->set( 'current', false )->save();
		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, null )->save();
		// new attempt should be #2
		$this->assertEquals( 2, $attempt->get( 'attempt' ) );

	}

	/**
	 * test quiz start
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_start() {

		$attempt = $this->get_mock_attempt( 2 );
		$attempt->start();

		$this->assertTrue( ! empty( $attempt->get( 'start_date' ) ) );

	}

	/**
	 * Take a bunch of quizzes
	 * quiz taking / ending functions
	 * Tests grade / point calculations
	 * pass/fail/complete actions
	 * @return   [type]     [description]
	 * @since    3.9.2
	 * @version  3.9.2
	 */
	public function test_take_some_quizzes( ) {

		$i = 0;
		$num_tests = 0;
		$num_pass = 0;
		$num_fail = 0;
		while ( $i <= 100 ) {

			$attempt = $this->take_a_quiz( $i, 65, 25 );

			if ( 0 === $i ) {
				$grade = 0;
			} else {
				$weight = ( 100 / $attempt->get_count( 'available_points' ) );
				$grade = floor( $i / 100 * 25 ) * $weight;
			}

			$this->assertEquals( $grade, $attempt->get( 'grade' ) );
			$this->assertTrue( ! is_null( $attempt->get( 'end_date' ) ) );
			$this->assertFalse( $attempt->get( 'current' ) );

			if ( $grade < 65 ) {
				$num_fail++;
				$this->assertFalse( $attempt->get( 'passed' ) );
			} else {
				$num_pass++;
				$this->assertTrue( $attempt->get( 'passed' ) );
				$this->assertTrue( llms_is_complete( $attempt->get( 'user_id' ), $attempt->get( 'lesson_id' ), 'lesson' ) );
			}

			$num_tests++;

			$i = $i + 5;

		}

	}

}
