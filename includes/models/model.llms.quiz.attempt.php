<?php
/**
 * Quiz Attempt Model
 * @since   3.9.0
 * @version [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Quiz_Attempt extends LLMS_Abstract_Database_Store {

	/**
	 * Array of table column name => format
	 * @var  array
	 */
	protected $columns = array(
		'student_id' => '%d',
		'quiz_id' => '%d',
		'lesson_id' => '%d',
		'start_date' => '%s',
		'update_date' => '%s',
		'end_date' => '%s',
		'current' => '%d',
		'attempt' => '%d',
		'grade' => '%f',
		'passed' => '%d',
		'questions' => '%s',
	);

	protected $date_created = 'start_date';
	protected $date_updated = 'update_date';

	/**
	 * Database Table Name
	 * @var  string
	 */
	protected $table = 'quiz_attempts';

	/**
	 * Constructor
	 * @param    mixed      $item  array/obj of attempt data or int
	 * @since    3.9.0
	 * @version  [version]
	 */
	public function __construct( $item = null ) {

		if ( is_numeric( $item ) ) {

			$this->id = $item;

		} elseif ( is_object( $item ) && isset( $item->id ) ) {

			$this->id = $item->id;

		} elseif ( is_array( $item ) && isset( $item['id'] ) )  {

			$this->id = $item['id'];

		}

		if ( ! $this->id ) {

			if ( is_array( $item ) || is_object( $item ) ) {
				$this->setup( $item );
			}

			parent::__construct();

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
	 * @version  [version]
	 */
	public function answer_question( $question_id, $answer ) {

		$questions = $this->get_questions();

		foreach ( $questions as $key => $data ) {
			if ( $question_id != $data['id'] ) {
				continue;
			}
			$question = llms_get_post( $question_id );
			$questions[ $key ]['answer'] = $answer;
			$questions[ $key ]['correct'] = $question->grade( $answer );
			break;
		}

		$this->set_questions( $questions )->save();

		llms_log( $this->get_questions() );

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
	 * @version  [version]
	 */
	private function calculate_point_weight() {
		$available = $this->get_count( 'available_points' );
		return ( $available > 0 ) ? ( 100 / $available ) : 0;
	}

	/**
	 * End a quiz attempt
	 * Sets end date, unsets the quiz as the current quiz, and records a grade
	 * @param    boolean   $silent   if true, will not trigger actions or mark related lesson as complete
	 * @return   $this
	 * @since    3.9.0
	 * @version  [version]
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
				if ( ! llms_is_complete( $this->get( 'student_id' ), $this->get( 'lesson_id' ), 'lesson' ) ) {
					llms_mark_complete( $this->get( 'student_id' ), $this->get( 'lesson_id' ), 'lesson', 'quiz_' . $this->get( 'quiz_id' ) );
				}
			}
		}

		// clear "cached" grade so it's recalced next time it's requested
		$this->get_student()->set( 'overall_grade', '' );

		return $this;

	}

	/**
	 * Retrieve a count for various pieces of information related to the attempt
	 * @param    string     $key  data to count
	 * @return   int
	 * @since    3.9.0
	 * @version  [version]
	 */
	public function get_count( $key ) {

		$count = 0;
		$questions = $this->get_questions();

		switch ( $key ) {

			case 'available_points':
			case 'correct_answers':
			case 'points':
				foreach ( $questions as $data ) {
					// get the total number of correct answers
					if ( 'correct_answers' === $key ) {
						if  ( 'yes' === $data['correct'] ) {
							$count++;
						}
					// get the total number of earned points
					} elseif ( 'points' === $key ) {
						if ( 'yes' === $data['correct'] ) {
							$count += $data['points'];
						}
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
	 * Retrieve the first question for the attempt
	 * @return   int|false
	 * @since    3.9.0
	 * @version  [version]
	 */
	public function get_first_question() {

		$questions = $this->get_questions();
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
	 * @version  [version]
	 */
	public function get_question_order( $question_id ) {

		foreach ( $this->get_questions() as $order => $data ) {

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
	 * @version  [version]
	 */
	public function get_key() {
		$hashids = new Hashids\Hashids( 'OwxbRhk6uyGb08wggj7K648Tdmsd4FDW' );
		return $hashids->encode( $this->get_id() );
		// return base64_encode( implode( '|', array(
		// 	$this->get( 'quiz_id' ),
		// 	$this->get( 'lesson_id' ),
		// 	$this->get( 'attempt' ),
		// ) ) );
	}

	/**
	 * Retrieve an array of blank questions for insertion into a new attempt during initialization
	 * @return   array
	 * @since    3.9.0
	 * @version  [version]
	 */
	private function get_new_questions() {

		$quiz = llms_get_post( $this->get( 'quiz_id' ) );

		$questions = array();

		if ( $quiz ) {

			foreach ( $quiz->get_questions() as $question ) {
				$questions[] = array(
					'id' => $question->get( 'id' ),
					'points' => $question->supports( 'points' ) ? $question->get( 'points' ) : 0,
					'answer' => null,
					'correct' => null,
				);
			}

			if ( 'yes' === $quiz->get( 'random_questions' ) ) {
				shuffle( $questions );
			}

		}

		return $questions;

	}

	/**
	 * Retrieve the next unanswered question in the attempt
	 * @return   int|false
	 * @since    3.9.0
	 * @version  [version]
	 */
	public function get_next_question( $last_question = null ) {

		$next = false;

		foreach ( $this->get_questions() as $question ) {

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
	 * @version  [version]
	 */
	public function get_permalink() {
		return add_query_arg( 'attempt_key', $this->get_key(), get_permalink( $this->get_quiz()->get( 'id' ) ) );
	}

	/**
	 * Get array of serialized questions
	 * @param    boolean    $cache  if true, save data to to the object for future gets
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_questions( $cache = true ) {

		$questions = $this->get( 'questions', $cache );
		if ( $questions ) {
			return unserialize( $questions );
		}
		return array();

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
	 * @version  [version]
	 */
	public static function init( $quiz_id, $lesson_id, $student = null ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			throw new Exception( __( 'You must be logged in to take a quiz!', 'lifterlms' ) );
		}

		// initialized attempt already exists for this quiz/lesson
		// $current = $student->quizzes()->get_current_attempt( $quiz_id );
		// if ( $current && $lesson_id == $current->get( 'lesson_id' ) ) {
		// 	return $current;
		// }

		// initialize a new attempt
		$attempt = new self();
		$attempt->set( 'quiz_id', $quiz_id );
		$attempt->set( 'lesson_id', $lesson_id );
		$attempt->set( 'student_id', $student->get_id() );
		$attempt->set_questions( $attempt->get_new_questions() );
		$attempt->set( 'current', true );

		$last_attempt = $student->quizzes()->get_last_attempt( $quiz_id );
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
	 * Setter for serialized questions array
	 * @param    array      $questions  question data
	 * @param    boolean    $save       if true, immediately persists to database
	 * @since    [version]
	 * @version  [version]
	 */
	public function set_questions( $questions = array(), $save = false ) {
		return $this->set( 'questions', serialize( $questions ), $save );
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
