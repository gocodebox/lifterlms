<?php
/**
 * Tests LLMS_Quiz_Attempt model.
 *
 * @group quizzes
 * @group quiz_attempt
 *
 * @since 3.9.0
 * @since 3.17.4 Unknown.
 * @since 4.0.0 Add tests for the answer_question() method.
 * @since 4.2.0 Added tests for the get_siblings() method.
 *              Added tests on lesson completion status when deleting attempts.
 * @since 5.3.0 Added tests on get_question_objects() when filtering out the removed questions.
 */
class LLMS_Test_Model_Quiz_Attempt extends LLMS_UnitTestCase {

	/**
	 * Teardown the test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_quiz_attempts" );
	}

	/**
	 * Get an initialized mock attempt
	 *
	 * @since 3.9.2
	 * @since 3.16.11 Unknown.
	 * @since 4.2.0 Added uid and courses parameter.
	 *
	 * @param integer $num_questions Optional. Number of questions to add to the quiz. Default 5.
	 * @param integer $uid           Optional. WordPress user id, if not passed a new user will be created. Default `null`.
	 * @param int[]   $course        Optional. Course id, if not passed a new mock course will be created. Default `null`.
	 * @return obj
	 */
	private function get_mock_attempt( $num_questions = 5, $uid = null, $course = null ) {

		$uid     = $uid ? $uid : $this->factory->user->create();
		$courses = ! empty( $course ) ? array( $course ) : $this->generate_mock_courses( 1, 1, 1, 1, $num_questions );

		$course = llms_get_post( $courses[0] );
		$lesson = $course->get_lessons()[0];
		$lid = $lesson->get( 'id' );
		$qid = $lesson->get( 'quiz' );

		$attempt = LLMS_Quiz_Attempt::init( $qid, $lid, $uid );
		$attempt->save();
		return $attempt;

	}

	/**
	 * Get a series of initialized mock sibling attempts
	 *
	 * @since 4.2.0
	 *
	 * @param integer $num           Optional. Number of sibling attmpts. Default 5.
	 * @param integer $num_questions Optional. Number of questions to add to the quiz. Default 5.
	 * @param integer $uid           Optional. WordPress user id, if not passed a new user will be created. Default `null`.
	 * @param int[]   $course        Optional. Course id, if not passed a new mock course will be created. Default `null`.
	 * @return obj[]
	 */
	private function get_mock_sibling_attempts( $num = 5, $num_questions = 5, $uid = null, $course = null ) {

		$uid     = $uid ? $uid : $this->factory->user->create();
		$course = ! empty( $course ) ? $course : $this->generate_mock_courses( 1, 1, 1, 1, $num_questions )[0];

		// Create attempts.
		$attempts = array();
		for ( $i = 0; $i < $num; $i++ ) {
			$attempts[] = $this->get_mock_attempt( $num_questions, $uid, $course );
		}

		return $attempts;
	}

	/**
	 * Retrieve the first incorrect choice for a given question.
	 *
	 * @since 4.0.0
	 *
	 * @param LLMS_Question|WP_Post|int $question Question object, WP_Post object for a question post, or WP_Post ID of the question.
	 * @return LLMS_Question_Choice
	 */
	private function get_incorrect_choice( $question ) {

		$question = is_a( $question, 'LLMS_Question' ) ? $question : llms_get_post( $question );

		foreach ( $question->get_choices() as $choice ) {

			if ( $choice->is_correct() ) {
				continue;
			}

			return array(
				$choice->get( 'id' ),
			);

		}

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

			// Answer correctly until we don't have to anymore.
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

	public function test_answer_question_correctly() {

		$attempt   = $this->get_mock_attempt();
		$questions = wp_list_pluck( $attempt->get_questions(), 'id' );
		$question  = llms_get_post( $questions[0] );
		$correct   = $question->get_correct_choice();

		// Answer question.
		$attempt = $attempt->answer_question( $questions[0], $correct );

		$this->assertTrue( is_a( $attempt, 'LLMS_Quiz_Attempt' ) );

		$res = $attempt->get_questions()[0];

		$this->assertEquals( $res['points'], $res['earned'] );
		$this->assertEquals( 'yes', $res['correct'] );
		$this->assertEquals( $correct, $res['answer'] );


		/**
		 * Answer the question again to simulate a user going back to change their answer.
		 *
		 * @see https://github.com/gocodebox/lifterlms/issues/1211
		 */
		$incorrect = $this->get_incorrect_choice( $question );
		$attempt->answer_question( $questions[0], $incorrect );

		$res = $attempt->get_questions()[0];

		$this->assertEquals( 0, $res['earned'] );
		$this->assertEquals( 'no', $res['correct'] );
		$this->assertEquals( $incorrect, $res['answer'] );

	}

	/**
	 * Test answer_question() when supplying a correct answer
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_answer_question_incorrectly() {

		$attempt   = $this->get_mock_attempt();
		$questions = wp_list_pluck( $attempt->get_questions(), 'id' );
		$question  = llms_get_post( $questions[0] );
		$correct   = $question->get_correct_choice();

		// Answer question.
		$incorrect = $this->get_incorrect_choice( $question );
		$attempt = $attempt->answer_question( $questions[0], $incorrect );

		$res = $attempt->get_questions()[0];

		$this->assertEquals( 0, $res['earned'] );
		$this->assertEquals( 'no', $res['correct'] );
		$this->assertEquals( $incorrect, $res['answer'] );

		/**
		 * Answer the question again to simulate a user going back to change their answer.
		 *
		 * @see https://github.com/gocodebox/lifterlms/issues/1211
		 */
		$attempt->answer_question( $questions[0], $correct );

		$this->assertTrue( is_a( $attempt, 'LLMS_Quiz_Attempt' ) );

		$res = $attempt->get_questions()[0];

		$this->assertEquals( $res['points'], $res['earned'] );
		$this->assertEquals( 'yes', $res['correct'] );
		$this->assertEquals( $correct, $res['answer'] );

	}

