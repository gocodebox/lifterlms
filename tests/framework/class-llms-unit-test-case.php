<?php
/**
 * LifterLMS Unit Test Case Base clase
 * @since    3.3.1
 * @version  3.24.0
 */
class LLMS_UnitTestCase extends WP_UnitTestCase {

	/**
	 * Setup tests
	 * Automatically called before each test
	 * @return   void
	 * @since    3.17.0
	 * @version  3.17.0
	 */
	public function setUp() {
		parent::setUp();
		llms_reset_current_time();
	}

	/**
	 * Setup Get data to mock post and request data
	 * @param    array      $vars  mock get data
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.4
	 */
	protected function setup_get( $vars = array() ) {
		$this->setup_request( 'GET', $vars );
	}

	/**
	 * Setup Post data to mock post and request data
	 * @param    array      $vars  mock post data
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.4
	 */
	protected function setup_post( $vars = array() ) {
		$this->setup_request( 'POST', $vars );
	}

	/**
	 * Setup reuqest data to mock post/get and request data
	 * @param    array      $vars  mock request data
	 * @return   void
	 * @since    3.19.4
	 * @version  3.19.4
	 */
	private function setup_request( $method, $vars = array() ) {
		putenv( 'REQUEST_METHOD=' . $method );
		if ( 'POST' === $method ) {
			$_POST = $vars;
		} elseif ( 'GET' === $method ) {
			$_GET = $vars;
		}
		$_REQUEST = $vars;
	}

	/**
	 * Automatically complete a percentage of courses for a student
	 * @param    integer    $student_id  WP User ID of a student
	 * @param    array      $course_ids  array of WP Post IDs for the courses
	 * @param    integer    $perc        percentage of each course complete
	 *                                   percentage is based off the total number of lessons in the course
	 *                                   fractions will be rounded up
	 * @return   void
	 * @since    3.7.3
	 * @version  3.24.0
	 */
	protected function complete_courses_for_student( $student_id = 0, $course_ids = array(), $perc = 100 ) {

		if ( ! $student_id ) {
			$student = $this->get_mock_student();
		} else {
			$student = llms_get_student( $student_id );
		}

		if ( ! is_array( $course_ids ) ) {
			$course_ids = array( $course_ids );
		}

		foreach ( $course_ids as $course_id ) {

			$course = llms_get_post( $course_id );

			// enroll the student if not already enrolled
			if ( ! $student->is_enrolled( $course_id ) ) {
				$student->enroll( $course_id );
			}

			$lessons = $course->get_lessons( 'ids' );
			$num_lessons = count( $lessons );
			$stop = 100 === $perc ? $num_lessons : round( ( $perc / 100 ) * $num_lessons );

			foreach ( $lessons as $i => $lid ) {

				// stop once we reach the stopping point
				if ( $i + 1 > $stop ) {
					break;
				}

				$lesson = llms_get_post( $lid );
				if ( $lesson->has_quiz() ) {

					$this->take_quiz( $lesson->get( 'quiz' ), $student->get_id() );

				} else {

					$student->mark_complete( $lid, 'lesson' );

				}

			}

		}

	}

	/**
	 * Take a quiz for a student and get a desired grade
	 * @param    int        $quiz_id     WP Post ID of the Quiz
	 * @param    int        $student_id  WP Used ID of the student
	 * @param    int        $grade       desired grade
	 *                                   do the math in the test, this can't make the grade happen if it's not possible
	 *                                   EG: a quiz with 5 questions CANNOT get a 75%!
	 *
	 * @return   void
	 * @since    3.24.0
	 * @version  3.24.0
	 */
	public function take_quiz( $quiz_id, $student_id, $grade = 100 ) {

		$quiz = llms_get_post( $quiz_id );
		$student = llms_get_student( $student_id );

		$attempt = LLMS_Quiz_Attempt::init( $quiz_id, $quiz->get( 'lesson_id' ), $student_id )->start();

		$questions_count = $attempt->get_count( 'gradeable_questions' );
		$points_per_question = ( 100 / $questions_count );
		$to_be_correct = $grade / $points_per_question;

		$i = 1;
		while ( $attempt->get_next_question() ) {

			$question_id = $attempt->get_next_question();

			$question = llms_get_post( $question_id );
			$correct = $question->get_correct_choice();
			// select the correct answer
			if ( $i <= $to_be_correct ) {

				$selected = $correct;

			// select a random incorrect answer
			} else {

				// filter all correct choices out of the array of choices
				$options = array_filter( $question->get_choices(), function( $choice ) {
					return ( ! $choice->is_correct() );
				} );

				// rekey
				$options = array_values( $options );

				// select a random incorrect answer
				$selected = array( $options[ rand( 0, count( $options ) - 1 ) ]->get( 'id' ) );

			}

			$attempt->answer_question( $question_id, $selected );

			$i++;

		}

		$attempt->end();

	}

