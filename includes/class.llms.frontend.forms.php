<?php
	if( !defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * Front End Forms Class
	 *
	 * Class used managing front end facing forms.
	 *
	 * @version 1.0
	 * @author  codeBOX
	 * @project lifterLMS
	 */
	class LLMS_Frontend_Forms {

		/**
		 * Constructor
		 * initializes the forms methods
		 */
		public function __construct() {
			add_action( 'template_redirect', array( $this, 'save_account_details' ) );
			add_action( 'init', array( $this, 'apply_coupon' ) );
			//add_action( 'init', array( $this, 'process_free_order' ) );
			add_action( 'init', array( $this, 'create_order' ) );
			add_action( 'init', array( $this, 'confirm_order' ) );
			add_action( 'init', array( $this, 'login' ) );
			add_action( 'init', array( $this, 'user_registration' ) );
			add_action( 'init', array( $this, 'reset_password' ) );
			add_action( 'init', array( $this, 'mark_complete' ) );
			add_action( 'init', array( $this, 'take_quiz' ) );
			add_action( 'init', array( $this, 'llms_start_quiz' ) );
			add_action( 'init', array( $this, 'llms_answer_question' ) );
			add_action( 'init', array( $this, 'prev_question' ) );
			add_action( 'lifterlms_order_process_begin', array( $this, 'order_processing' ), 10, 1 );
			add_action( 'lifterlms_order_process_success', array( $this, 'order_success' ), 10, 1 );
			add_action( 'lifterlms_order_process_complete', array( $this, 'order_complete' ), 10, 1 );
			add_action( 'lifterlms_content_restricted', array( $this, 'restriction_alert' ), 10, 2 );

		}

		/**
		 * Previous question button click
		 * Finds the previous question and redirects the user to the post
		 *
		 * @return void
		 */
		public function prev_question() {
			$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );

			if( 'POST' !== $request_method ) {
				return;
			}

			if( !isset( $_POST[ 'llms_prev_question' ] ) || empty( $_POST[ '_wpnonce' ] ) ) {
				return;
			}
			if( isset( $_POST[ 'llms_prev_question' ] ) ) {
				$quiz = LLMS()->session->get( 'llms_quiz' );

				foreach( (array)$quiz->questions as $key => $value ) {
					if( $value[ 'id' ] == $_POST[ 'question_id' ] ) {
						$previous_question_key = ( $key - 1 );
						if( $previous_question_key >= 0 ) {
							$prev_question_link = get_permalink( $quiz->questions[ $previous_question_key ][ 'id' ] );
							$redirect           = get_permalink( $prev_question_link );
							wp_redirect( apply_filters( 'lifterlms_prev_question_redirect', $prev_question_link ) );
						}
					}
				}
			}
		}

		/**
		 * answer question form post (next lesson / complete quiz button click)
		 * inserts answer in database and adds it to current quiz session
		 *
		 * @return void
		 */
		public function llms_answer_question() {
			$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );

			if( 'POST' !== $request_method ) {
				return;
			}

			if( !isset( $_POST[ 'llms_answer_question' ] ) || empty( $_POST[ '_wpnonce' ] ) ) {
				return;
			}

			if( isset( $_POST[ 'llms_answer_question' ] ) ) {

				//get quiz object from session
				$quiz = LLMS()->session->get( 'llms_quiz' );

				//if quiz session does not exist return an error message to the user.
				if( empty( $quiz ) ) {
					return llms_add_notice( __( 'There was an error finding the associated quiz. Please return to the lesson and begin quiz again.', 'lifterlms' ), 'error' );
				}

				if( $_POST[ 'llms_option_selected' ] === '' ) {
					return llms_add_notice( __( 'You must answer the question to continue.', 'lifterlms' ), 'error' );
				}

				//get question meta data
				$correct_option   = '';
				$question_options = get_post_meta( $_POST[ 'question_id' ], '_llms_question_options', true );

				foreach( $question_options as $key => $value ) {
					if( $value[ 'correct_option' ] ) {
						$correct_option = $key;
					}
				}

				//update quiz object
				foreach( (array)$quiz->questions as $key => $value ) {

					if( $value[ 'id' ] == $_POST[ 'question_id' ] ) {

						$current_question = $value[ 'id' ];

						$quiz->questions[ $key ][ 'answer' ] = $_POST[ 'llms_option_selected' ];

						if( $_POST[ 'llms_option_selected' ] == $correct_option ) {
							$quiz->questions[ $key ][ 'correct' ] = true;
						}
						else {
							$quiz->questions[ $key ][ 'correct' ] = false;
						}

					}
				}

				LLMS()->session->set( 'llms_quiz', $quiz );

				//update quiz user meta data
				$quiz_data = get_user_meta( $quiz->user_id, 'llms_quiz_data', true );

				foreach( $quiz_data as $key => $value ) {

					if( $value[ 'wpnonce' ] == $quiz->wpnonce ) {

						foreach( $quiz_data[ $key ][ 'questions' ] as $id => $data ) {
							if( $data[ 'id' ] == $_POST[ 'question_id' ] ) {

								$quiz_data[ $key ][ 'questions' ][ $id ][ 'answer' ]  = $quiz->questions[ $id ][ 'answer' ];
								$quiz_data[ $key ][ 'questions' ][ $id ][ 'correct' ] = $quiz->questions[ $id ][ 'correct' ];

							}
						}
					}

				}
			}
			update_user_meta( $quiz->user_id, 'llms_quiz_data', $quiz_data );


			//if another question exists in lessons array then take user to next question
			foreach( (array)$quiz->questions as $k => $q ) {
				if( $q[ 'id' ] == $current_question ) {
					$next_question = $k + 1;
				}
				if( !empty( $next_question ) && $k == $next_question ) {
					$next_question_id = $q[ 'id' ];
				}
			}
			if( empty( $next_question_id ) ) {

				$quiz->end_date = current_time( 'mysql' );
				//$quiz->attempts = ( $quiz->attempts - 1 );

				//save quiz object to usermeta
				$quiz_array = (array)$quiz;

				if( $quiz_data ) {

					foreach( $quiz_data as $id => $q ) {

						if( $q[ 'wpnonce' ] == $quiz->wpnonce ) {

							$points = 0;
							$grade  = 0;

							//set the end time
							$quiz_data[ $id ][ 'end_date' ] = $quiz->end_date;

							$quiz_obj = new LLMS_Quiz( $quiz->id );

							//get grade
							//get total points earned
							foreach( $q[ 'questions' ] as $key => $value ) {
								if( $value[ 'correct' ] ) {
									$points += $value[ 'points' ];
								}
							}

							//calculate grade
							if( $points == 0 ) {
								$quiz_data[ $id ][ 'grade' ] = 0;
							}
							else {
								$quiz_data[ $id ][ 'grade' ] = $quiz_obj->get_grade( $points );
								update_user_meta( $quiz->user_id, 'llms_quiz_data', $quiz_data );

								$quiz_data[ $id ][ 'passed' ] = $quiz_obj->is_passing_score( $quiz->user_id );
								update_user_meta( $quiz->user_id, 'llms_quiz_data', $quiz_data );

								LLMS()->session->set( 'llms_quiz', $quiz );
							}

							if( $quiz_data[ $id ][ 'passed' ] ) {
								$lesson = new LLMS_Lesson( $quiz->assoc_lesson );
								$lesson->mark_complete( $quiz->user_id );
							}

						}


					}
				}
				else {
					return llms_add_notice( __( 'There was an error with your quiz.', 'lifterlms' ), 'error' );
				}


				//if there are no other questions in the questions array then redirect back to quiz page
				$redirect = get_permalink( $quiz->id );
				wp_redirect( apply_filters( 'lifterlms_quiz_completed_redirect', $redirect ) );

			}
			else {
				$redirect = get_permalink( $next_question_id );
				wp_redirect( $redirect );
			}

		}


		/**
		 * Start Quiz submit handler
		 * Performs security and verification checks
		 * If quiz is enabled for user redirects user to 1st question.
		 *
		 * @return void
		 */
		public function llms_start_quiz() {
			$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
			if( 'POST' !== $request_method ) {
				return;
			}

			if( !isset( $_POST[ 'llms_start_quiz' ] ) || empty( $_POST[ '_wpnonce' ] ) ) {
				return;
			}

			if( isset( $_POST[ 'llms_start_quiz' ] ) ) {

				$quiz = LLMS()->session->get( 'llms_quiz' );

				if( $quiz->id == $_POST[ 'llms-quiz_id' ] && $quiz->user_id == $_POST[ 'llms-user_id' ] ) {

					if( property_exists( $quiz, 'end_date' ) && $quiz->end_date === '' ) {
						$redirect = get_permalink( $quiz->questions[ 0 ][ 'id' ] );
						wp_redirect( apply_filters( 'lifterlms_lesson_begin_quiz', $redirect ) );
						return;
					}

					$quiz->start_date = current_time( 'mysql' );
					$quiz->end_date   = '';
					$quiz->grade      = 0;
					$quiz->passed     = false;

					//get existing quiz object from database
					$quiz_data = get_user_meta( $quiz->user_id, 'llms_quiz_data', true );

					//count previous attempts and set quiz attempt to +1 of quiz attempt count
					$attempts = 0;

					if( $quiz_data ) {

						foreach( $quiz_data as $key => $value ) {
							if( $value[ 'id' ] == $quiz->id ) {
								$attempts++;
							}
						}
					}

					$quiz->attempt = ( $attempts + 1 );
					$quiz->wpnonce = wp_create_nonce( 'my-action_' . $quiz->id . $quiz->attempt );
					//add questions to quiz object
					//question_id (int), answer (string), correct (bool)
					$quiz_obj = new LLMS_Quiz( $quiz->id );

					//$all_questions = array();
					$questions = $quiz_obj->get_questions();

					if( $questions ) {
						foreach( $questions as $key => $value ) {
							$questions[ $key ][ 'answer' ]  = '';
							$questions[ $key ][ 'correct' ] = false;
						}

						$quiz->questions = $questions;
					}
					else {
						return llms_add_notice( __( 'There are no questions associated with this quiz.', 'lifterlms' ), 'error' );
					}

					//save quiz object to usermeta
					$quiz_array = (array)$quiz;

					if( $quiz_data ) {
						array_push( $quiz_data, $quiz_array );
					}
					else {
						$quiz_data    = array();
						$quiz_data[ ] = $quiz_array;
					}

					update_user_meta( $quiz->user_id, 'llms_quiz_data', $quiz_data );

					//save quiz object to session
					LLMS()->session->set( 'llms_quiz', $quiz );

					//redirect user to first question in questions
					$redirect = get_permalink( $quiz->questions[ 0 ][ 'id' ] );
					wp_redirect( apply_filters( 'lifterlms_lesson_begin_quiz', $redirect ) );
				}
				else {
					return llms_add_notice( __( 'There was an error starting the quiz. Please return to the lesson and begin again.', 'lifterlms' ), 'error' );
				}

			}
			else {
				return llms_add_notice( __( 'There was an error starting the quiz. Please return to the lesson and begin again.', 'lifterlms' ), 'error' );
			}
		}

		/**
		 * Take quiz submit handler from lesson
		 * Redirect user to quiz if quiz is available for lesson.
		 * Creates session object llms_quiz
		 *
		 * @return void
		 */
		public function take_quiz() {

			$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
			if( 'POST' !== $request_method ) {
				return;
			}

			if( !isset( $_POST[ 'take_quiz' ] ) || empty( $_POST[ '_wpnonce' ] ) ) {
				return;
			}

			if( isset( $_POST[ 'take_quiz' ] ) ) {

				//create quiz session object
				$quiz               = new stdClass();
				$quiz->id           = $_POST[ 'quiz_id' ];
				$quiz->assoc_lesson = $_POST[ 'associated_lesson' ];
				$quiz->user_id      = (int)get_current_user_id();

				LLMS()->session->set( 'llms_quiz', $quiz );

				//redirect user to quiz page
				$redirect = get_permalink( $_POST[ 'quiz_id' ] );
				wp_redirect( apply_filters( 'lifterlms_lesson_start_quiz_redirect', $redirect ) );

			}
		}

		/**
		 * Mark Lesson as complete
		 * Complete Lesson form post
		 *
		 * Marks lesson as complete and returns completion message to user
		 *
		 * @return void
		 */
		public function mark_complete() {
			global $wpdb;

			$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
			if( 'POST' !== $request_method ) {
				return;
			}

			if( !isset( $_POST[ 'mark_complete' ] ) || empty( $_POST[ '_wpnonce' ] ) ) {
				return;
			}

			if( isset( $_POST[ 'mark-complete' ] ) ) {
				$lesson = new LLMS_Lesson( $_POST[ 'mark-complete' ] );
				$lesson->mark_complete( get_current_user_id() );
			}

		}

		/**
		 * Mark Course complete form post
		 * Called by lesson complete.
		 *
		 * If all lessons are complete in course mark course as complete
		 *
		 * @param  int $user_id   [ID of the current user]
		 * @param  int $lesson_id [ID of the current lesson]
		 *
		 * @return void
		 */
		function mark_course_complete( $user_id, $lesson_id ) {
			global $wpdb;

			$lesson    = new LLMS_Lesson( $lesson_id );
			$course_id = $lesson->get_parent_course();

			$course            = new LLMS_Course( $course_id );
			$course_completion = $course->get_percent_complete();

			$user = new LLMS_Person( $user_id );

			if( $course_completion == '100' ) {

				$key   = '_is_complete';
				$value = 'yes';

				$user_postmetas = $user->get_user_postmeta_data( $user_id, $course->id );
				if( !empty( $user_postmetas[ '_is_complete' ] ) ) {
					if( $user_postmetas[ '_is_complete' ]->meta_value === 'yes' ) {
						return;
					}
				}

				$update_user_postmeta = $wpdb->insert( $wpdb->prefix . 'lifterlms_user_postmeta',
					array(
						'user_id'      => $user_id,
						'post_id'      => $course->id,
						'meta_key'     => $key,
						'meta_value'   => $value,
						'updated_date' => current_time( 'mysql' ),
					)
				);

				do_action( 'llms_course_completed', $user_id, $course->id );

			}
		}

		/**
		 * mark section complete
		 * Called by mark_lesson_complte
		 *
		 * If all lessons in section complete mark section as complete.
		 *
		 * @param  int $user_id   [ID of the current user]
		 * @param  int $lesson_id [ID of the current lesson]
		 *
		 * @return void
		 */
		public function mark_section_complete( $user_id, $lesson_id ) {
			global $wpdb;

			$lesson          = new LLMS_Lesson( $lesson_id );
			$course_id       = $lesson->get_parent_course();
			$course          = new LLMS_Course( $course_id );
			$course_syllabus = $course->get_syllabus();
		}

		/**
		 * Processes orders that, calculated with a coupon, result in a free amount
		 * and bypass the payment gateway.
		 */
		public function process_free_order() {
			/** Stop script if necessary nonce doesn't exist or is invalid */
			// if( !array_key_exists( "_wpnonce", $_POST ) || !wp_verify_nonce( $_POST[ "_wpnonce" ], "create_order_details" ) ) {
			// 	return true;
			// }

			/** Coupon data */
			$coupon = LLMS()->session->get( "llms_coupon", array() );
			/** Order data */
			$order = LLMS()->session->get( "llms_order", array() );
			/** Don't do anything if no coupon has been applied */
			if( !isset( $coupon->id ) || !$coupon->id ) {
				return false;
			}

			//don't do anything if coupon amount does not = 100% off. 
			if ( ( $coupon->amount != '100' && $coupon->type === 'percent' ) 
				|| ( $coupon->type === "dollar" && ( $coupon->amount !== $order->total ) ) ) {
				return false;
			}

			$coupon_type     = get_post_meta( $coupon->id, "_llms_discount_type", true );
			$coupon_amount   = get_post_meta( $coupon->id, "_llms_coupon_amount", true );
			$coupon_is_valid = true;

			/** Check if coupon is valid and actually results in 0 total */
			if( $coupon_amount !== $coupon->amount ) {

				$coupon_is_valid = false;
			}
			if( $coupon_type == "percent" && $coupon_amount != 100 ) {

				$coupon_is_valid = false;
			}
			elseif( $coupon_type == "dollar" && ( $coupon_amount - $order->total ) ) {

				$coupon_is_valid = false;
			}
			if( !$coupon_is_valid ) {
				/** Clear session */
				LLMS()->session->set( "llms_coupon", "" );
				return $coupon->coupon_code;
			}

			/** Insert order into database */
			$handle = new LLMS_Order();
			$lifterlms_checkout = LLMS()->checkout();
			$handle->process_order( $order );
			$handle->update_order( $order );

			/** Clear session */
			LLMS()->session->set( "llms_coupon", "" );
			LLMS()->session->set( "llms_order", "" );

			/** Redirect to success page */
			do_action( 'lifterlms_order_process_success', $order );
			//return true;
		}

					// $lifterlms_checkout->process_order( $order );
					// $lifterlms_checkout->update_order( $order );
					
		/**
		 * Confirm order form post
		 * User clicks confirm order
		 *
		 * Executes payment gateway confirm order method and completes order.
		 * Redirects user to appropriate page / post
		 *
		 * @return void
		 */
		public function confirm_order() {
			global $wpdb;

			$paypal_enabled = get_option( 'lifterlms_gateways_paypal_enabled', '' );
			if( !empty( $paypal_enabled ) ) {
				if( !isset( $_REQUEST[ 'token' ] ) ) {
					return;
				}
			}

			$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
			if( 'POST' !== $request_method ) {
				return;
			}

			if( empty( $_POST[ 'action' ] ) || ( 'process_order' !== $_POST[ 'action' ] ) || empty( $_POST[ '_wpnonce' ] ) ) {
				return;
			}

			// noonnce the post
			wp_verify_nonce( $_POST[ '_wpnonce' ], 'lifterlms_create_order_details' );

			$order              = LLMS()->session->get( 'llms_order', array() );
			$payment_method     = $order->payment_method;
			$available_gateways = LLMS()->payment_gateways()->get_available_payment_gateways();

			$result = $available_gateways[ $payment_method ]->confirm_payment( $_REQUEST );

			$errors = new WP_Error();

			$process_result = $available_gateways[ $payment_method ]->complete_payment( $result, $order );

		}

		/**
		 * Apply Coupon to order form post
		 * Applies coupon value to session
		 * Sets price html output to discounted value
		 *
		 * @return void
		 */
		public function apply_coupon() {
			global $wpdb;

			if( !isset( $_POST[ 'llms_apply_coupon' ] ) ) {
				return;
			}

			$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
			if( 'POST' !== $request_method ) {
				return;
			}

			if( empty( $_POST[ '_wpnonce' ] ) ) {

				return;
			}

			wp_verify_nonce( $_POST[ '_wpnonce' ], 'llms-checkout-coupon' );

			$coupon = new stdClass();
			$errors = new WP_Error();

			$coupon->user_id = (int)get_current_user_id();

			if( empty( $coupon->user_id ) ) {
				return;
			}

			$coupon->coupon_code = llms_clean( $_POST[ 'coupon_code' ] );
			$coupon->product_id  = $_POST[ 'product_id' ];

			$args        = array(
				'posts_per_page' => 1,
				'post_type'      => 'llms_coupon',
				'nopaging'       => true,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'   => '_llms_coupon_title',
						'value' => $coupon->coupon_code
					)
				)
			);
			$coupon_post = get_posts( $args );

			if( empty( $coupon_post ) ) {
				return llms_add_notice( sprintf( __( 'Coupon code <strong>%s</strong> was not found.', 'lifterlms' ), $coupon->coupon_code ), 'error' );
			}

			foreach( $coupon_post as $cp ) {
				$coupon->id = $cp->ID;
			}

			//get coupon metadata
			$coupon_meta = get_post_meta( $coupon->id );

			$coupon->type   = !empty( $coupon_meta[ '_llms_discount_type' ][ 0 ] ) ? $coupon_meta[ '_llms_discount_type' ][ 0 ] : '';
			$coupon->amount = !empty( $coupon_meta[ '_llms_coupon_amount' ][ 0 ] ) ? $coupon_meta[ '_llms_coupon_amount' ][ 0 ] : '';

			$coupon->limit = $coupon_meta[ '_llms_usage_limit' ][ 0 ] !== '' ? $coupon_meta[ '_llms_usage_limit' ][ 0 ] : 'unlimited';

			$coupon->title = !empty( $coupon_meta[ '_llms_coupon_title' ][ 0 ] ) ? $coupon_meta[ '_llms_coupon_title' ][ 0 ] : '';

			if( $coupon->type == 'percent' ) {
				$coupon->name = ( $coupon->title . ': ' . $coupon->amount . '% coupon' );
			}
			elseif( $coupon->type == 'dollar' ) {
				$coupon->name = ( $coupon->title . ': ' . '$' . $coupon->amount . ' coupon' );
			}

			//if coupon limit is not unlimited deduct 1 from limit
			if( isset( $coupon->limit ) ) {
				if ( $coupon->limit !== 'unlimited' ) {
				
					if( $coupon->limit <= 0 ) {
						return llms_add_notice( sprintf( __( 'Coupon code <strong>%s</strong> cannot be applied to this order.', 'lifterlms' ), $coupon->coupon_code ), 'error' );
					}

					//remove coupon limit
					$coupon->limit = ( $coupon->limit - 1 );

				}
			}

			LLMS()->session->set( 'llms_coupon', $coupon );
			return llms_add_notice( sprintf( __( 'Coupon code <strong>%s</strong> has been applied to your order.', 'lifterlms' ), $coupon->coupon_code ), 'success' );

		}

		/**
		 * Create order
		 * Payment step 1 form submission
		 * Creates order object and order session object.
		 * passes data to payment gateway if price exists
		 * If no price exists passes order to order
		 * Redirects user to appropriate page / post
		 *
		 * @return void
		 */
		public function create_order() {
			global $wpdb;

			if( isset( $_POST[ 'llms-checkout-coupon' ] ) ) {
				return;
			}
			// check if session already exists. if it does assign it.
			$current_order = LLMS()->session->get( 'llms_order', array() );

			$request_method = strtoupper( getenv( 'REQUEST_METHOD' ) );
			if( 'POST' !== $request_method ) {
				return;
			}

			if( empty( $_POST[ 'action' ] ) || ( 'create_order_details' !== $_POST[ 'action' ] ) || empty( $_POST[ '_wpnonce' ] ) ) {

				return;
			}

			// noonnce the post
			wp_verify_nonce( $_POST[ '_wpnonce' ], 'lifterlms_create_order_details' );

			$order  = new stdClass();
			$errors = new WP_Error();

			$order->user_id = (int)get_current_user_id();

			if( empty( $order->user_id ) ) {
				return;
			}

			$user_meta = get_user_meta( $order->user_id );

			$order->billing_address_1 = ( !empty( $user_meta[ 'llms_billing_address_1' ][ 0 ] ) ? $user_meta[ 'llms_billing_address_1' ][ 0 ] : '' );
			$order->billing_address_2 = ( !empty( $user_meta[ 'llms_billing_address_2' ][ 0 ] ) ? $user_meta[ 'llms_billing_address_2' ][ 0 ] : '' );
			$order->billing_city      = ( !empty( $user_meta[ 'llms_billing_city' ][ 0 ] ) ? $user_meta[ 'llms_billing_city' ][ 0 ] : '' );
			$order->billing_state     = ( !empty( $user_meta[ 'llms_billing_state' ][ 0 ] ) ? $user_meta[ 'llms_billing_state' ][ 0 ] : '' );
			$order->billing_zip       = ( !empty( $user_meta[ 'llms_billing_zip' ][ 0 ] ) ? $user_meta[ 'llms_billing_zip' ][ 0 ] : '' );
			$order->billing_country   = ( !empty( $user_meta[ 'llms_billing_country' ][ 0 ] ) ? $user_meta[ 'llms_billing_country' ][ 0 ] : '' );

			//get POST data
			$order->product_id    = !empty( $_POST[ 'product_id' ] ) ? llms_clean( $_POST[ 'product_id' ] ) : '';
			$order->product_title = $_POST[ 'product_title' ];
			//$order->payment_method	= $_POST['payment_method'];

			$payment_method_data = explode( "_", $_POST[ 'payment_method' ] );
			$order->payment_type = $payment_method_data[ 0 ];

			if( count( $payment_method_data ) > 1 ) {
				$order->payment_method = $payment_method_data[ 1 ];
			}

			$available_gateways = LLMS()->payment_gateways()->get_available_payment_gateways();

			if( $order->payment_type == 'creditcard' && empty( $_POST[ 'use_existing_card' ] ) ) {
				if( empty( $_POST[ 'cc_type' ] ) ) {
					llms_add_notice( __( 'Please select a credit card type.', 'lifterlms' ), 'error' );
				}
				if( empty( $_POST[ 'cc_number' ] ) ) {
					llms_add_notice( __( 'Please enter a credit card number.', 'lifterlms' ), 'error' );
				}
				if( empty( $_POST[ 'cc_exp_month' ] ) ) {
					llms_add_notice( __( 'Please select an expiration month.', 'lifterlms' ), 'error' );
				}
				if( empty( $_POST[ 'cc_exp_year' ] ) ) {
					llms_add_notice( __( 'Please select an expiration year.', 'lifterlms' ), 'error' );
				}
				if( empty( $_POST[ 'cc_cvv' ] ) ) {
					llms_add_notice( __( 'Please enter the credit card CVV2 number', 'lifterlms' ), 'error' );
				}
				if( llms_notice_count( 'error' ) ) {
					return;
				}

			}

			$order->use_existing_card = empty( $_POST[ 'use_existing_card' ] ) ? '' : $_POST[ 'use_existing_card' ];

			$order->cc_type      = ( !empty( $_POST[ 'cc_type' ] ) ? $_POST[ 'cc_type' ] : '' );
			$order->cc_number    = ( !empty( $_POST[ 'cc_number' ] ) ? $_POST[ 'cc_number' ] : '' );
			$order->cc_exp_month = ( !empty( $_POST[ 'cc_exp_month' ] ) ? $_POST[ 'cc_exp_month' ] : '' );
			$order->cc_exp_year  = ( !empty( $_POST[ 'cc_exp_year' ] ) ? $_POST[ 'cc_exp_year' ] : '' );
			$order->cc_cvv       = ( !empty( $_POST[ 'cc_cvv' ] ) ? $_POST[ 'cc_cvv' ] : '' );


			$order->order_completed = 'no';

			$product = new LLMS_Product( $order->product_id );

			$payment_option_data      = explode( "_", $_POST[ 'payment_option' ] );
			$order->payment_option    = $payment_option_data[ 0 ];
			$order->payment_option_id = $payment_option_data[ 1 ];

			//if $ based coupon and recurring order return error and clear session variables
			if ( $order->payment_option == 'recurring' && !empty( $coupon ) ) {
				$coupon = LLMS()->session->get( "llms_coupon", array() );
				if ( $coupon->type === 'dollar' ) {
					return llms_add_notice( __( 'Only percent based coupons can be applied to payment plans.', 'lifterlms' ), 'error' );
				}
			}
			

			$order->product_sku   = $product->get_sku();
			$order->total         = 0;
			$order->product_price = 0;
			//get product price (could be single or recurring)
			if( property_exists( $order, 'payment_option' ) ) {
				if( $order->payment_option == 'single' ) {
					$order->product_price = $product->get_single_price();
					$order->total         = $order->product_price;
				}
				elseif( $order->payment_option == 'recurring' ) {

					$subs = $product->get_subscriptions();

					foreach( $subs as $id => $sub ) {

						if( $id == $order->payment_option_id ) {
							$order->product_price      = $product->get_subscription_payment_price( $sub );
							$order->total              = $product->get_subscription_total_price( $sub );
							$order->first_payment      = $product->get_subscription_first_payment( $sub );
							$order->billing_period     = $product->get_billing_period( $sub );
							$order->billing_freq       = $product->get_billing_freq( $sub );
							$order->billing_cycle      = $product->get_billing_cycle( $sub );
							$order->billing_start_date = $product->get_recurring_next_payment_date( $sub );
						}

					}
				}
				elseif( $order->payment_option == 'none' ) {
					$order->product_price = 0;
					$order->total         = 0;
				}
			}

			if( $order->user_id <= 0 ) {

				return;
			}

			$order->currency   = get_lifterlms_currency();
			$order->return_url = $this->llms_confirm_payment_url();
			$order->cancel_url = $this->llms_cancel_payment_url();

			$url      = isset( $_POST[ 'redirect' ] ) ? llms_clean( $_POST[ 'redirect' ] ) : '';
			$redirect = LLMS_Frontend_Forms::llms_get_redirect( $url );

			// if no errors were returned save the data
			if( llms_notice_count( 'error' ) == 0 ) {

				$order = apply_filters( 'lifterlms_before_order_process', $order );

				if( $order->total == 0 ) {
					$lifterlms_checkout = LLMS()->checkout();
					$lifterlms_checkout->process_order( $order );
					$lifterlms_checkout->update_order( $order );
					do_action( 'lifterlms_order_process_success', $order );
				}
				else {
					$order_session = clone $order;
					unset( $order_session->cc_type, $order_session->cc_number, $order_session->cc_exp_month, $order_session->cc_exp_year, $order_session->cc_cvv );
					LLMS()->session->set( 'llms_order', $order_session );

					$order_discounted_to_free = $this->process_free_order();

					if ( ! $order_discounted_to_free ) {

						$lifterlms_checkout = LLMS()->checkout();
						$lifterlms_checkout->process_order( $order );
						$result = $available_gateways[ $order->payment_method ]->process_payment( $order );

					} else {
						return llms_add_notice( sprintf( "There was an error processing the payment with coupon <strong>%s</strong>.", $order_discounted_to_free ), "error" );
					}

					

				}

			}

		}

		/**
		 * Get url for redirect when user confirms payment
		 * @return string [url to redirect user to on form post]
		 */
		function llms_confirm_payment_url() {

			$confirm_payment_url = llms_get_endpoint_url( 'confirm-payment', '', get_permalink( llms_get_page_id( 'checkout' ) ) );

			return apply_filters( 'lifterlms_checkout_confirm_payment_url', $confirm_payment_url );
		}

		/**
		 * Get url for when user cancels payment
		 * @return string [url to redirect user to on form post]
		 */
		function llms_cancel_payment_url() {

			$cancel_payment_url = esc_url( get_permalink( llms_get_page_id( 'checkout' ) ) );

			return apply_filters( 'lifterlms_checkout_confirm_payment_url', $cancel_payment_url );
		}

		/**
		 * Get redirect url method
		 * Safe redirect: If there is no referer then redirect user to myaccount
		 *
		 * @param  string $url [sting of url to redirect user to]
		 *
		 * @return string  $redirec [url to redirect user to]
		 */
		public static function llms_get_redirect( $url ) {
			if( !empty( $url ) ) {

				$redirect = esc_url( $url );

			}

			elseif( wp_get_referer() ) {

				$redirect = esc_url( wp_get_referer() );

			}

			else {

				$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );

			}

			return $redirect;

		}

		/**
		 * Redirect user to confirm payment page
		 *
		 * @param  string $url [url to redirect user to]
		 *
		 * @return void
		 */
		public function order_processing( $url ) {

			$redirect = esc_url( $url );

			llms_add_notice( __( 'Please confirm your payment.', 'lifterlms' ) );

			wp_redirect( apply_filters( 'lifterlms_order_process_pending_redirect', $url ) );
			exit;

		}

		/**
		 * Redirect user on order success
		 * If order is returned successful from payment gateway
		 * Chooses the appropriate url based on order data.
		 *
		 * @param  object $order [order object]
		 *
		 * @return void
		 */
		public function order_success( $order ) {
			$product_title = $order->product_title;
			$post_obj      = get_post( $order->product_id );

			if( $post_obj->post_type == 'course' ) {
				//if post type is course then redirect user back to course
				// $course = new LLMS_Course($order->product_id);
				// $next_lesson = $course->get_next_uncompleted_lesson();

				$redirect = esc_url( get_permalink( $order->product_id ) );
				llms_add_notice( sprintf( __( 'Congratulations! You have enrolled in <strong>%s</strong>', 'lifterlms' ), $product_title ) );
			}
			elseif( $post_obj->post_type == 'llms_membership' ) {
				$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );
				llms_add_notice( sprintf( __( 'Congratulations! Your new membership level is <strong>%s</strong>', 'lifterlms' ), $product_title ) );
			}
			else {
				$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );
				llms_add_notice( sprintf( __( 'You have successfully purchased <strong>%s</strong>', 'lifterlms' ), $product_title ) );
			}

			wp_redirect( apply_filters( 'lifterlms_order_process_success_redirect', $redirect ) );

			exit;

		}

		/**
		 * Redirect user on completed order
		 *
		 * @param  int $user_id [ID of current user]
		 *
		 * @return void
		 */
		public function order_complete( $user_id ) {

			$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );

			llms_add_notice( __( 'You already own this course. You cannot purchase it again.', 'lifterlms' ) );

			wp_redirect( apply_filters( 'lifterlms_order_process_complete_redirect', $redirect ) );

		}

		/**
		 * Alert message when course / lesson is restricted by start date.
		 *
		 * @param  string $date [Formatted date for display]
		 *
		 * @return void
		 */
		public function llms_restricted_by_start_date( $date ) {
			llms_add_notice( sprintf( __( 'This content is not available until %s', 'lifterlms' ),
				$date ) );
		}

		/**
		 * Alert message when page / post is restricted
		 *
		 * @param  int    $post_id [ID of the current post / page]
		 * @param  string $reason  [string code of reason]
		 *
		 * @return void
		 */
		public function restriction_alert( $post_id, $reason ) {
			$post = get_post( $post_id );

			switch( $reason ) {
				case 'site_wide_membership':
					$membership       = get_option( 'lifterlms_membership_required', '' );
					$membership       = get_post( $membership );
					$membership_title = $membership->post_title;
					$membership_url   = get_permalink( $membership->ID );
					llms_add_notice( apply_filters( 'lifterlms_membership_restricted_message', sprintf( __( '<a href="%s">%s</a> membership level is required to access this content.', 'lifterlms' ), $membership_url, $membership_title ) ) );
					break;
				case 'membership':
					$memberships = llms_get_post_memberships( $post_id );
					foreach( $memberships as $key => $value ) {
						$membership       = get_post( $value );
						$membership_title = $membership->post_title;
						llms_add_notice( apply_filters( 'lifterlms_membership_restricted_message', sprintf( __( '%s membership level is required to access this content.', 'lifterlms' ), $membership_title ) ) );
					}
					break;
				case 'parent_membership' :
					$memberships = llms_get_parent_post_memberships( $post_id );
					foreach( $memberships as $key => $value ) {
						$membership       = get_post( $value );
						$membership_title = $membership->post_title;
						llms_add_notice( apply_filters( 'lifterlms_membership_restricted_message', sprintf( __( '%s membership level is required to access this content.', 'lifterlms' ), $membership_title ) ) );
					}
					break;
				case 'prerequisite' :
					$prerequisite = llms_get_prerequisite( get_current_user_id(), $post_id );
					$link         = get_permalink( $prerequisite->ID );

					llms_add_notice( sprintf( __( 'You must complete <strong><a href="%s" alt="%s">%s</strong></a> before accessing this content', 'lifterlms' ),
						$link, $prerequisite->post_title, $prerequisite->post_title ) );
					break;
				case 'lesson_start_date' :
					$start_date = llms_get_lesson_start_date( $post_id );

					llms_add_notice( sprintf( __( 'Lesson is not available until %s.', 'lifterlms' ), $start_date ) );
					break;
				case 'course_start_date' :
					$start_date = llms_get_course_start_date( $post_id );
					llms_add_notice( sprintf( __( 'Course is not available until %s.', 'lifterlms' ), $start_date ) );
					break;
				case 'course_end_date' :
					$end_date = llms_get_course_end_date( $post_id );
					llms_add_notice( sprintf( __( 'Course ended %s.', 'lifterlms' ), $end_date ) );
					break;
				case 'quiz_restricted' :
					//$end_date = llms_get_course_end_date($post_id);
					llms_add_notice( sprintf( __( 'You do not have access to the quiz questions. <a href="%s">Return to Account page</a>.', 'lifterlms' ), get_permalink( llms_get_page_id( 'myaccount' ) ) ) );
					//$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );
					//p_redirect( apply_filters( 'quiz_restricted_redirect', $redirect ) );
					break;
			}

		}

		/**
	* Account details form
	*
	* @return void
	*/
	public function save_account_details() {


		if ( ! $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			return;
		}

		if ( 'POST' !== strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {

			return;
		}

		if ( empty( $_POST[ 'action' ] ) || ( 'save_account_details' !== $_POST[ 'action' ] ) || empty( $_POST['_wpnonce'] ) ) {

			return;

		}

		wp_verify_nonce( $_POST['_wpnonce'], 'save_account_details' );

		$update       = true;
		$errors       = new WP_Error();
		$user         = new stdClass();

		$user->ID     = (int) get_current_user_id();
		$current_user = get_user_by( 'id', $user->ID );

		if ( $user->ID <= 0 ) {

			return;

		}

		$account_first_name = ! empty( $_POST[ 'account_first_name' ] ) 	? llms_clean( $_POST[ 'account_first_name' ] ) : '';
		$account_last_name  = ! empty( $_POST[ 'account_last_name' ] ) 		? llms_clean( $_POST[ 'account_last_name' ] ) : '';
		$account_email      = ! empty( $_POST[ 'account_email' ] ) 			? sanitize_email( $_POST[ 'account_email' ] ) : '';
		$pass1              = ! empty( $_POST[ 'password_1' ] ) 			? $_POST[ 'password_1' ] : '';
		$pass2              = ! empty( $_POST[ 'password_2' ] ) 			? $_POST[ 'password_2' ] : '';

		$user->first_name   = $account_first_name;
		$user->last_name    = $account_last_name;
		$user->user_email   = $account_email;
		$user->display_name = $user->first_name;

		if ('yes' === get_option( 'lifterlms_registration_require_address' ) ) {
			$billing_address_1 	= ! empty( $_POST[ 'billing_address_1' ] ) 		? llms_clean( $_POST[ 'billing_address_1' ] ) 	: '';
			$billing_address_2 	= ! empty( $_POST[ 'billing_address_2' ] ) 		? llms_clean( $_POST[ 'billing_address_2' ] ) 	: '';
			$billing_city 		= ! empty( $_POST[ 'billing_city' ] ) 			? llms_clean( $_POST[ 'billing_city' ] ) 			: '';
			$billing_state 		= ! empty( $_POST[ 'billing_state' ] ) 			? llms_clean( $_POST[ 'billing_state' ] ) 			: '';
			$billing_zip 		= ! empty( $_POST[ 'billing_zip' ] ) 			? llms_clean( $_POST[ 'billing_zip' ] ) 			: '';
			$billing_country 	= ! empty( $_POST[ 'billing_country' ] ) 		? llms_clean( $_POST[ 'billing_country' ] ) 	: '';
		}

		if ( 'yes' == get_option( 'lifterlms_registration_add_phone' ) ) {
			$phone = llms_clean( $_POST['phone'] );
		}

		if ( $pass1 ) {

			$user->user_pass = $pass1;

		}

		if ( empty( $account_first_name ) || empty( $account_last_name ) ) {

			llms_add_notice( __( 'Please enter your name.', 'lifterlms' ), 'error' );

		}

		if ( empty( $account_email ) || ! is_email( $account_email ) ) {

			llms_add_notice( __( 'Please provide a valid email address.', 'lifterlms' ), 'error' );

		}

		elseif ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {

			llms_add_notice( __( 'The email entered is associated with another account.', 'lifterlms' ), 'error' );

		}

		if ( ! empty( $pass1 ) && empty( $pass2 ) ) {

			llms_add_notice( __( 'Please re-enter your password.', 'lifterlms' ), 'error' );

		}

		elseif ( ! empty( $pass1 ) && $pass1 !== $pass2 ) {

			llms_add_notice( __( 'Passwords do not match.', 'lifterlms' ), 'error' );

		}

		elseif ( 'yes' === get_option( 'lifterlms_registration_require_address' ) ) {
			if ( empty( $billing_address_1 ) ) {
				llms_add_notice( __( 'Please enter your billing address.', 'lifterlms' ), 'error' );
			}
			if ( empty( $billing_city ) ) {
				llms_add_notice( __( 'Please enter your billing city.', 'lifterlms' ), 'error' );
			}
			if ( empty( $billing_state ) ) {
				llms_add_notice( __( 'Please enter your billing state.', 'lifterlms' ), 'error' );
			}
			if ( empty( $billing_zip ) ) {
				llms_add_notice( __( 'Please enter your billing zip code.', 'lifterlms' ), 'error' );
			}
			if ( empty( $billing_country ) ) {
				llms_add_notice( __( 'Please enter your billing country.', 'lifterlms' ), 'error' );
			}
		}

		do_action_ref_array( 'user_profile_update_errors', array ( &$errors, $update, &$user ) );

		if ( $errors->get_error_messages() ) {

			foreach ( $errors->get_error_messages() as $error ) {

				llms_add_notice( $error, 'error' );

			}

		}

		// if no errors were returned save the data
		if ( llms_notice_count( 'error' ) == 0 ) {

			wp_update_user( $user ) ;

			$person_address = apply_filters( 'lifterlms_new_person_address', array(
				'llms_billing_address_1' 	=>	$billing_address_1,
				'llms_billing_address_2'	=>	$billing_address_2,
				'llms_billing_city'			=>	$billing_city,
				'llms_billing_state'		=>	$billing_state,
				'llms_billing_zip'			=>	$billing_zip,
				'llms_billing_country'		=>	$billing_country
				) );

			foreach ($person_address as $key => $value ) {
				update_user_meta( $user->ID, $key, $value );
			}

			if ( 'yes' == get_option( 'lifterlms_registration_add_phone' ) ) {
				update_user_meta( $user->ID, 'llms_phone', $phone );
			}

			llms_add_notice( __( 'Account details were changed successfully.', 'lifterlms' ) );

			do_action( 'lifterlms_save_account_details', $user->ID, $_POST );

			wp_safe_redirect( get_permalink( llms_get_page_id( 'myaccount' ) ) );

			exit;
		}

	}

		/**
		 * login form
		 *
		 * @return void
		 */
		public function login() {

			if( !empty( $_POST[ 'login' ] ) && !empty( $_POST[ '_wpnonce' ] ) ) {

				wp_verify_nonce( $_POST[ '_wpnonce' ], 'lifterlms-login' );

				try {

					$creds = array();

					$validation_error = new WP_Error();

					$validation_error = apply_filters( 'lifterlms_login_errors', $validation_error, $_POST[ 'username' ], $_POST[ 'password' ] );

					if( $validation_error->get_error_code() ) {

						throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . $validation_error->get_error_message() );

					}

					if( empty( $_POST[ 'username' ] ) ) {

						throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . __( 'Username is required.', 'lifterlms' ) );

					}

					if( empty( $_POST[ 'password' ] ) ) {

						throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . __( 'Password is required.', 'lifterlms' ) );

					}

					if( is_email( $_POST[ 'username' ] ) && apply_filters( 'lifterlms_get_username_from_email', true ) ) {

						$user = get_user_by( 'email', $_POST[ 'username' ] );

						if( isset( $user->user_login ) ) {

							$creds[ 'user_login' ] = $user->user_login;

						}

						else {

							throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . __( 'A user could not be found with this email address.', 'lifterlms' ) );

						}

					}

					else {

						$creds[ 'user_login' ] = $_POST[ 'username' ];

					}

					$creds[ 'user_password' ] = $_POST[ 'password' ];
					$creds[ 'remember' ]      = isset( $_POST[ 'rememberme' ] );
					$secure_cookie            = is_ssl() ? true : false;
					$user                     = wp_signon( apply_filters( 'lifterlms_login_credentials', $creds ), $secure_cookie );

					if( is_wp_error( $user ) ) {

						throw new Exception( $user->get_error_message() );

					}

					else {

						if( !empty( $_POST[ 'redirect' ] ) ) {

							$redirect = esc_url( $_POST[ 'redirect' ] );

						}

						elseif( wp_get_referer() ) {

							$redirect = esc_url( wp_get_referer() );

						}

						else {

							$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );

						}

						// Feedback
						llms_add_notice( sprintf( __( 'You are now logged in as <strong>%s</strong>', 'lifterlms' ), $user->display_name ) );

						if( !empty( $_POST[ 'product_id' ] ) ) {

							$product_id = $_POST[ 'product_id' ];

							$course       = new LLMS_Course( $product_id );
							$user_object  = new LLMS_Person( $user->ID );
							$product      = new LLMS_Product( $product_id );
							$single_price = $product->get_single_price();
							$rec_price    = $product->get_recurring_price();

							$user_postmetas = $user_object->get_user_postmeta_data( $user->ID, $course->id );
							if( !empty( $user_postmetas[ '_status' ] ) ) {
								$course_status = $user_postmetas[ '_status' ]->meta_value;
							}


							if( ( $single_price > 0 || $rec_price > 0 ) && $course_status != 'Enrolled' ) {

								$checkout_url      = get_permalink( llms_get_page_id( 'checkout' ) );
								$checkout_redirect = add_query_arg( 'product-id', $product_id, $checkout_url );

								wp_redirect( apply_filters( 'lifterlms_checkout_redirect', $checkout_redirect ) );
								exit;
							}

							else {
								$checkout_url = get_permalink( $course->post->ID );

								wp_redirect( apply_filters( 'lifterlms_checkout_redirect', $checkout_url ) );
								exit;
							}
						}

						else {
							wp_redirect( apply_filters( 'lifterlms_registration_redirect', $redirect ) );
							exit;
						}
					}

				} catch( Exception $e ) {

					llms_add_notice( apply_filters( 'login_errors', $e->getMessage() ), 'error' );

				}
			}

		}

		/**
	* Reset password form
	*
	* @return void
	*/
	public function reset_password() {

		if ( ! isset( $_POST['llms_reset_password'] ) ) {

			return;
		}

		// process lost password form
		if ( isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] ) ) {

			wp_verify_nonce( $_POST['_wpnonce'], 'lifterlms-lost_password' );

			LLMS_Shortcode_My_Account::retrieve_password();

		}

		// process reset password form
		if ( isset( $_POST['password_1'] )
			&& isset( $_POST['password_2'] )
			&& isset( $_POST['reset_key'] )
			&& isset( $_POST['reset_login'] )
			&& isset( $_POST['_wpnonce'] ) ) {

			// verify reset key again
			$user = LLMS_Shortcode_My_Account::check_password_reset_key( $_POST['reset_key'], $_POST['reset_login'] );

			if ( is_object( $user ) ) {

				// save these values into the form again in case of errors
				$args['key']   = llms_clean( $_POST['reset_key'] );
				$args['login'] = llms_clean( $_POST['reset_login'] );

				wp_verify_nonce( $_POST['_wpnonce'], 'lifterlms-reset_password' );

				if ( empty( $_POST['password_1'] ) || empty( $_POST['password_2'] ) ) {

					llms_add_notice( __( 'Please enter your password.', 'lifterlms' ), 'error' );
					$args['form'] = 'reset_password';

				}

				if ( $_POST[ 'password_1' ] !== $_POST[ 'password_2' ] ) {

					llms_add_notice( __( 'Passwords do not match.', 'lifterlms' ), 'error' );
					$args['form'] = 'reset_password';

				}

				$errors = new WP_Error();
				do_action( 'validate_password_reset', $errors, $user );

				if ( $errors->get_error_messages() ) {

					foreach ( $errors->get_error_messages() as $error ) {

						llms_add_notice( $error, 'error');
					}

				}

				if ( 0 == llms_notice_count( 'error' ) ) {

					LLMS_Shortcode_My_Account::reset_password( $user, $_POST['password_1'] );

					do_action( 'lifterlms_person_reset_password', $user );

					wp_redirect( add_query_arg( 'reset', 'true', remove_query_arg( array( 'key', 'login' ) ) ) );

					exit;
				}
			}

		}

	}

	/**
	* User Registration form
	*
	* @return void
	*/
	public function user_registration() {

		if ( ! empty( $_POST['register'] ) ) {

			wp_verify_nonce( $_POST['register'], 'lifterlms-register' );

			do_action('lifterlms_new_user_registration', array( $this, $_POST));

			if ( 'no' === get_option( 'lifterlms_registration_generate_username' ) ) {

				$_username = $_POST['username'];

			}

			else {

				$_username = '';
			}

			if ( 'yes' === get_option( 'lifterlms_registration_require_name' ) ) {
				$_firstname = $_POST['firstname'];
				$_lastname = $_POST['lastname'];

			}
			else {
				$_firstname = '';
				$_lastname = '';
			}

			if ( 'yes' === get_option( 'lifterlms_registration_require_address' ) ) {
				$_billing_address_1 = $_POST['billing_address_1'];
				$_billing_address_2 = $_POST['billing_address_2'];
				$_billing_city = $_POST['billing_city'];
				$_billing_state = $_POST['billing_state'];
				$_billing_zip = $_POST['billing_zip'];
				$_billing_country = $_POST['billing_country'];

			}
			else {
				$_billing_address_1 = '';
				$_billing_address_2 = '';
				$_billing_city 		= '';
				$_billing_state 	= '';
				$_billing_zip 		= '';
				$_billing_country 	= '';
			}

			if ( 'yes' == get_option( 'lifterlms_registration_add_phone' ) ) {
				$_phone = $_POST['phone'];
			}

			$_password = $_POST['password'];
			$_password2 = $_POST['password_2'];

			$_agree_to_terms = ( ! empty ( $_POST['agree_to_terms'] ) ) ? true : false;

			try {

				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'lifterlms_user_registration_errors',
					$validation_error,
					$_username,
					$_firstname,
					$_lastname,
					$_password,
					$_password2,
					$_POST['email'],
					$_billing_address_1,
					$_billing_city,
					$_billing_state,
					$_billing_zip,
					$_billing_country,
					$_agree_to_terms
				);

				if ( $validation_error->get_error_code() ) {

					throw new Exception( '<strong>' . __( 'Error', 'lifterlms' ) . ':</strong> ' . $validation_error->get_error_message() );

				}

			}

			catch ( Exception $e ) {

				llms_add_notice( $e->getMessage(), 'error' );
				return;

			}

			$username   = ! empty( $_username ) ? llms_clean( $_username ) : '';
			$firstname  = ! empty( $_firstname ) ? llms_clean( $_firstname ) : '';
			$lastname   = ! empty( $_lastname ) ? llms_clean( $_lastname ) : '';
			$email      = ! empty( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
			if ( 'yes' === get_option( 'lifterlms_registration_confirm_email' ) ) {
				$email2     = ! empty( $_POST['email_confirm'] ) ? sanitize_email( $_POST['email_confirm'] ) : '';
			} else {
				$email2 = $email;
			}
			$password   = $_password;
			$password2   = $_password2;

			$billing_address_1 	= ! empty( $_billing_address_1 ) 	? llms_clean( $_billing_address_1 ) : '';
			$billing_address_2 	= ! empty( $_billing_address_2 ) 	? llms_clean( $_billing_address_2 ) : '';
			$billing_city 		= ! empty( $_billing_city ) 		? llms_clean( $_billing_city ) 		: '';
			$billing_state 		= ! empty( $_billing_state ) 		? llms_clean( $_billing_state ) 	: '';
			$billing_zip 		= ! empty( $_billing_zip ) 			? llms_clean( $_billing_zip ) 		: '';
			$billing_country 	= ! empty( $_billing_country ) 		? llms_clean( $_billing_country ) 	: '';
			$phone   		 	= ! empty( $_phone ) 				? llms_clean( $_phone ) 			: '';
			$agree_to_terms     = $_agree_to_terms;

			// Anti-spam trap
			if ( ! empty( $_POST['email_2'] ) ) {

				llms_add_notice( '<strong>' . __( 'ERROR', 'lifterlms' ) . '</strong>: ' . __( 'Anti-spam field was filled in.', 'lifterlms' ), 'error' );
				return;

			}

			$new_person = llms_create_new_person(
				$email,
				$email2,
				$username,
				$firstname,
				$lastname,
				$password,
				$password2,
				$billing_address_1,
				$billing_address_2,
				$billing_city,
				$billing_state,
				$billing_zip,
				$billing_country,
				$agree_to_terms,
				$phone
			);

			if ( is_wp_error( $new_person ) ) {

				llms_add_notice( $new_person->get_error_message(), 'error' );
				return;

			}

			llms_set_person_auth_cookie( $new_person );

			// Redirect
			if ( wp_get_referer() ) {

				$redirect = esc_url( wp_get_referer() );
			}

			else {

				$redirect = esc_url( get_permalink( llms_get_page_id( 'myaccount' ) ) );

			}
			do_action('lifterlms_user_registered', $new_person);

			if ( ! empty($_POST['product_id']) ) {

				$product_id = $_POST['product_id'];

				$course = new LLMS_Course($product_id);
				$product = new LLMS_Product($product_id);
				$single_price = $product->get_single_price();
				$rec_price = $product->get_recurring_price();



				if ( $single_price  > 0 || $rec_price > 0) {

					$checkout_url = get_permalink( llms_get_page_id( 'checkout' ) );
					$checkout_redirect = add_query_arg( 'product-id', $product_id, $checkout_url );

					wp_redirect( apply_filters( 'lifterlms_checkout_redirect', $checkout_redirect ) );
					exit;
				}

				else {
					$checkout_url = get_permalink($product_id);

					wp_redirect( apply_filters( 'lifterlms_checkout_redirect', $checkout_url ) );
					exit;
				}
			}

			else {
				wp_redirect( apply_filters( 'lifterlms_registration_redirect', $redirect ) );
				exit;
			}
		}

	}

}

new LLMS_Frontend_Forms();