	/**
	 * Test answer_question() when supplying an incorrect answer
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
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
		while ( $i <= 10 ) {

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
	 * @version  3.17.4
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

			$i = $i + ( 5 * rand( 1, 20 ) );

		}

	}

	/**
	 * Test get siblings
	 *
	 * @return void
	 */
	public function test_get_siblings() {

		$attempts = $this->get_mock_sibling_attempts( 5, 1 );
		$attempt_ids = array_map(
			function( $attempt ) {
				return $attempt->get( 'id' );
			},
			$attempts
		);

		// Test get siblings of the first attempt equals to the created array of attempts (id).
		$this->assertEquals(
			array_reverse( $attempt_ids ),
			$attempts[0]->get_siblings( array(), 'ids' )
		);

		// Test exclude.
		$this->assertEquals(
			array_reverse( array_slice( $attempt_ids, 1, count( $attempt_ids ) ) ),
			$attempts[0]->get_siblings(
				array(
					'exclude' => array( $attempt_ids[0] ),
				),
				'ids'
			)
		);

		// Test per page, get only 4 attempts out of 5.
		$this->assertEquals(
			array_slice( array_reverse( $attempt_ids ), 0, count( $attempt_ids ) - 1 ),
			$attempts[0]->get_siblings(
				array(
					'per_page' => count( $attempt_ids ) - 1,
				),
				'ids'
			)
		);

		// Test return as attempt.
		$is_attempt = $attempts[0]->get_siblings(
			array(
				'per_page' => 1,
			),
			'attempts'
		)[0] instanceof LLMS_Quiz_Attempt;
		$is_attempt_two = $attempts[0]->get_siblings(
			array(
				'per_page' => 1,
			),
			'whatever'
		)[0] instanceof LLMS_Quiz_Attempt;
		$this->assertTrue( $is_attempt );

	}

	/**
	 * Test lesson completion on delete attempts for a lesson not requiring a passing grade
	 *
	 * @return void
	 */
	public function test_delete_not_requiring_passing_grade_lesson() {

		// Create 3 attempts (for a quiz with 1 question), for a given lesson.
		$attempts   = $this->get_mock_sibling_attempts( 3, 1 );
		$lesson_id  = $attempts[0]->get( 'lesson_id' );
		$student_id = $attempts[0]->get( 'student_id' );

		// Take a quiz (no passing). This will mark the lesson as complete.
		$this->take_a_quiz( 0, 65, 1, $attempts[0] );

		// Only the last deletion will mark the lesson as incomplete.
		foreach ( $attempts as $attempt ) {
			$this->assertTrue( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );
			$attempt->delete();
		}
		$this->assertFalse( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );

		// Create 3 attempts (for a quiz with 1 question), for a given lesson.
		$attempts   = $this->get_mock_sibling_attempts( 3, 1 );
		$lesson_id  = $attempts[0]->get( 'lesson_id' );
		$student_id = $attempts[0]->get( 'student_id' );

		// Take a quiz (passing). This will mark the lesson as complete.
		$this->take_a_quiz( 100, 65, 1, $attempts[0] );

		// We have 1 passing attempt, still only the last deletion will mark the lesson as incomplete.
		foreach ( $attempts as $attempt ) {
			$this->assertTrue( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );
			$attempt->delete();
		}
		$this->assertFalse( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );

	}

