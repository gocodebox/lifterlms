<?php

use LLMS\Users\User;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Base Question Class
*/
class LLMS_Quiz_Legacy {

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

		} elseif ( $quiz instanceof LLMS_Quiz ) {

			$this->id   = absint( $quiz->id );
			$this->post = $quiz;

		} elseif ( isset( $quiz->ID ) ) {

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

	public function get_id() {

		return $this->id;
	}

	/**
	 * Retrieve the course associated with the lesson
	 * @return   obj|null     Instance of the LLMS_Course or null
	 * @since    3.6.0
	 * @version  3.8.1
	 */
	public function get_course( $user_id = null ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$lesson_id = $this->get_assoc_lesson( $user_id );

		// this handles getting the lesson when the quiz hasn't been saved yet or has just been started
		if ( ! $lesson_id ) {
			$session = LLMS()->session->get( 'llms_quiz' );
			$lesson_id = ( $session && isset( $session->assoc_lesson ) ) ? $session->assoc_lesson : false;
		}

		if ( $lesson_id ) {
			$lesson = llms_get_post( $lesson_id );
			return $lesson->get_course();
		}

		return null;

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
		return ( 0 != $points ? $points : 0 );
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
	public function get_grade( $points ) {
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
		return round( $grade );
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

		if ( $quiz ) {
			foreach ( $quiz as $key => $value ) {
				if ( $value['id'] == $this->id ) {
					if ( $value['grade'] ) {
						array_push( $grades, $value['grade'] );
					}
				}
			}
		}

		$highest_grade = ( empty( $grades ) ? 0 : max( $grades ) );
		return round( $highest_grade );
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
					//best attempt
					if ( $value['end_date'] ) {
						$total_time = $this->get_date_diff( $value['start_date'], $this->get_end_date( $user_id, $unique_id ) );
					}
					break;
				} elseif ( $value['id'] == $this->id ) {
					if ( $value['end_date'] ) {
						$total_time = $this->get_date_diff( $value['start_date'], $this->get_end_date( $user_id ) );
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
	 * @param int $grade grade for current quiz
	 * @return bool [is grade > required passing percent]
	 */
	public function is_passing_score( $user_id, $grade ) {
		// $biggest_score = max( $this->get_best_grade( $user_id ), $grade );

		return ( $this->get_passing_percent() <= $grade );
	}

	public function get_end_date( $user_id, $unique_id = '' ) {
		$end_date = '';
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );

		foreach ( $quiz as $key => $value ) {
			if ( $value['wpnonce'] == $unique_id ) {
				return $value['end_date'];
			} elseif ( $value['id'] == $this->id ) {
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
				//best
				if ( $value['wpnonce'] == $unique_id ) {
					return  $value['start_date'];
				} elseif ( $value['id'] == $this->id ) {
					$start_date = $value['start_date'];
				}
			}
		}

		return $start_date;

	}

	/**
	 * Get lesson associated with quiz
	 * @param    int $user_id [ID of user]
	 * @return   int [ID of associated lesson with quiz attempt]
	 * @since    1.0.0
	 * @version  3.9.0
	 */
	public function get_assoc_lesson( $user_id ) {

		$student = llms_get_student( $user_id );
		if ( ! $student ) {
			return false;
		}

		// if there's only one possible lesson the quiz can be associated with
		// return that lesson id
		$query = new WP_Query( array(
			'post_status' => 'publish',
			'post_type' => 'lesson',
			'posts_per_page' => 1,
			'meta_key' => '_llms_assigned_quiz',
			'meta_value' => $this->get_id(),
		) );

		if ( 1 == $query->found_posts ) {
			return $query->posts[0]->ID;
		}

		$current = $student->quizzes()->get_current_attempt( $this->get_id() );
		if ( $current ) {
			return $current->get( 'lesson_id' );
		}

		return false;

	}


	/**
	 * Get remaining quiz attempts
	 * @param  int $user_id [ID of user]
	 * @return int [number of attempts user has remaining]
	 *
	 * @version 3.0.0 -- display 0 instead of negative attempts
	 */
	public function get_remaining_attempts_by_user( $user_id ) {
		$attempts_allowed = $this->get_total_allowed_attempts();
		$attempts = $this->get_total_attempts_by_user( $user_id );

		if ( ! empty( $attempts_allowed ) ) {

			if ( empty( $attempts ) ) {

				$attempts = 0;
			}

			$total_attempts_remaining = ( $attempts_allowed - $attempts );

			// don't show negative attmepts
			if ( $total_attempts_remaining < 0 ) {

				$total_attempts_remaining = 0;

			}
		} else {

			$total_attempts_remaining = _x( 'Unlimited', 'quiz attempts remaining', 'lifterlms' );

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
				} elseif ( $value['id'] == $this->id && '' == $wpnonce ) {
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
	public function get_question_key( $question_id ) {
		foreach ( $this->get_questions() as $key => $value ) {
			if ( $key == $question_id ) {
				$question_key = $key;
			}
		}
		return $question_key;
	}

	/**
	 * Previous question button click
	 * Finds the previous question and redirects the user to the post
	 *
	 * @return void
	 */
	public static function previous_question( $question_id ) {

		$quiz = LLMS()->session->get( 'llms_quiz' );

		foreach ( (array) $quiz->questions as $key => $value ) {
			if ( $value['id'] == $question_id ) {
				$previous_question_key = ( $key - 1 );
				if ( $previous_question_key >= 0 ) {
					$prev_question_link = get_permalink( $quiz->questions[ $previous_question_key ]['id'] );
					$redirect           = get_permalink( $prev_question_link );

					return $quiz->questions[ $previous_question_key ]['id'];

				}
			}
		}
	}

	public function show_quiz_results() {

		return $this->llms_show_results;
	}

	public function show_correct_answer() {

		return $this->llms_show_correct_answer;
	}

	public function show_description_wrong_answer() {

		return $this->llms_show_options_description_wrong_answer;
	}

	public function show_description_right_answer() {

		return $this->llms_show_options_description_right_answer;
	}

	public function get_users_last_attempt( $user ) {

		if ( is_a( $user, '\LLMS\Users\User' ) ) {
			$user = llms_get_student( $user->get_id() );
		}

		$quiz_data = $user->quizzes()->get_all();

		$last_attempt = array();

		foreach ( (array) $quiz_data as $quiz ) {
			if ( isset( $quiz['id'] ) && (int) $quiz['id'] === (int) $this->get_id()
				&& (int) $this->get_total_attempts_by_user( $user->get_id() ) === (int) $quiz['attempt'] ) {
				$last_attempt = $quiz;
			}
		}

		return $last_attempt;
	}

	public function get_show_random_answers() {

		return $this->llms_random_answers;
	}

	/**
	 * Get human readable time difference between 2 dates
	 *
	 * Return difference between 2 dates in year, month, hour, minute or second
	 * The $precision caps the number of time units used: for instance if
	 * $time1 - $time2 = 3 days, 4 hours, 12 minutes, 5 seconds
	 * - with precision = 1 : 3 days
	 * - with precision = 2 : 3 days, 4 hours
	 * - with precision = 3 : 3 days, 4 hours, 12 minutes
	 *
	 * From: http://www.if-not-true-then-false.com/2010/php-calculate-real-differences-between-two-dates-or-timestamps/
	 *
	 * @param mixed $time1 a time (string or timestamp)
	 * @param mixed $time2 a time (string or timestamp)
	 * @param integer $precision Optional precision
	 * @return string time difference
	 */
	private function get_date_diff( $time1, $time2, $precision = 2 ) {
		return llms_get_date_diff( $time1, $time2, $precision );
	}

}