	/**
	 * Generates a set of mock courses
	 * @param    integer    $num_courses   number of courses to generate
	 * @param    integer    $num_sections  number of sections to generate for each course
	 * @param    integer    $num_lessons   number of lessons to generate for each section
	 * @param    integer    $num_quizzes   number of quizzes to generate for each section
	 *                                     quizzes will be attached to the last lessons ie each section
	 *                                     if you generate 3 lessons / section and 1 quiz / section the quiz
	 *                                     will always be the 3rd lesson
	 * @return   array 					   indexed array of course ids
	 * @since    3.7.3
	 * @version  3.7.3
	 */
	protected function generate_mock_courses( $num_courses = 1, $num_sections = 5, $num_lessons = 5, $num_quizzes = 1, $num_questions = 5 ) {

		$courses = array();
		$i = 1;
		while ( $i <= $num_courses ) {
			$courses[] = $this->get_mock_course_array( $i, $num_sections, $num_lessons, $num_quizzes, $num_questions );
			$i++;
		}

		$gen = new LLMS_Generator( array( 'courses' => $courses ) );
		$gen->set_generator( 'LifterLMS/BulkCourseGenerator' );
		$gen->set_default_post_status( 'publish' );
		$gen->generate();
		if ( ! $gen->is_error() ) {
			return $gen->get_generated_courses();
		}

	}

	/**
	 * Generates an array of course data which can be passed to a Generator
	 * @param    int     $iterator      number for use as course number
	 * @param    int     $num_sections  number of sections to generate for the course
	 * @param    int     $num_lessons   number of lessons for each section in the course
	 * @param    int     $num_quizzes   number of quizzes for each section in the course
	 * @return   array
	 * @since    3.7.3
	 * @version  3.16.12
	 */
	protected function get_mock_course_array( $iterator = 1, $num_sections = 3, $num_lessons = 5, $num_quizzes = 1, $num_questions = 5 ) {

		$mock = array(
			'title' => sprintf( 'mock course %d', $iterator ),
		);

		$sections = array();
		$sections_i = 1;
		while ( $sections_i <= $num_sections ) {

			$section = array(
				'title' => sprintf( 'mock section %d', $sections_i ),
				'lessons' => array(),
			);

			$lessons_i = 1;

			$quizzes_start_i = $num_lessons - $num_quizzes + 1;

			while ( $lessons_i <= $num_lessons ) {

				$lesson = array(
					'title' => sprintf( 'mock lesson %d', $lessons_i ),
				);

				if ( $lessons_i >= $quizzes_start_i ) {

					$lesson['quiz_enabled'] = 'yes';

					$lesson['quiz'] = array(
						'title' => sprintf( 'mock quiz %d', $lessons_i ),
					);

					$questions = array();
					$questions_i = 1;
					while ( $questions_i <= $num_questions ) {

						$options_i = 1;
						$total_options = rand( 2, 5 );
						$correct_option = rand( $options_i, $total_options );
						$choices = array();
						while( $options_i <= $total_options ) {
							$choices[] = array(
								'choice' => sprintf( 'choice %d', $options_i ),
								'choice_type' => 'text',
								'correct' => ( $options_i === $correct_option ),
							);
							$options_i++;
						}
						$questions[] = array(
							'title' => sprintf( 'question %d', $questions_i ),
							'question_type' => 'choice',
							'choices' => $choices,
							'points' => 1,
						);

						$questions_i++;

					}

					$lesson['quiz']['questions'] = $questions;

				}

				array_push( $section['lessons'], $lesson );
				$lessons_i++;
			}

			array_push( $sections, $section );

			$sections_i++;

		}

		$mock['sections'] = $sections;

		return $mock;

	}

	protected function get_mock_order( $plan = null, $coupon = false ) {

		$gateway = LLMS()->payment_gateways()->get_gateway_by_id( 'manual' );
		update_option( $gateway->get_option_name( 'enabled' ), 'yes' );

		if ( ! $plan ) {
			if ( ! $this->saved_mock_plan ) {
				$plan = $this->get_mock_plan();
				$this->saved_mock_plan = $plan;
			} else {
				$plan = $this->saved_mock_plan;
			}
		}

		if ( $coupon ) {
			$coupon = new LLMS_Coupon( 'new', 'couponcode' );
			$coupon_data = array(
				'coupon_amount' => 10,
				'discount_type' => 'percent',
				'plan_type' => 'any',
			);
			foreach ( $coupon_data as $key => $val ) {
				$coupon->set( $key, $val );
			}
		}

		$order = new LLMS_Order( 'new' );
		return $order->init( $this->get_mock_student(), $plan, $gateway, $coupon );

	}

	protected function get_mock_plan( $price = 25.99, $frequency = 1, $expiration = 'lifetime', $on_sale = false, $trial = false ) {

		$course = $this->generate_mock_courses( 1, 0 );
		$course_id = $course[0];

		$plan = new LLMS_Access_Plan( 'new', 'Test Access Plan' );
		$plan_data = array(
			'access_expiration' => $expiration,
			'access_expires' => ( 'limited-date' === $expiration ) ? date( 'm/d/Y', current_time( 'timestamp' ) + DAY_IN_SECONDS ) : '',
			'access_length' => '1',
			'access_period' => 'year',
			'frequency' => $frequency,
			'is_free' => 'no',
			'length' => 0,
			'on_sale' => $on_sale ? 'yes' : 'no',
			'period' => 'day',
			'price' => $price,
			'product_id' => $course_id,
			'sale_price' => round( $price - ( $price * .1 ), 2 ),
			'sku' => 'accessplansku',
			'trial_length' => 1,
			'trial_offer' => $trial ? 'yes' : 'no',
			'trial_period' => 'week',
			'trial_price' => 1.00,
		);

		foreach ( $plan_data as $key => $val ) {
			$plan->set( $key, $val );
		}

		return $plan;

	}

	protected function get_mock_student( $login = false ) {
		$student_id = $this->factory->user->create( array( 'role' => 'student' ) );
		if ( $login ) {
			wp_set_current_user( $student_id );
		}
		return llms_get_student( $student_id );
	}

}