	/**
	 * Test lesson completion on delete attempts for a lesson requiring a passing grade
	 *
	 * @return void
	 */
	public function test_delete_requiring_passing_grade_lesson() {

		// Create 3 attempts (for a quiz with 1 question), for a given lesson.
		$attempts   = $this->get_mock_sibling_attempts( 3, 1 );
		$lesson_id  = $attempts[0]->get( 'lesson_id' );
		$student_id = $attempts[0]->get( 'student_id' );

		// Take a quiz (no passing). This will NOT mark the lesson as complete.
		$this->take_a_quiz( 0, 65, 1, $attempts[0], 'no', 'yes' );

		foreach ( $attempts as $attempt ) {
			$this->assertFalse( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );
			$attempt->delete();
		}
		$this->assertFalse( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );

		// Create 3 attempts (for a quiz with 1 question), for a given lesson.
		$attempts   = $this->get_mock_sibling_attempts( 3, 1 );
		$lesson_id  = $attempts[0]->get( 'lesson_id' );
		$student_id = $attempts[0]->get( 'student_id' );

		// Take a quiz (passing). This will mark the lesson as complete.
		$this->take_a_quiz( 100, 65, 1, $attempts[0], 'no', 'yes' );

		// We have 1 passing attempt (the first), deleting all the others (see the reverse order) will not mark the lesson as incomplete.
		foreach ( array_reverse( $attempts ) as $attempt ) {
			$this->assertTrue( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );
			$attempt->delete();
		}
		$this->assertFalse( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );

		// Create 1 attempts (for a quiz with 1 question), for a given lesson.
		$attempts   = $this->get_mock_sibling_attempts( 3, 1 );
		$lesson_id  = $attempts[0]->get( 'lesson_id' );
		$student_id = $attempts[0]->get( 'student_id' );

		// Take a quiz (passing). This will mark the lesson as complete.
		$this->take_a_quiz( 100, 65, 1, $attempts[0], 'no', 'yes' );

		// We have 1 passing attempt (the first), deleting it will mark the lesson as incomplete.
		$attempts[0]->delete();
		$this->assertFalse( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );

		// Create 3 attempts (for a quiz with 1 question), for a given lesson.
		$attempts   = $this->get_mock_sibling_attempts( 3, 1 );
		$lesson_id  = $attempts[0]->get( 'lesson_id' );
		$student_id = $attempts[0]->get( 'student_id' );

		// Take two passing quizzes.
		$this->take_a_quiz( 100, 65, 1, $attempts[0], 'no', 'yes' );
		//$this->take_a_quiz( 100, 65, 1, $attempts[1], 'no', 'yes' );

		// We have 2 passing attempts (the first two), the lesson will be marked as incomplete only after deleting all the passed attempts.
		foreach ( array_reverse( $attempts ) as $attempt ) {
			$this->assertTrue( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );
			$attempt->delete();
		}
		$this->assertFalse( llms_is_complete( $student_id, $lesson_id, 'lesson' ) );

	}

	/**
	 * Test get_question_objects() method when filtering out the removed questions.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_get_question_objects_filter_removed() {

		$attempt   = $this->get_mock_attempt();
		$questions = wp_list_pluck( $attempt->get_questions(), 'id' );

		// Check `get_question_objects()` returns the same list of `get_questions()`.
		$this->assertEqualSets(
			$questions,
			$this->question_object_ids_list_pluck( $attempt )
		);

		// Delete a question.
		wp_delete_post( $questions[ 1 ] );

		// Check `get_question_objects()` still returns the same list of `get_questions()`.
		$this->assertEqualSets(
			$questions,
			$this->question_object_ids_list_pluck( $attempt )
		);

		// Check `get_question_objects()` returns the same list of `get_questions()` except for the removed question
		// when the `$filter_remove` is passed as true.
		$this->assertEqualSets(
			array_merge(
				array(
					$questions[0]
				),
				array_slice(
					$questions,
					2
				)
			),
			$this->question_object_ids_list_pluck( $attempt, true, true )
		);

	}

	/**
	 * Returns a question object id given a LLMS_Quiz_Attempt
	 *
	 * @since 5.3.0
	 *
	 * @param LLMS_Quiz_Attempt $attemt Attempt object.
	 * @return void
	 */
	private function question_object_ids_list_pluck( $attempt, $cache = true, $filter_removed = false  ) {
		return array_filter(
			array_map(
				function( $qo ) {
					return $qo->get('id');
				},
				$attempt->get_question_objects( $cache, $filter_removed )
			)
		);
	}
}
