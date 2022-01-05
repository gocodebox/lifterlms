<?php
/**
 * LifterLMS Unit Test Case Base class
 *
 * @since 3.3.1
 * @since 3.33.0 Marked `setup_get()` and `setup_post()` as deprecated and removed private `setup_request()`. Use methods from lifterlms/lifterlms_tests.
 * @since 3.37.4 Add certificate template mock generation and earning methods.
 * @since 3.37.8 Changed return of `take_quiz` method from `void` to an `LLMS_Quiz_Attempt` object
 * @since 3.37.17 Added voucher creation method.
 * @since 3.38.0 Added `setManualGatewayStatus()` method.
 * @since 4.0.0 Added create_mock_session-data() class.
 * @since 4.7.0 Disabled image sideloading during mock course generation.
 * @since 5.0.0 Automatically clear notices on teardown.
 *              Add a method to generate mock vouchers.
 * @since [version] Removed deprecated items.
 *              - `LLMS_UnitTestCase::setup_get()` method
 *              - `LLMS_UnitTestCase::setup_post()` method
 */
class LLMS_UnitTestCase extends LLMS_Unit_Test_Case {

	/**
	 * Setup tests
	 * Automatically called before each test
	 *
	 * @since 3.17.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		llms_reset_current_time();
	}

	/**
	 * Create mock user session data.
	 *
	 * @since 4.0.0
	 *
	 * @param integer $count   Number of session to create.
	 * @param boolean $expired Whether or not the sessions are expired.
	 * @return int[]
	 */
	protected function create_mock_session_data( $count = 5, $expired = false ) {

		$sessions = array();

		global $wpdb;
		$i = 1;
		while ( $i <= $count ) {
			$wpdb->insert( $wpdb->prefix . 'lifterlms_sessions', array(
				'session_key' => LLMS_Unit_Test_Util::call_method( llms()->session, 'generate_id' ),
				'data'        => serialize( array( microtime() ) ),
				'expires'     => $expired ? time() - DAY_IN_SECONDS : time() + DAY_IN_SECONDS,
			), array( '%s', '%s', '%d' ) );

			$sessions[] = $wpdb->insert_id;

			++$i;

		}

		return $sessions;

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
	 * Create a voucher.
	 *
	 * @since 3.37.17
	 *
	 * @param int   $codes    Number of codes to generate for the voucher.
	 * @param int   $uses     Number of uses per code.
	 * @param int[] $products List of course/membership ids.
	 * @return LLMS_Voucher
	 */
	protected function create_voucher( $codes = 5, $uses = 5, $products = array() ) {

		// Create the Voucher Post.
		$post_id = $this->factory->post->create( array( 'post_type' => 'llms_voucher' ) );
		$voucher = new LLMS_Voucher( $post_id );

		// Generate voucher codes.
		$i = 0;
		while( $i < $codes ) {
			$voucher->save_voucher_code( array(
				'code'             => substr( bin2hex( random_bytes( 12 ) ), 0, 12 ),
				'redemption_count' => $uses,
			) );
			++$i;
		}

		// Add a mock course if no products are specified.
		if ( ! $products ) {
			$products[] = $this->factory->post->create( array( 'post_type' => 'course' ) );
		}

		// Save the products.
		foreach ( $products as $product ) {
			$voucher->save_product( $product );
		}

		return $voucher;

	}

	/**
	 * Take a quiz for a student and get a desired grade
	 *
	 * @since 3.24.0
	 * @since 3.37.8 Change return from `void` to an `LLMS_Quiz_Attempt` object
	 *
	 * @param int $quiz_id    WP Post ID of the Quiz.
	 * @param int $student_id WP Used ID of the student.
	 * @param int $grade      Desired grade. Do the math in the test, this can't make the grade happen if it's not possible
	 *                        for example a quiz with 5 questions CANNOT get a 75%!
	 *
	 * @return LLMS_Quiz_Attempt
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

		return $attempt;

	}

	/**
	 * Generates a set of mock courses
	 *
	 * @since 3.7.3
	 * @since 4.7.0 Disabled image sideloading during mock course generation.
	 *
	 * @param    integer    $num_courses   number of courses to generate
	 * @param    integer    $num_sections  number of sections to generate for each course
	 * @param    integer    $num_lessons   number of lessons to generate for each section
	 * @param    integer    $num_quizzes   number of quizzes to generate for each section
	 *                                     quizzes will be attached to the last lessons ie each section
	 *                                     if you generate 3 lessons / section and 1 quiz / section the quiz
	 *                                     will always be the 3rd lesson
	 * @return   array 					   indexed array of course ids
	 */
	protected function generate_mock_courses( $num_courses = 1, $num_sections = 5, $num_lessons = 5, $num_quizzes = 1, $num_questions = 5 ) {

		$courses = array();
		$i = 1;
		while ( $i <= $num_courses ) {
			$courses[] = $this->get_mock_course_array( $i, $num_sections, $num_lessons, $num_quizzes, $num_questions );
			$i++;
		}

		add_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );

		$gen = new LLMS_Generator( array( 'courses' => $courses ) );
		$gen->set_generator( 'LifterLMS/BulkCourseGenerator' );
		$gen->set_default_post_status( 'publish' );
		$gen->generate();

		remove_filter( 'llms_generator_is_image_sideloading_enabled', '__return_false' );
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

		$gateway = llms()->payment_gateways()->get_gateway_by_id( 'manual' );
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

	/**
	 * Retrieve a mock access plan
	 *
	 * Automatically generates a course associated with the plan.
	 *
	 * @since 3.38.0
	 *
	 * @param float   $price      Plan price.
	 * @param integer $frequency  Recurring frequency.
	 * @param string  $expiration Plan expiration.
	 * @param boolean $on_sale    Whether or not the plan is on sale.
	 * @param boolean $trial      whether or not the plan has a trial.
	 * @return LLMS_Access_Plan
	 */
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
			'is_free' => $price > 0 ? 'no' : 'yes',
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

	/**
	 * Generate a mock voucher.
	 *
	 * @since 5.0.0
	 *
	 * @param int $codes Number of codes to create for the voucher.
	 * @param int $uses Number of uses for each code.
	 * @param array $products Array of WP_Post IDs to associate with voucher.
	 * @return LLMS_Voucher
	 */
	protected function get_mock_voucher( $codes = 5, $uses = 1, $products = array() ) {

		$voucher_id = $this->factory->post->create( array( 'post_type' => 'llms_voucher' ) );
		$voucher = new LLMS_Voucher( $voucher_id );

		if ( ! $products ) {
			$products = array( $this->factory->course->create( array( 'sections' => 0 ) ) );
		}

		array_map( array( $voucher, 'save_product' ), $products );

		$i = 1;
		while( $i <= $codes ) {
			$voucher->save_voucher_code( array(
				'code'             => wp_generate_password( 12, false ),
				'redemption_count' => $uses,
			) );
			++$i;
		}

		return $voucher;

	}

	protected function get_mock_student( $login = false ) {
		$student_id = $this->factory->user->create( array( 'role' => 'student' ) );
		if ( $login ) {
			wp_set_current_user( $student_id );
		}
		return llms_get_student( $student_id );
	}


	/**
	 * Create a certificate template post.
	 *
	 * @since 3.37.4
	 *
	 * @param string $title   Certificate title.
	 * @param string $content Certificate content.
	 * @param int    $image   Certificate background image ID.
	 * @return int
	 */
	protected function create_certificate_template( $title = 'Mock Certificate Title', $content = '', $image = '' ) {

		$template = $this->factory->post->create( array(
			'post_type' => 'llms_certificate',
			'post_content' => $content ? $content : '{site_title}, {current_date}',
		) );
		update_post_meta( $template, '_llms_certificate_title', $title );
		set_post_thumbnail( $template, $image );

		return $template;

	}

	/**
	 * Create an achievement template post.
	 *
	 * @since [version]
	 *
	 * @param string $title Achievement title.
	 * @param string $image Achievement image path.
	 * @return int
	 */
	protected function create_achievement_template( $title = 'Mock Achievement Title',  $image = '' ) {

		return $this->factory->post->create( array(
			'post_type' => 'llms_achievement',
			'meta_input' => array(
				'_llms_achievement_title' => $title,
				'_llms_achievement_image' => $image,
			),
		) );
	}

	protected function create_email_template( $subject = 'Mock Email Title' ) {

		return $this->factory->post->create( array(
			'post_type' => 'llms_email',
			'meta_input' => array(
				'_llms_email_subject' => $subject,
			),
		) );
	}

	/**
	 * Earn an achievement for a user.
	 *
	 * @since 3.37.3
	 * @since 3.37.4 Moved to `LLMS_UnitTestCase`.
	 * @since [version] Add `$engagement` param & use `LLMS_Engagement_Handler::handle_certificate()` in favor of deprecated `LLMS_Certificates::trigger_engagement()`.
	 *
	 * @param int      $user       WP_User ID.
	 * @param int      $template   WP_Post ID of the `llms_certificate` template.
	 * @param int      $related    WP_Post ID of the related post.
	 * @param int|null $engagement WP_Post ID of the engagement post.
	 * @return int[] {
	 *     Indexed array containing information about the earned certificate.
	 *
	 *     int $0 WP_User ID.
	 *     int $1 WP_Post ID of the earned cert (`llms_my_achievement`).
	 *     int $2 WP_Post ID of the related post.
	 *     int $3 WP_Post ID of the triggering engagement.
	 * }
	 */
	protected function earn_achievement( $user_id, $template_id, $related_id, $engagement_id = null ) {

		llms_enroll_student( $user_id, $related_id );

		$earned = LLMS_Engagement_Handler::handle_achievement( array( $user_id, $template_id, $related_id, $engagement_id ) );

		return array(
			$user_id,
			$earned->get( 'id' ),
			$related_id,
			$engagement_id,
		);

	}

	/**
	 * Earn a certificate for a user.
	 *
	 * @since 3.37.3
	 * @since 3.37.4 Moved to `LLMS_UnitTestCase`.
	 * @since [version] Add `$engagement` param & use `LLMS_Engagement_Handler::handle_certificate()` in favor of deprecated `LLMS_Certificates::trigger_engagement()`.
	 *
	 * @param int      $user       WP_User ID.
	 * @param int      $template   WP_Post ID of the `llms_certificate` template.
	 * @param int      $related    WP_Post ID of the related post.
	 * @param int|null $engagement WP_Post ID of the engagement post.
	 * @return int[] {
	 *     Indexed array containing information about the earned certificate.
	 *
	 *     int $0 WP_User ID.
	 *     int $1 WP_Post ID of the earned cert (`llms_my_certificate`).
	 *     int $2 WP_Post ID of the related post.
	 *     int $3 WP_Post ID of the triggering engagement.
	 * }
	 */
	protected function earn_certificate( $user_id, $template_id, $related_id, $engagement_id = null ) {

		llms_enroll_student( $user_id, $related_id );

		$earned = LLMS_Engagement_Handler::handle_certificate( array( $user_id, $template_id, $related_id, $engagement_id ) );

		return array(
			$user_id,
			$earned->get( 'id' ),
			$related_id,
			$engagement_id,
		);

	}

	/**
	 * Toggle the status of the manual payment gateway.
	 *
	 * @since 3.38.0
	 *
	 * @param string $enabled Status of the gateway, "yes" for enabled and "no" for disabled.
	 */
	protected function setManualGatewayStatus( $enabled = 'yes' ) {

		$manual = llms()->payment_gateways()->get_gateway_by_id( 'manual' );
		update_option( $manual->get_option_name( 'enabled' ), $enabled );

	}

	/**
	 * Create an engagement post and template post
	 *
	 * @since [version]
	 *
	 * @param string  $trigger_type    Type of trigger (see list below).
	 * @param string  $engagement_type Type of engagement to be awarded (email, achievement, certificate).
	 * @param integer $delay           Sending delay for the created engagement trigger.
	 * @return WP_Post Post object for the created `llms_engagement` post type.
	 */
	public function create_mock_engagement( $trigger_type, $engagement_type, $delay = 0, $trigger_post = null, $engagement_post = null ) {

		if ( ! $trigger_post ) {

			/**
			 * Trigger Types
			 *
			 * user_registration
			 *
			 * course_completed
			 * lesson_completed
			 * section_completed
			 *
			 * course_track_completed
			 *
			 * quiz_completed
			 * quiz_passed
			 * quiz_failed
			 *
			 * course_enrollment
			 * membership_enrollment
			 *
			 * access_plan_purchased
			 * course_purchased
			 * membership_purchased
			 */
			switch ( $trigger_type ) {
				case 'user_registration':
					$trigger_post = 0;
					break;

				case 'course_completed':
				case 'lesson_completed':
				case 'section_completed':
				case 'quiz_completed':
				case 'quiz_passed':
				case 'quiz_failed':
				case 'course_enrollment':
				case 'membership_enrollment':
				case 'access_plan_purchased':
				case 'course_purchased':
				case 'membership_purchased':
					$post_type    = str_replace( array( '_completed', '_enrollment', '_passed', '_failed', '_purchased' ), '', $trigger_type );
					$post_type    = in_array( $post_type, array( 'access_plan', 'membership', 'quiz' ), true ) ? 'llms_' . $post_type : $post_type;
					$trigger_post = $this->factory->post->create( compact( 'post_type' ) );
					break;
			}

		}

		if ( ! $engagement_post ) {

			$engagement_create_func = "create_{$engagement_type}_template";
			$engagement_post        = $this->$engagement_create_func();

		}

		return $this->factory->post->create_and_get( array(
			'post_type'  => 'llms_engagement',
			'meta_input' => array(
				'_llms_trigger_type'            => $trigger_type,
				'_llms_engagement_trigger_post' => $trigger_post,
				'_llms_engagement_type'         => $engagement_type,
				'_llms_engagement'              => $engagement_post,
				'_llms_engagement_delay'        => $delay,
			)
		) );

	}

}
