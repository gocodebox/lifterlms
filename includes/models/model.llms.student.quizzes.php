<?php
/**
 * Student Quiz Data
 * Rather than instatiating this class directly use LLMS_Student->quizzes().
 * @package  LifterLMS/Models
 * @since   3.9.0
 * @version 3.16.11
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Student_Quizzes model.
 */
class LLMS_Student_Quizzes extends LLMS_Abstract_User_Data {

	/**
	 * Retrieve # of quiz attempts for a quiz
	 * @param    int     $quiz_id  WP Post ID of the quiz
	 * @param    array   $args     additional args to pass to LLMS_Query_Quiz_Attempt
	 * @return   int
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function count_attempts_by_quiz( $quiz_id ) {

		$query = new LLMS_Query_Quiz_Attempt( array(
			'student_id' => $this->get_id(),
			'quiz_id' => $quiz_id,
			'per_page' => 1,
		) );

		return $query->found_results;

	}

	/**
	 * Remove Student Quiz attempt by ID
	 * @param    int     $attempt_id  Attempt ID
	 * @return   boolean              true on success, false on error
	 * @since    3.9.0
	 * @version  3.16.11
	 */
	public function delete_attempt( $attempt_id ) {

		$attempt = $this->get_attempt_by_id( $attempt_id );
		return $attempt->delete();

	}

	/**
	 * Retrieve quiz data for a student and optionally filter by quiz_id(s)
	 * @param    mixed   $quiz    WP Post ID / Array of WP Post IDs
	 * @return   object           Instance of LLMS_Query_Quiz_Attempt
	 * @since    3.9.0
	 * @version  3.16.11
	 */
	public function get_all( $quiz = array() ) {

		$query = new LLMS_Query_Quiz_Attempt( array(
			'quiz_id' => $quiz,
			'per_page' => 5000,
		) );

		return apply_filters( 'llms_student_get_quiz_data', $query->get_attempts(), $quiz );

	}

	/**
	 * Retrieve quiz attempts
	 * @param    int     $quiz_id  WP Post ID of the quiz
	 * @param    array   $args     additional args to pass to LLMS_Query_Quiz_Attempt
	 * @return   array             array of LLMS_Quiz_Attempts
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_attempts_by_quiz( $quiz_id, $args = array() ) {

		$args = wp_parse_args( array(
			'student_id' => $this->get_id(),
			'quiz_id' => $quiz_id,
		), $args );

		$query = new LLMS_Query_Quiz_Attempt( $args );

		if ( $query->has_results() ) {
			return $query->get_attempts();
		}

		return array();

	}

	/**
	 * Retrieve an attempt by attempt id
	 * @param    int     $attempt_id  Attempt ID
	 * @return   obj
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_attempt_by_id( $attempt_id ) {
		return new LLMS_Quiz_Attempt( $attempt_id );
	}

	/**
	 * Decodes an attempt string and returns the associated attempt
	 * @param    string     $attempt_key  encoded attempt key
	 * @return   obj|false
	 * @since    3.9.0
	 * @version  3.16.0
	 */
	public function get_attempt_by_key( $attempt_key ) {

		$id = $this->parse_attempt_key( $attempt_key );
		if ( ! $id ) {
			return false;
		}
		return $this->get_attempt_by_id( $id );

	}

	/**
	 * Get the # of attempts remaining by a student for a given quiz
	 * @param    int     $quiz_id  WP Post ID of the Quiz
	 * @return   mixed
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_attempts_remaining_for_quiz( $quiz_id ) {

		$quiz = llms_get_post( $quiz_id );

		$ret = _x( 'Unlimited', 'quiz attempts remaining', 'lifterlms' );

		if ( $quiz->has_attempt_limit() ) {

			$allowed = $quiz->get( 'allowed_attempts' );
			$used = $this->count_attempts_by_quiz( $quiz->get( 'id' ) );

			// ensure undefined, null, '', etc.. show as an int
			if ( ! $allowed ) {
				$allowed = 0;
			}

			$remaining = ( $allowed - $used );

			// don't show negative attmepts
			$ret = max( 0, $remaining );

		}

		return apply_filters( 'llms_student_quiz_attempts_remaining_for_quiz', $ret, $quiz, $this );

	}

	/**
	 * Get all the attempts for a given quiz/lesson from an attempt key
	 * @param    string     $attempt_key  an encoded attempt key
	 * @return   false|array
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_sibling_attempts_by_key( $attempt_key ) {

		$id = $this->parse_attempt_key( $attempt_key );
		if ( ! $id ) {
			return false;
		}

	}

	/**
	 * Get the quiz attempt with the highest grade for a given quiz and lesson combination
	 * @param    int     $quiz_id    WP Post ID of a Quiz
	 * @param    null    $deprecated deprecated
	 * @return   false|array
	 * @since    3.9.0
	 * @version  3.16.0
	 */
	public function get_best_attempt( $quiz_id = null, $deprecated = null ) {

		$attempts = $this->get_attempts_by_quiz( $quiz_id, array(
			'per_page' => 1,
			'sort' => array(
				'grade' => 'DESC',
				'update_date' => 'DESC',
				'id' => 'DESC',
			),
			'status' => array( 'pass', 'fail' ),
		) );

		if ( $attempts ) {
			return $attempts[0];
		}

		return false;

	}

	/**
	 * Retrieve the last recorded attempt for a student for a given quiz/lesson
	 * "Last" is defined as the attempt with the highest attempt number
	 * @param    int     $quiz_id    WP Post ID of the quiz
	 * @return   obj|false
	 * @since    3.9.0
	 * @version  3.16.0
	 */
	public function get_last_attempt( $quiz_id ) {

		$attempts = $this->get_attempts_by_quiz( $quiz_id, array(
			'per_page' => 1,
			'sort' => array(
				'attempt' => 'DESC',
			),
		) );

		if ( $attempts ) {
			return $attempts[0];
		}

		return false;

	}

	/**
	 * Get the last completed attempt for a given quiz or quiz/lesson combination
	 * @param    int     $quiz    WP Post ID of a Quiz
	 * @param    int     $lesson  WP Post ID of a Lesson
	 * @return   false|obj
	 * @since    3.9.0
	 * @version  3.16.0
	 */
	public function get_last_completed_attempt( $quiz_id = null, $deprecated = null ) {

		$query = new LLMS_Query_Quiz_Attempt( array(
			'student_id' => $this->get_id(),
			'quiz_id' => $quiz_id,
			'per_page' => 1,
			'status_exclude' => array( 'incomplete' ),
			'sort' => array(
				'end_date' => 'DESC',
				'id' => 'DESC',
			),
		) );

		if ( $query->has_results() ) {
			return $query->get_attempts()[0];
		}

		return false;
	}

	/**
	 * Parse an attempt key into it's parts
	 * @param    string     $attempt_key  an encoded attempt key
	 * @return   array|false
	 * @since    3.9.0
	 * @version  3.16.7
	 */
	private function parse_attempt_key( $attempt_key ) {

		return LLMS_Hasher::unhash( $attempt_key );

	}

}
