<?php
/**
 * Quiz Attempt Model
 * @since   3.9.0
 * @version 3.14.9
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Quiz_Attempt {

	private $data = array();

	/**
	 * Constructor
	 * @param    array      $data  raw array of quiz data
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function __construct( $data = array() ) {

		$data = wp_parse_args( $data, $this->get_new_data() );
		foreach ( $data as $key => $val ) {
			$this->set( $key, $val );
		}

	}

	/**
	 * Answer a question
	 * records the selected option and whether or not the selected option was the correct option
	 * Automatically updates & saves the attempt to the dabatase
	 * @param    int     $question_id  WP_Post ID of the LLMS_Question
	 * @param    int     $answer       index/key of the selected answer option
	 *                                 as found in the array of options retrieved by LLMS_Question->get_options()
	 * @return   $this
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function answer_question( $question_id, $answer ) {

		$questions = $this->get( 'questions' );
		foreach ( $questions as $key => $data ) {
			if ( $question_id != $data['id'] ) {
				continue;
			}
			$question = llms_get_post( $question_id );
			$questions[ $key ]['answer'] = $answer;
			$questions[ $key ]['correct'] = ( $question->get_correct_option_key() == $answer );
			break;
		}

		$this->set( 'questions', $questions )->save();

		return $this;

	}

	/**
	 * Calculate and the grade for a completed quiz
	 * @return   $this      for chaining
	 * @since    3.9.0
	 * @version  3.9.2
	 */
	private function calculate_grade() {

		$grade = round( $this->get_count( 'points' ) * $this->calculate_point_weight(), 2 );
		$quiz = $this->get_quiz();
		$min_grade = $quiz ? $quiz->get_passing_percent() : 100;

		$this->set( 'grade', $grade );
		$this->set( 'passed', ( $min_grade <= $grade ) );

		return $this;

	}

	/**
	 * Calculate the weight of each point
	 * @return   float
	 * @since    3.9.2
	 * @version  3.9.2
	 */
	private function calculate_point_weight() {
		return ( 100 / $this->get_count( 'available_points' ) );
	}

	/**
	 * End a quiz attempt
	 * Sets end date, unsets the quiz as the current quiz, and records a grade
	 * @param    boolean   $silent   if true, will not trigger actions or mark related lesson as complete
	 * @return   $this
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function end( $silent = false ) {

		$this->set( 'end_date', current_time( 'mysql' ) );
		$this->set( 'current', false );
		$this->calculate_grade()->save();

		if ( ! $silent ) {

			// do quiz completion actions
			do_action( 'lifterlms_quiz_completed', $this->get_student()->get_id(), $this->get( 'quiz_id' ), $this );
			if ( $this->get( 'passed' ) ) {
				$passed = true;
				do_action( 'lifterlms_quiz_passed', $this->get_student()->get_id(), $this->get( 'quiz_id' ), $this );
			} else {
				$passed = false;
				do_action( 'lifterlms_quiz_failed', $this->get_student()->get_id(), $this->get( 'quiz_id' ), $this );
			}

			// mark lesson complete
			$lesson = llms_get_post( $this->get( 'lesson_id' ) );
			$passing_required = ( 'yes' === $lesson->get( 'require_passing_grade' ) );
			if ( ! $passing_required || ( $passing_required && $passed ) ) {
				// mark associated lesson complete only if it hasn't been completed before
				if ( ! llms_is_complete( $this->get( 'user_id' ), $this->get( 'lesson_id' ), 'lesson' ) ) {
					llms_mark_complete( $this->get( 'user_id' ), $this->get( 'lesson_id' ), 'lesson', 'quiz_' . $this->get( 'quiz_id' ) );
				}
			}
		}

		// clear "cached" grade so it's recalced next time it's requested
		$this->get_student()->set( 'overall_grade', '' );

		return $this;

	}

	/**
	 * Get the value of a field
	 * @param    string     $key  name of the field
	 * @return   mixed
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get( $key ) {
		$key = $this->get_field_alias( $key );
		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}
		return null;
	}

	/**
	 * Retrieve a count for various pieces of information related to the attempt
	 * @param    string     $key  data to count
	 * @return   int
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_count( $key ) {

		$count = 0;
		$questions = $this->get( 'questions' );

		switch ( $key ) {

			case 'available_points':
			case 'correct_answers':
			case 'points':
				foreach ( $questions as $data ) {
					// get the total number of correct answers
					if ( 'correct_answers' === $key && $data['correct'] ) {
						$count++;
						// get the total number of earned points
					} elseif ( 'points' === $key && $data['correct'] ) {
						$count += $data['points'];
						// get the total number of possible points
					} elseif ( 'available_points' === $key ) {
						$count += $data['points'];
					}
				}
			break;

			case 'questions':
				return count( $questions );
			break;

		}

		return $count;

	}

	/**
	 * Retrieve a formatted date
	 * @param    string     $key     start or end
	 * @param    string     $format  output date format (PHP), uses wordpress format options if none provided
	 * @return   string
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_date( $key, $format = null ) {

		$date = strtotime( $this->get( $key . '_date' ) );
		$format = ! $format ? get_option( 'date_format' ) : $format;
		return date_i18n( $format, $date );

	}

	/**
	 * Normalize field keys
	 * Futurproofing for more consistent data structures
	 * @param    string     $key  key name
	 * @return   string
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	private function get_field_alias( $key ) {
		$aliases = array(
			'quiz_id' => 'id',
			'lesson_id' => 'assoc_lesson',
			'student_id' => 'user_id',
		);
		if ( isset( $aliases[ $key ] ) ) {
			return $aliases[ $key ];
		}
		return $key;
	}

	/**
	 * Retrieve the first question for the attempt
	 * @return   int|false
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_first_question() {

		$questions = $this->get( 'questions' );
		if ( $questions ) {
			$first = array_shift( $questions );
			return $first['id'];
		}

		return false;

	}

	/**
	 * Get the numeric order of a question in a given quiz
	 * @param    int     $question_id  WP Post ID of the LLMS_Question
	 * @return   int
	 * @since    3.9.2
	 * @version  3.9.2
	 */
	public function get_question_order( $question_id ) {

		foreach ( $this->get( 'questions' ) as $order => $data ) {

			if ( $data['id'] == $question_id ) {
				return $order + 1;
			}
		}

		return 0;

	}

	/**
	 * Get an encoded attempt key that can be passed in URLs and the like
	 * @return   string
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_key() {
		return base64_encode( implode( '|', array(
			$this->get( 'quiz_id' ),
			$this->get( 'lesson_id' ),
			$this->get( 'attempt' ),
		) ) );
	}

	/**
	 * Retrieve a blank array of default data for a new quiz attempt
	 * @return   array
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	private function get_new_data() {

		$data = array(
			'attempt' => 1,
			'assoc_lesson' => null,
			'current' => null, // there can only be one...
			'end_date' => null,
			'grade' => null,
			'id' => null,
			'passed' => null,
			'questions' => array(),
			'start_date' => null,
			'user_id' => null,
		);

		return apply_filters( 'llms_quiz_attempt_get_new_data', $data, $this );
	}

	/**
	 * Retrieve an array of blank questions for insertion into a new attempt during initialization
	 * @param    int     $quiz_id  WP Post ID of the quiz
	 * @return   array
	 * @since    3.9.0
	 * @version  3.12.0
	 */
	private function get_new_questions( $quiz_id ) {

		$qquiz = new LLMS_QQuiz( $quiz_id );
		$questions = array();
		foreach ( $qquiz->get_questions( 'ids' ) as $qid ) {
			$questions[] = array(
				'id' => absint( $qid ),
				'points' => $qquiz->get_question_value( $qid ),
				'answer' => null,
				'correct' => null,
			);
		}

		if ( 'yes' === $qquiz->get( 'random_questions' ) ) {
			shuffle( $questions );
		}

		return $questions;

	}

	/**
	 * Retrieve the next unanswered question in the attempt
	 * @return   int|false
	 * @since    3.9.0
	 * @version  3.14.9
	 */
	public function get_next_question( $last_question = null ) {

		$next = false;

		foreach ( $this->get( 'questions' ) as $question ) {

			if ( $next || is_null( $question['answer'] ) ) {
				return $question['id'];

				// when rewinding and moving back through we don't want to skip questions
			} elseif ( $last_question && $last_question == $question['id'] ) {
				$next = true;
			}
		}

		return false;

	}

	/**
	 * Retrieve a permalink for the attempt
	 * @return   string
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_permalink() {
		return add_query_arg( 'attempt_key', $this->get_key(), get_permalink( $this->get_quiz()->get_id() ) );
	}

	/**
	 * Get an instance of the LLMS_Quiz for the attempt
	 * @return   obj
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_quiz() {
		return llms_get_post( $this->get( 'quiz_id' ) );
	}

	/**
	 * Get the attempts status based on start and end dates
	 * @return   string
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_status() {

		$start = $this->get( 'start_date' );
		$end = $this->get( 'end_date' );

		// quiz has been initialized but hasn't been started yet
		if ( ! $start && ! $end ) {

			return 'new';

		} elseif ( $start && ! $end ) {

			if ( true == $this->get( 'current' ) ) {

				return 'in-progress';

			}

			return 'incomplete';

		}

		return 'complete';

	}

	/**
	 * Get an LLMS_Student for the quiz
	 * @return   obj
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_student() {
		return llms_get_student( $this->get( 'student_id' ) );
	}

	/**
	 * Get the time spent on the quiz from start to end
	 * @param    integer    $precision  precision passed to llms_get_date_diff
	 * @return   string
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function get_time( $precision = 2 ) {
		return llms_get_date_diff( $this->get_date( 'start', 'U' ), $this->get_date( 'end', 'U' ), $precision );
	}

	/**
	 * Initialize a new quiz attempt by quiz and lesson for a user
	 * if no user is passed the current user will be used
	 * if no user found returns a WP_Error
	 * @param    int       $quiz_id    WP Post ID of the quiz
	 * @param    int       $lesson_id  WP Post ID of the lesson
	 * @param    mixed     $student    accepts anything that can be passed to llms_get_student
	 * @return   obj                   $this (for chaining)
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public static function init( $quiz_id, $lesson_id, $student = null ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			throw new Exception( __( 'You must be logged in to take a quiz!', 'lifterlms' ) );
		}

		// initialized attempt already exists for this quiz/lesson
		$current = $student->quizzes()->get_current_attempt( $quiz_id );
		if ( $current && $lesson_id == $current->get( 'lesson_id' ) ) {
			return $current;
		}

		// initialize a new attempt
		$attempt = new self();
		$attempt->set( 'quiz_id', $quiz_id );
		$attempt->set( 'lesson_id', $lesson_id );
		$attempt->set( 'student_id', $student->get_id() );
		$attempt->set( 'questions', $attempt->get_new_questions( $quiz_id ) );
		$attempt->set( 'current', true );

		$last_attempt = $student->quizzes()->get_last_attempt( $quiz_id, $lesson_id );
		if ( $last_attempt ) {
			$attempt->set( 'attempt', absint( $last_attempt->get( 'attempt' ) ) + 1 );
		}

		return $attempt;

	}

	/**
	 * Determine if the attempt was passing
	 * @return   boolean
	 * @since    3.9.2
	 * @version  3.9.2
	 */
	public function is_passing() {
		return $this->get( 'passed' );
	}

	/**
	 * Translate attempt related strings
	 * @param    string     $key  key to translate
	 * @return   string
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function l10n( $key ) {

		switch ( $key ) {
			case 'passed':
				return $this->get( 'passed' ) ? __( 'Passed', 'lifterlms' ) : __( 'Failed', 'lifterlms' );
			break;
		}

		return '';

	}

	/**
	 * Save the current state of the attempt to the database
	 * @return   obj          $this for chaining
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function save() {
		$this->get_student()->quizzes()->save_attempt( $this->to_array() );
		return $this;

	}

	/**
	 * Set the value of a field
	 * @param    string     $key   field key
	 * @param    mixed      $val   field value
	 * @return   obj               $this for chaining
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function set( $key, $val ) {
		$this->data[ $this->get_field_alias( $key ) ] = $val;
		return $this;
	}

	/**
	 * Record the attempt as started
	 * @return   obj             $this for chaining
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function start() {

		$this->set( 'start_date', current_time( 'mysql' ) );
		$this->save();
		return $this;

	}

	/**
	 * Retrieve the private data array
	 * @return   array
	 * @since    3.9.0
	 * @version  3.9.0
	 */
	public function to_array() {
		return $this->data;
	}

}
