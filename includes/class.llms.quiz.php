<?php

use LLMS\Users\User;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Base Question Class
*/
class LLMS_Quiz {

	/**
	* ID
	* @access public
	* @var int
	*/
	public $id;

	/**
	* Post Object
	* @access public
	* @var array
	*/
	public $post;

	/**
	* Constructor
	*
	* initializes the quiz object based on post data
	*/
	public function __construct( $quiz ) {

		if ( is_numeric( $quiz ) ) {

			$this->id   = absint( $quiz );
			$this->post = get_post( $this->id );

		}

		elseif ( $quiz instanceof LLMS_Quiz ) {

			$this->id   = absint( $quiz->id );
			$this->post = $quiz;

		}

		elseif ( isset( $quiz->ID ) ) {

			$this->id   = absint( $quiz->ID );
			$this->post = $quiz;

		}

	}

	/**
	* __isset function
	*
	* checks if metadata exists
	*
	* @param string $item
	*/
	public function __isset( $item ) {

		return metadata_exists( 'post', $this->id, '_' . $item );

	}

	/**
	* __get function
	*
	* initializes the quiz object based on post data
	*
	* @param string $item
	* @return string $value
	*/
	public function __get( $item ) {

		$value = get_post_meta( $this->id, '_' . $item, true );

		return $value;
	}

	public function get_id()
	{
		return $this->id;
	}

	/**
	 * Get Allowed Attempts
	 *
	 * @return string
	 */
	public function get_total_allowed_attempts() {

		return $this->llms_allowed_attempts;

	}

	/**
	 * Get Passing Percent
	 *
	 * @return string
	 */
	public function get_passing_percent() {

		return $this->llms_passing_percent;

	}

	public function get_time_limit() {
		return $this->llms_time_limit;
	}

	/**
	 * returns the total points possible
	 * @return int [sum of all question points]
	 */
	public function get_total_possible_points() {
		$questions = $this->get_questions();

		$points = 0;

		if ( ! empty( $questions ) ) {
			foreach ( $questions as $key => $value ) {
				$points += $value['points'];
			}
		}
		return ( $points != 0 ? $points : 0 );
	}

	/**
	 * Get weight of individual question
	 * @return int[question weight]
	 */
	public function get_point_weight() {
		return ( 100 / $this->get_total_possible_points() );
	}

	/**
	 * Get Grade
	 * Multiply total points earned by total point wieght
	 * 
	 * @param  int $points [total points earned]
	 * @return int [numeric representation of grade percentage]
	 */
	public function get_grade($points) {
		return $points * $this->get_point_weight();
	}

	/**
	 * Get user grade
	 * @param  int $user_id [ID of user]
	 * @return int [quiz grade]
	 */
	public function get_user_grade( $user_id ) {
		$grade = 0;
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );


		if ( ! $quiz ) {
			return;
		}

