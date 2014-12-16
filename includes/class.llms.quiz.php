<?php
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
		return $grade;
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
		return $highest_grade;
	}

	/**
	 * Get Id of quiz with best grade
	 * @param  int $user_id [ID of user]
	 * @return int [ID of quiz attempt]
	 */
	public function get_best_quiz_attempt( $user_id ) {
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );
		$grades = array();

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
						$total_time = round( abs( $end_date - $start_date ) / 60, 2 ) . " minutes";
					}
					break;
				}
				elseif ( $value['id'] == $this->id ) {
					if ( $value['end_date'] ) {
						$start_date = strtotime( $value['start_date'] );
						$end_date = strtotime( $this->get_end_date( $user_id ) );
						$total_time = round( abs( $end_date - $start_date ) / 60, 2 ) . " minutes";
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
		return ( $this->get_passing_percent() < $this->get_best_grade( $user_id ) );
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
		$end_date = '';
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );

		foreach ( $quiz as $key => $value ) { 
			if ( $value['wpnonce'] == $unique_id ) {
				$start_date = $value['start_date'];
				break;
			}
			elseif ( $value['id'] == $this->id ) {
				$start_date = $value['start_date'];
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
		$end_date = '';
		$quiz = get_user_meta( $user_id, 'llms_quiz_data', true );

		if ( ! $quiz ) {
			return;
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

		if ( empty($attempts) ) {
			$attempts = 0;
		}

		$total_attempts_remaining = ($attempts_allowed - $attempts);

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

}