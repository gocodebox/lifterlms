<?php
/**
 * Tests for the LLMS_Install Class
 * @group    quizzes
 * @since    3.9.0
 * @version  3.16.11
 */
class LLMS_Test_Model_Quiz_Attempt extends LLMS_UnitTestCase {

	/**
	 * Get an initialized mock attempt
	 * @param    integer    $num_questions  number of questions to add to the quiz
	 * @return   obj
	 * @since    3.9.2
	 * @version  3.16.11
	 */
	private function get_mock_attempt( $num_questions = 5 ) {

		$uid = $this->factory->user->create();
		$courses = $this->generate_mock_courses( 1, 1, 1, 1, $num_questions );

		$course = llms_get_post( $courses[0] );
		$lesson = $course->get_lessons()[0];
		$lid = $lesson->get( 'id' );
		$qid = $lesson->get( 'quiz' );

		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid );
		$attempt->save();
		return $attempt;

	}

	/**
	 * [take_a_quiz description]
	 * @param    [type]     $desired_grade    grade for the attempt
	 * @param    [type]     $passing_percent  required passing percentage
	 * @param    integer    $num_questions    number of questions in the quiz
	 * @param    string     $rand             whether to randomize question order
	 * @param    string     $passing_required whether passing grade is required to complete the associated lesson
	 * @return   [type]                       [description]
	 * @since    3.9.2
	 * @version  3.17.1
	 */
	private function take_a_quiz( $desired_grade, $passing_percent, $num_questions = 15, $attempt = null, $rand = 'no', $passing_required = 'no' ) {

		if ( ! $attempt ) {
			$attempt = $this->get_mock_attempt( $num_questions );
		}

		update_post_meta( $attempt->get( 'lesson_id' ), '_llms_require_passing_grade', $passing_required );

		update_post_meta( $attempt->get( 'quiz_id' ), '_llms_random_questions', $rand );
		update_post_meta( $attempt->get( 'quiz_id' ), '_llms_passing_percent', $passing_percent );
		$to_answer_correctly = 0 === $desired_grade ? 0 : $desired_grade / 100 * $num_questions;

		$attempt->start();

		$current_question = 1;
		while ( $attempt->get_next_question() ) {

			$question_id = $attempt->get_next_question();
			$question = llms_get_post( $question_id );

			$answer_type = ( $current_question <= $to_answer_correctly );

			// answer correctly until we don't have to anymore
			foreach( $question->get_choices() as $key => $choice ) {
				if ( $answer_type === $choice->is_correct() ) {
					$attempt->answer_question( $question_id, array( $choice->get( 'id' ) ) );
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

		$questions = $attempt->get_questions();

		foreach ( $questions as $key => &$data ) {
			$data['points'] = 3.3333;
		}

		$attempt->set_questions( $questions, true );

		$attempt = $this->take_a_quiz( 67, 65, 6, $attempt );
		$this->assertEquals( 66.67, $attempt->get( 'grade' ) );

	}


	/**
	 * Test counter functions
	 * @return   void
	 * @since    3.9.2
	 * @version  3.16.11
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
			$questions = $attempt->get_questions();
			$total_points = 0;
			foreach( $questions as $key => $question ) {
				$add = rand( 1, 100 );
				$questions[ $key ]['points'] = $add;
				$total_points += $add;
			}

			$attempt->set_questions( $questions, true );

			$this->assertEquals( $total_points, $attempt->get_count( 'available_points' ) );


			$i++;

		}


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
		$qid = $lesson->get( 'quiz' );

		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid );

		$this->assertTrue( is_a( $attempt->get_student(), 'LLMS_Student' ) );
		$this->assertEquals( $uid, $attempt->get_student()->get_id() );

	}

	// /**
	//  * test getters and setters and save method
	//  * @return   void
	//  * @since    3.9.0
	//  * @version  3.9.2
	//  */
	// public function test_getters_setters_and_save() {

	// 	$uid = $this->factory->user->create();
	// 	$courses = $this->generate_mock_courses( 1, 1, 1, 1 );

	// 	$course = llms_get_post( $courses[0] );
	// 	$lesson = $course->get_lessons()[0];
	// 	$lid = $lesson->get( 'id' );
	// 	$qid = $lesson->get( 'quiz' );

	// 	$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid );

	// 	$data = array(
	// 		'attempt' => 5,
	// 		'current' => false,
	// 		'end_date' => current_time( 'mysql' ),
	// 		'grade' => 85.35,
	// 		'passed' => true,
	// 		'start_date' => current_time( 'mysql' ),
	// 	);

	// 	foreach ( $data as $key => $val ) {

	// 		$attempt->set( $key, $val );
	// 		$this->assertEquals( $data[ $key ], $attempt->get( $key ) );

	// 	}

	// 	foreach ( $attempt->get( 'questions' ) as $key => $question ) {

	// 		$this->assertEquals( $key + 1, $attempt->get_question_order( $question['id'] ) );

	// 	}

	// 	// save the attempt again and ensure persistence works
	// 	$attempt->save();

	// 	$student = llms_get_student( $uid );
	// 	$attempt = $student->quizzes()->get_attempt( $qid, $lid, $data['attempt'] );
	// 	foreach ( $data as $key => $val ) {
	// 		$this->assertEquals( $data[ $key ], $attempt->get( $key ) );
	// 	}

	// }

	// /**
	//  * test static init function
	//  * @return   void
	//  * @since    3.9.0
	//  * @version  3.9.0
	//  */
	// public function test_init() {

	// 	$uid = $this->factory->user->create();
	// 	$courses = $this->generate_mock_courses( 1, 1, 1, 1 );

	// 	$course = llms_get_post( $courses[0] );
	// 	$lesson = $course->get_lessons()[0];
	// 	$lid = $lesson->get( 'id' );
	// 	$qid = $lesson->get( 'assigned_quiz' );

	// 	$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid )->save();

	// 	$att_num = $attempt->get( 'attempt' );
	// 	$student = llms_get_student( $uid );

	// 	// attempt saved successfully
	// 	$this->assertEquals( $student->quizzes()->get_attempt( $qid, $lid, $att_num ), $attempt );

	// 	// no user, attempt throws exception
	// 	try {
	// 		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, null )->save();
	// 	} catch ( Exception $exception ) {
	// 		$this->assertTrue( is_a( $exception, 'Exception' ) );
	// 	}

	// 	// no user but a current user exists
	// 	wp_set_current_user( $uid );
	// 	$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, null )->save();
	// 	$att_num = $attempt->get( 'attempt' );
	// 	$this->assertEquals( 1, $att_num ); // should not increment because the attempt already exists
	// 	$this->assertEquals( $student->quizzes()->get_attempt( $qid, $lid, $att_num ), $attempt );

	// 	// mark the new attempt as not-current
	// 	$attempt->set( 'current', false )->save();
	// 	$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, null )->save();
	// 	// new attempt should be #2
	// 	$this->assertEquals( 2, $attempt->get( 'attempt' ) );

	// }

	// /**
	//  * test quiz start
	//  * @return   void
	//  * @since    3.9.0
	//  * @version  3.9.0
	//  */
	// public function test_start() {

	// 	$attempt = $this->get_mock_attempt( 2 );
	// 	$attempt->start();

	// 	$this->assertTrue( ! empty( $attempt->get( 'start_date' ) ) );

	// }

	/**
	 * Take a bunch of quizzes
	 * quiz taking / ending functions
	 * Tests grade / point calculations
	 * pass/fail/complete actions
	 * @return   void
	 * @since    3.9.2
	 * @version  3.17.1
	 */
	public function test_take_some_quizzes( ) {

		$i = 0;
		$num_tests = 0;
		$num_pass = 0;
		$num_fail = 0;
		while ( $i <= 100 ) {

			$rand = rand( 0, 1 ) ? 'yes' : 'no';
			$passing = $rand = rand( 0, 1 ) ? 'yes' : 'no';
			$attempt = $this->take_a_quiz( $i, 65, 25, null, $rand, $passing );

			if ( 0 === $i ) {
				$grade = 0;
			} else {
				$weight = ( 100 / $attempt->get_count( 'available_points' ) );
				$grade = floor( $i / 100 * 25 ) * $weight;
			}

			$this->assertEquals( $grade, $attempt->get( 'grade' ) );
			$this->assertTrue( ! is_null( $attempt->get( 'end_date' ) ) );

			if ( $grade < 65 ) {
				$num_fail++;
				$this->assertFalse( $attempt->is_passing() );
				$is_complete = llms_parse_bool( $passing ) ? false : true;
			} else {
				$num_pass++;
				$this->assertTrue( $attempt->is_passing() );
				$is_complete = true;
			}

			$this->assertEquals( $is_complete, llms_is_complete( $attempt->get( 'student_id' ), $attempt->get( 'lesson_id' ), 'lesson' ) );

			$num_tests++;

			$i = $i + 5;

		}

	}

}