		if ( $quiz ) {
			foreach ( $quiz as $key => $value ) {
				if ( $value['id'] == $this->id ) {
					$grade = $value['grade'];
				}

			}
		}
		return round($grade);
	}

	/**
	 * Get Best Grade
	 * Finds best grade in grades array
	 * 
	 * @param  int $user_id [ID of user]
	 * @return int [best grade]
	 */
	public function get_best_grade( $user_id ) {
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );

		//get all grades and add to grades array
		$grades = array();

		if ($quiz) {
			foreach ( $quiz as $key => $value ) {
				if ( $value['id'] == $this->id ) {
					if ( $value['grade'] ) {
						array_push( $grades, $value['grade'] );
					}
				}
			}
		}

		$highest_grade = ( empty( $grades ) ? 0 : max( $grades ) );
		return round($highest_grade);
	}

	/**
	 * Get Id of quiz with best grade
	 * @param  int $user_id [ID of user]
	 * @return int [ID of quiz attempt]
	 */
	public function get_best_quiz_attempt( $user_id ) {
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );
		$grades = array();
		$unique_id = '';

		if ( $quiz ) {

			foreach ( $quiz as $key => $value ) {
				if ( $value['id'] == $this->id ) {
					if ( $value['grade'] ) {
						array_push( $grades, $value['grade'] );
					}
				}
			}
			$highest_grade = ( empty( $grades ) ? 0 : max( $grades ) );

			foreach ( $quiz as $key => $value ) {
				if ( $value['id'] == $this->id && $highest_grade == $value['grade'] ) {
					$unique_id = $value['wpnonce'];
				}
			}

		}
		
		return $unique_id;
	}

	/**
	 * Get total time spent on quiz
	 * Subtract starttime from endtime
	 * 
	 * @param  int $user_id [ID of user]
	 * @param  string $unique_id [wpnonce of quiz submit]
	 * @return string [formatted string representing total minutes]
	 */
	public function get_total_time( $user_id, $unique_id = '' ) {
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );
		$total_time = 0;
		if ( $quiz ) {
			foreach ( $quiz as $key => $value ) {
				if ( $unique_id == $value['wpnonce'] ) {
					if ( $value['end_date'] ) {
						$start_date = strtotime( $value['start_date'] );
						$end_date = strtotime( $this->get_end_date( $user_id, $unique_id ) );
						$total_time = round( round( abs( $end_date - $start_date ) / 60, 2 ) ) . " minutes";
					}
					break;
				}
				elseif ( $value['id'] == $this->id ) {
					if ( $value['end_date'] ) {
						$start_date = strtotime( $value['start_date'] );
						$end_date = strtotime( $this->get_end_date( $user_id ) );
						$total_time = round( round( abs( $end_date - $start_date ) / 60, 2 ) ) . " minutes";
					}
				}
			}
		}
		return $total_time;
	}

	/**
	 * Check if quiz score is passing grade
	 * 
	* @param  int $user_id [ID of user]
	 * @return bool [is grade > required passing percent]
	 */
	public function is_passing_score( $user_id ) {
		return ( $this->get_passing_percent() <= $this->get_best_grade( $user_id ) );
	}

	public function get_end_date( $user_id, $unique_id = '' ) {
		$end_date = '';
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );

		foreach ( $quiz as $key => $value ) {
			if ( $value['wpnonce'] == $unique_id ) {
				$end_date = $value['end_date'];
				break;
			}
			elseif ( $value['id'] == $this->id ) {
				$end_date = $value['end_date'];
			}
		}
		return $end_date;
	}

	/**
	 * Get Quiz Start Time
	 * 
	 * @param  int $user_id [ID of user]
	 * @param  string $unique_id [quiz wpnonce]
	 * 
	 * @return datetime [time user started quiz]
	 */
	public function get_start_date( $user_id, $unique_id = '' ) {
		$start_date = '';

		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );

		if ( $quiz ) {

			foreach ( $quiz as $key => $value ) { 
				if ( $value['wpnonce'] == $unique_id ) {
					$start_date = $value['start_date'];
					break;
				}
				elseif ( $value['id'] == $this->id ) {
					$start_date = $value['start_date'];
				}
			}
		

		}

		return $start_date;
		
	}

	/**
	 * Get lesson associated with quiz
	 * @param  int $user_id [ID of user]
	 * @return int [ID of associated lesson with quiz attempt]
	 */
	public function get_assoc_lesson( $user_id ) {
		
		$lesson = false;
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );

		if ( ! $quiz ) {
			return false;
		}
		foreach ( $quiz as $key => $value ) {
			if ( $value['id'] == $this->id ) {
				$lesson = $value['assoc_lesson'];
			}
		}
		return $lesson;
	}

	/**
	 * Get total attempts by user
	 * @param  int $user_id [ID of user]
	 * @return int [number of times user has taken quiz]
	 */
	public function get_total_attempts_by_user( $user_id ) {
		global $wpdb;
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );
		$attempts = 0;

		if ( $quiz ) {
			foreach ( $quiz as $key => $value ) {
				if ( $value['id'] == $this->id ) {
					$attempts++;
				}
			}
		}

		return $attempts;
	}

	/**
	 * Get remaining quiz attempts
	 * @param  int $user_id [ID of user]
	 * @return int [number of attempts user has remaining]
	 */
	public function get_remaining_attempts_by_user( $user_id ) {
		$attempts_allowed = $this->get_total_allowed_attempts();
		$attempts = $this->get_total_attempts_by_user( $user_id );

		if ( !empty( $attempts_allowed ) ) {

			if ( empty($attempts) ) {

				$attempts = 0;
			}

			$total_attempts_remaining = ($attempts_allowed - $attempts);
		
		} else {

			$total_attempts_remaining = __('unlimited','lifterlms');

		}

		return $total_attempts_remaining;
	}

	/**
	 * Get Quiz Questions
	 * @return array [quiz questions]
	 */
	public function get_questions() {
		return $this->llms_questions;

	}

	/**
	 * Get Number of questions in quiz
	 * @return int [number of questions in quiz]
	 */
	public function get_question_count() {
		return count( $this->llms_questions );
	}

	/**
	 * Get number of correct answers
	 * @param  int $user_id [ID of user]
	 * @param  string $unique_id [quiz wpnonce]
	 * @return int [total number of correct answers]
	 */
	public function get_correct_answers_count( $user_id, $unique_id = '' ) {
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );
		$wpnonce = '';

		if ( $quiz ) {
			foreach ( $quiz as $key => $value ) {
				if ( $unique_id == $value['wpnonce'] ) {
					$count = 0;
					$wpnonce = $value['wpnonce'];
					foreach ( $value['questions'] as $k => $v ) {
						if ( $v['correct'] ) {
							$count++;
						}
					}
				}
				elseif ( $value['id'] == $this->id && $wpnonce == '' ) {
					$count = 0;
					foreach ( $value['questions'] as $k => $v ) {
						if ( $v['correct'] ) {
							$count++;
						}
					}
				}
			}
		}
		return $count;
	}

	/**
	 * Get question key
	 * @param  int $question_id [ID of question]
	 * @return key [key of question in questions array]
	 */
	public function get_question_key ($question_id) {
		foreach ($this->get_questions() as $key => $value) {
			if ($key == $quiz_id) {
				$question_key = $key;
			}
		}
		return $question_key;
	}

	/**
	 * Start Quiz submit handler
	 * Performs security and verification checks
	 * If quiz is enabled for user redirects user to 1st question.
	 *
	 * @return void
	 */
	public static function start_quiz( $quiz_id, $user_id ) {
		
		$quiz = LLMS()->session->get( 'llms_quiz' );

		if( $quiz->id == $quiz_id && $quiz->user_id == $user_id ) {

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
				$quiz_data[] = $quiz_array;
			}

			update_user_meta( $quiz->user_id, 'llms_quiz_data', $quiz_data );

			//save quiz object to session
			LLMS()->session->set( 'llms_quiz', $quiz );

			//return first question in quiz
			return $quiz->questions[ 0 ][ 'id' ];

		}
		else {

			$response['message'] = __( 'There was an error starting the quiz. Please return to the lesson and begin again.', 'lifterlms' );
			return $response;

		}

	}

	/**
	 * answer question form post (next lesson / complete quiz button click)
	 * inserts answer in database and adds it to current quiz session
	 *
	 * @return void
	 */
	public static function answer_question( $quiz_id, $question_id, $question_type, $answer,  $complete ) {

			//get quiz object from session
			$quiz = LLMS()->session->get( 'llms_quiz' );

			//if quiz session does not exist return an error message to the user.
			if( empty( $quiz ) ) {

				$response['message'] = __( 'There was an error finding the associated quiz. Please return to the lesson and begin quiz again.', 'lifterlms' );
				return $response;

			}

			//get question meta data
			$correct_option   = '';
			$question_options = get_post_meta( $question_id, '_llms_question_options', true );

			foreach( $question_options as $key => $value ) {
				if( $value[ 'correct_option' ] ) {
					$correct_option = $key;
				}
			}

			//update quiz object
			foreach( (array)$quiz->questions as $key => $value ) {

				if( $value[ 'id' ] == $question_id ) {

					$current_question = $value[ 'id' ];

					$quiz->questions[ $key ][ 'answer' ] = $answer;

					if( $answer == $correct_option ) {
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
						if( $data[ 'id' ] == $question_id ) {

							$quiz_data[ $key ][ 'questions' ][ $id ][ 'answer' ]  = $quiz->questions[ $id ][ 'answer' ];
							$quiz_data[ $key ][ 'questions' ][ $id ][ 'correct' ] = $quiz->questions[ $id ][ 'correct' ];

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

		//setup response array
		$response = array();

		//if there is not a next querstion end the quiz
		if( empty( $next_question_id ) || $complete ) {

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

				$response['message'] = __( 'There was an error with your quiz.', 'lifterlms' );
				return $response;

			}

			$response['redirect'] = get_permalink( $quiz->id );
			return $response;

		}
		else {

			$response['next_question_id'] = $next_question_id;
			return $response;

		}

	}

	/**
	 * Previous question button click
	 * Finds the previous question and redirects the user to the post
	 *
	 * @return void
	 */
	public static function previous_question( $question_id ) {

		$quiz = LLMS()->session->get( 'llms_quiz' );

		foreach( (array)$quiz->questions as $key => $value ) {
			if( $value[ 'id' ] == $question_id ) {
				$previous_question_key = ( $key - 1 );
				if( $previous_question_key >= 0 ) {
					$prev_question_link = get_permalink( $quiz->questions[ $previous_question_key ][ 'id' ] );
					$redirect           = get_permalink( $prev_question_link );

					return $quiz->questions[ $previous_question_key ][ 'id' ];

				}
			}
		}
	}

	public function show_quiz_results()
	{
		return $this->llms_show_results;
	}

	public function show_correct_answer()
	{
		return $this->llms_show_correct_answer;
	}

	public function show_description_wrong_answer()
	{
		return $this->llms_show_options_description_wrong_answer;
	}

	public function show_description_right_answer()
	{
		return $this->llms_show_options_description_right_answer;
	}

	public function get_users_last_attempt(User $user)
	{
		$quiz_data = $user->get_quiz_data();

		$last_attempt = array();

		foreach ((array)$quiz_data as $quiz)
		{
			if ((int)$quiz['id'] === (int)$this->get_id() 
				&& (int)$this->get_total_attempts_by_user($user->get_id()) === (int)$quiz['attempt'])
			{
				$last_attempt = $quiz;
			}
		}

		return $last_attempt;
	}

	public function get_show_random_answers()
	{
		return $this->llms_random_answers;
	}

}
