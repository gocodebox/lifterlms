<?php
/**
 * Tests for the LLMS_Install Class
 * @since    3.9.0
 * @version  3.9.0
 */
class LLMS_Test_Model_Quiz_Attempt extends LLMS_UnitTestCase {

	/**
	 * test get status function
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_get_status() {

		$uid = $this->factory->user->create();
		$courses = $this->generate_mock_courses( 1, 1, 1, 1 );

		$course = llms_get_post( $courses[0] );
		$lesson = $course->get_lessons()[0];
		$lid = $lesson->get( 'id' );
		$qid = $lesson->get( 'assigned_quiz' );

		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid );

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
	 * test get key function
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_get_key() {

		$uid = $this->factory->user->create();
		$courses = $this->generate_mock_courses( 1, 1, 1, 1 );

		$course = llms_get_post( $courses[0] );
		$lesson = $course->get_lessons()[0];
		$lid = $lesson->get( 'id' );
		$qid = $lesson->get( 'assigned_quiz' );

		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid )->save();

		$this->assertTrue( is_string( $attempt->get_key() ) );
		$got = $attempt->get_student()->quizzes()->get_attempt_by_key( $attempt->get_key() );
		$this->assertEquals( $attempt, $got );

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

	}

	/**
	 * test getters and setters and save method
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
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

		$uid = $this->factory->user->create();
		$courses = $this->generate_mock_courses( 1, 1, 1, 1 );

		$course = llms_get_post( $courses[0] );
		$lesson = $course->get_lessons()[0];
		$lid = $lesson->get( 'id' );
		$qid = $lesson->get( 'assigned_quiz' );

		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid );
		$attempt->start();

		$this->assertTrue( ! empty( $attempt->get( 'start_date' ) ) );

	}

	/**
	 * take a whole quiz
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function test_take_a_quiz() {

		$uid = $this->factory->user->create();
		$courses = $this->generate_mock_courses( 1, 1, 1, 1 );

		$course = llms_get_post( $courses[0] );
		$lesson = $course->get_lessons()[0];
		$lid = $lesson->get( 'id' );
		$qid = $lesson->get( 'assigned_quiz' );

		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid );

		$attempt->start();

		while ( $attempt->get_next_question() ) {

			$question_id = $attempt->get_next_question();

			$question = llms_get_post( $question_id );
			$options = $question->get_options();
			$answer = ( count( $options ) - 1 );
			$the_answer = rand( 0, $answer );
			$is_correct = $options[ $the_answer ]['correct_option'];
			$attempt->answer_question( $question_id, $the_answer );

			$answered = $attempt->get( 'questions' );
			foreach ( $answered as $data ) {
				if ( $question_id == $data['id'] ) {
					$this->assertEquals( $the_answer, $data['answer'] );
					$this->assertEquals( $is_correct, $data['correct'] );
				}
			}

		}

		$attempt->end();

		$this->assertTrue( ! is_null( $attempt->get( 'grade' ) ) );
		$this->assertTrue( ! is_null( $attempt->get( 'end_date' ) ) );
		$this->assertTrue( ! is_null( $attempt->get( 'passed' ) ) );
		$this->assertFalse( $attempt->get( 'current' ) );

		$this->assertEquals( 1, did_action( 'lifterlms_quiz_completed' ) );
		if ( $attempt->get( 'passed') ) {
			$this->assertEquals( 1, did_action( 'lifterlms_quiz_passed' ) );
			$this->assertTrue( llms_is_complete( $uid, $lid, 'lesson' ) );
		} else {
			$this->assertEquals( 1, did_action( 'lifterlms_quiz_failed' ) );
		}

	}


}
