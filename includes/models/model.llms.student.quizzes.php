<?php
/**
 * Student Quiz Data
 * Rather than instatiating this class directly
 * use LLMS_Student->quizzes()
 * @since   3.9.0
 * @version 3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Student_Quizzes extends LLMS_Abstract_User_Data {

	/**
	 * Remove Student Quiz attempt(s)
	 * @param    int     $quiz_id    WP Post ID of a Quiz
	 * @param    int     $lesson_id  WP Post ID of a lesson
	 * @param    int     $attempt    attempt number
	 * @return   array               updated array quiz data for the student
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function delete_attempt( $quiz_id, $lesson_id, $attempt ) {

		$quizzes = $this->get_all();

		$id = $this->get_attempt_index( $quiz_id, $lesson_id, $attempt );

		if ( false !== $id ) {
			unset( $quizzes[ $id ] );
		}

		// reindex
		$quizzes = array_values( $quizzes );

		// save
		$this->save( $quizzes );

		// return updated quiz data
		return $quizzes;

	}

	/**
	 * Retrieve quiz data for a student for a lesson / quiz combination
	 * @param    int     $quiz    WP Post ID of a Quiz
	 * @param    int     $lesson  WP Post ID of a lesson
	 * @return   array
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_all( $quiz = null, $lesson = null ) {

		// get all quiz data
		$quizzes = $this->get( 'quiz_data' );

		if ( ! is_array( $quizzes ) ) {
			$quizzes = array();
		}

		// reduce the data to those matching the requested quiz & lesson
		if ( $quizzes && ( $quiz || $lesson ) ) {

			foreach ( $quizzes as $i => $data ) {

				if ( $quiz && $quiz != $data['id'] ) {
					unset( $quizzes[ $i ] );
				}

				if ( $lesson && $lesson != $data['assoc_lesson'] ) {
					unset( $quizzes[ $i ] );
				}
			}

			// reindex
			$quizzes = array_values( $quizzes );

		}

		return apply_filters( 'llms_student_get_quiz_data', $quizzes, $quiz, $lesson );

	}

	/**
	 * Retrieve the data for a single attempt by quiz, lesson, and attempt number
	 * @param    int     $quiz_id    WP Post ID of a Quiz
	 * @param    int     $lesson_id  WP Post ID of a lesson
	 * @param    int     $attempt    attempt number
	 * @return   false|obj
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_attempt( $quiz, $lesson, $attempt ) {

		$id = $this->get_attempt_index( $quiz, $lesson, $attempt );
		if ( false !== $id ) {
			$quizzes = $this->get_all();
			return new LLMS_Quiz_Attempt( $quizzes[ $id ] );
		}

		return false;

	}

	/**
	 * Decodes an attempt string and returns the associated attempt
	 * @param    string     $attempt_key  encoded attempt key
	 * @return   obj|false
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_attempt_by_key( $attempt_key ) {

		$parsed = $this->parse_attempt_key( $attempt_key );
		if ( ! $parsed ) {
			return false;
		}
		return $this->get_attempt( $parsed['quiz_id'], $parsed['lesson_id'], $parsed['attempt'] );

	}

	/**
	 * Get all the attempts for a given quiz/lesson from an attempt key
	 * @param    string     $attempt_key  an encoded attempt key
	 * @return   false|array
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_sibling_attempts_by_key( $attempt_key ) {

		$parsed = $this->parse_attempt_key( $attempt_key );
		if ( ! $parsed ) {
			return false;
		}
		return $this->get_all( $parsed['quiz_id'], $parsed['lesson_id'] );

	}

	/**
	 * Retrieve the index of a single attempt by quiz, lesson, and attempt number
	 * The index is the the attempt in the raw array of quiz data without any filtering
	 * @param    int     $quiz_id    WP Post ID of a Quiz
	 * @param    int     $lesson_id  WP Post ID of a lesson
	 * @param    int     $attempt    attempt number
	 * @return   int|false
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	private function get_attempt_index( $quiz, $lesson, $attempt ) {

		// get all quiz data
		$quizzes = $this->get_all();

		foreach ( $quizzes as $i => $data ) {
			if ( $quiz == $data['id'] && $lesson == $data['assoc_lesson'] && $attempt == $data['attempt'] ) {
				return $i;
			}
		}

		return false;

	}

	/**
	 * Get the quiz attempt with the highest grade for a given quiz and lesson combination
	 * @param    int     $quiz_id    WP Post ID of a Quiz
	 * @param    int     $lesson_id  WP Post ID of a lesson
	 * @return   false|array
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_best_attempt( $quiz = null, $lesson = null ) {

		$attempts = $this->get_all( $quiz, $lesson );

		if ( $attempts ) {

			$best = false;

			foreach ( $attempts as $attempt ) {

				if ( empty( $best['grade'] ) || $attempt['grade'] >= $best['grade'] ) {
					$best = $attempt;
				}
			}

			return $best;

		}

		return false;

	}

	/**
	 * Retrieve the currently initialized quiz attempt that hasn't been started yet
	 * Replaces quiz data stored in the session
	 * @param    int        $quiz    WP_Post ID of the quiz to retrieve the current attempt for
	 * @return   false|obj
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_current_attempt( $quiz ) {

		foreach ( array_reverse( $this->get_all( $quiz ) ) as $attempt ) {
			if ( isset( $attempt['current'] ) && true === $attempt['current'] ) {
				return new LLMS_Quiz_Attempt( $attempt );
			}
		}

		return false;

	}

	/**
	 * Retrieve the last recorded attempt for a student for a given quiz/lesson
	 * "Last" is defined as the attempt with the highest attempt number
	 * @param    int     $quiz    WP Post ID of the quiz
	 * @param    int     $lesson  WP Post ID of the lesson
	 * @return   obj|false
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_last_attempt( $quiz, $lesson ) {

		$attempts = $this->get_all( $quiz, $lesson );

		if ( $attempts ) {
			$numbers = wp_list_pluck( $attempts, 'attempt' );
			$last = max( $numbers );
			return $this->get_attempt( $quiz, $lesson, $last );
		}

		return false;

	}

	/**
	 * Get the last completed attempt for a given quiz or quiz/lesson combination
	 * @param    int     $quiz    WP Post ID of a Quiz
	 * @param    int     $lesson  WP Post ID of a Lesson
	 * @return   false|obj
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_last_completed_attempt( $quiz = null, $lesson = null ) {
		$attempts = $this->get_all( $quiz, $lesson );
		if ( $attempts ) {
			$attempts = array_reverse( $attempts );
			foreach ( $attempts as $attempt ) {
				$attempt = new LLMS_Quiz_Attempt( $attempt );
				if ( 'complete' === $attempt->get_status() ) {
					return $attempt;
				}
			}
		}
		return false;
	}

	/**
	 * Parse an attempt key into it's parts
	 * @param    string     $attempt_key  an encoded attempt key
	 * @return   array|false
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	private function parse_attempt_key( $attempt_key ) {

		$parsed = explode( '|', base64_decode( $attempt_key ) );

		if ( 3 !== count( $parsed ) ) {
			return false;
		}

		return array(
			'attempt' => $parsed[2],
			'lesson_id' => $parsed[1],
			'quiz_id' => $parsed[0],
		);

	}

	/**
	 * Save quiz data updates
	 * If updating a single attempt the data MUST be merged back into the raw data array otherwise data will be lost
	 * @param    array     $quizzes   quiz data array
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	private function save( $quizzes ) {
		return apply_filters( 'llms_student_quizzes_save', $this->set( 'quiz_data', $quizzes ), $this );
	}

	/**
	 * Save a single quiz attempt
	 * Handles dupchecking
	 * if the attempt already exists it will be handled as an update
	 * if the attempt is new it will be appended to the list of quizzes
	 * @param    array     $attempt_data   raw attempt data array
	 * @return   void
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function save_attempt( $attempt_data ) {
		$quizzes = $this->get_all();
		$id = $this->get_attempt_index( $attempt_data['id'], $attempt_data['assoc_lesson'], $attempt_data['attempt'] );
		if ( false === $id ) {
			$quizzes[] = $attempt_data;
		} else {
			$quizzes[ $id ] = $attempt_data;
		}
		return $this->save( $quizzes );
	}

}
