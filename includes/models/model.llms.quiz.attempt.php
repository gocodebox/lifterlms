<?php
/**
 * Quiz Attempt Model
 *
 * @package LifterLMS/Models
 * @since   3.9.0
 * @version 3.26.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Quiz_Attempt model.
 */
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
		'status' => '%s',
		'attempt' => '%d',
		'grade' => '%f',
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
	 * @version  3.16.0
	 */
	public function __construct( $item = null ) {

		if ( is_numeric( $item ) ) {

			$this->id = $item;

		} elseif ( is_object( $item ) && isset( $item->id ) ) {

			$this->id = $item->id;

		} elseif ( is_array( $item ) && isset( $item['id'] ) ) {

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
	 * @version  3.16.0
	 */
	public function answer_question( $question_id, $answer ) {

		$questions = $this->get_questions();

		foreach ( $questions as $key => $data ) {
			if ( $question_id != $data['id'] ) {
				continue;
			}
			$question = llms_get_post( $question_id );
			$graded = $question->grade( $answer );
			$questions[ $key ]['answer'] = $answer;
			$questions[ $key ]['correct'] = $graded;
			if ( llms_parse_bool( $graded ) ) {
				$questions[ $key ]['earned'] = $questions[ $key ]['points'];
			}
			break;
		}

		$this->set_questions( $questions )->save();

		return $this;

	}

	/**
	 * Calculate and the grade for a completed quiz
	 * @return   $this      for chaining
	 * @since    3.9.0
	 * @version  3.24.0
	 */
	public function calculate_grade() {

		$status = 'pending';

		if ( $this->is_auto_gradeable() ) {

			$grade = LLMS()->grades()->round( $this->get_count( 'earned' ) * $this->calculate_point_weight() );

			$quiz = $this->get_quiz();
			$min_grade = $quiz ? $quiz->get_passing_percent() : 100;

			$this->set( 'grade', $grade );
			$status = ( $min_grade <= $grade ) ? 'pass' : 'fail';

		}

		$this->set_status( $status );

		return $this;

	}

	/**
	 * Calculate the weight of each point
	 * @return   float
	 * @since    3.9.2
	 * @version  3.16.0
	 */
	private function calculate_point_weight() {
		$available = $this->get_count( 'available_points' );
		return ( $available > 0 ) ? ( 100 / $available ) : 0;
	}

	/**
	 * Run actions designating quiz completion
	 * @return   void
	 * @since    3.16.0
	 * @version  3.17.1
	 */
	public function do_completion_actions() {

		// do quiz completion actions
		do_action( 'lifterlms_quiz_completed', $this->get_student()->get_id(), $this->get( 'quiz_id' ), $this );

		$passed = false;

		switch ( $this->get( 'status' ) ) {

			case 'pass':
				$passed = true;
				do_action( 'lifterlms_quiz_passed', $this->get_student()->get_id(), $this->get( 'quiz_id' ), $this );
			break;

			case 'fail':
				do_action( 'lifterlms_quiz_failed', $this->get_student()->get_id(), $this->get( 'quiz_id' ), $this );
			break;

			case 'pending':
				do_action( 'lifterlms_quiz_pending', $this->get_student()->get_id(), $this->get( 'quiz_id' ), $this );
			break;

		}

	}

	/**
	 * End a quiz attempt
	 * Sets end date, unsets the quiz as the current quiz, and records a grade
	 * @param    boolean   $silent   if true, will not trigger actions or mark related lesson as complete
	 * @return   $this
	 * @since    3.9.0
	 * @version  3.16.0
	 */
	public function end( $silent = false ) {

		$this->set( 'end_date', current_time( 'mysql' ) );
		$this->calculate_grade()->save();

		if ( ! $silent ) {

			$this->do_completion_actions();

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
	 * @version  3.19.2
	 */
	public function get_count( $key ) {

		$count = 0;
		$questions = $this->get_questions();

		switch ( $key ) {

			case 'available_points':
			case 'correct_answers':
			case 'earned':
			case 'gradeable_questions': // like "questions" but excludes content questions
			case 'points': // legacy version of earned
				foreach ( $questions as $data ) {
					// get the total number of correct answers
					if ( 'correct_answers' === $key ) {
						if ( 'yes' === $data['correct'] ) {
							$count++;
						}
					} elseif ( 'earned' === $key || 'points' === $key ) {
						$count += $data['earned'];
						// get the total number of possible points
					} elseif ( 'available_points' === $key ) {
						$count += $data['points'];
					} elseif ( 'gradeable_questions' === $key ) {
						if ( $data['points'] ) {
							$count++;
						}
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
	 * @version  3.16.0
	 */
	public function get_date( $key, $format = null ) {

		$date = strtotime( $this->get( $key . '_date' ) );
		$format = ! $format ? get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) : $format;
		return date_i18n( $format, $date );

	}

	/**
	 * Retrieve the first question for the attempt
	 * @return   int|false
	 * @since    3.9.0
	 * @version  3.16.0
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
	 * @version  3.16.0
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
	 * @version  3.16.7
	 */
	public function get_key() {
		return LLMS_Hasher::hash( $this->get( 'id' ) );
	}

	/**
	 * Retrieve an array of blank questions for insertion into a new attempt during initialization
	 * @return   array
	 * @since    3.9.0
	 * @version  3.16.0
	 */
	private function get_new_questions() {

		$quiz = llms_get_post( $this->get( 'quiz_id' ) );

		$questions = array();

		if ( $quiz ) {

			$randomize = llms_parse_bool( $quiz->get( 'random_questions' ) );

			// array of indexes that will be locked during shuffling
			$locks = array();

			foreach ( $quiz->get_questions() as $index => $question ) {

				// if randomization is enabled, store the questions index so we can lock it during randomization
				if ( $randomize && $question->supports( 'random_lock' ) ) {
					$locks[] = $index;
				}

				$questions[] = array(
					'id' => $question->get( 'id' ),
					'earned' => 0,
					'points' => $question->supports( 'points' ) ? $question->get( 'points' ) : 0,
					'answer' => null,
					'correct' => null,
				);

			}

			if ( $randomize ) {

				// lifted from https://stackoverflow.com/a/28491007/400568
				// i generally comprehend this code but also in a truer way i have no idea...
				$inc = array();
				$i = 0;
				$j = 0;
				$l = count( $questions );
				$le = count( $locks );
				while ( $i < $l ) {
					if ( $j >= $le || $i < $locks[ $j ] ) {
						$inc[] = $i;
					} else {
						$j++;
					}
					$i++;
				}

				// fisher-yates-knuth shuffle variation O(n)
				$num = count( $inc );
				while ( $num-- ) {
					$perm = rand( 0, $num );
					$swap = $questions[ $inc[ $num ] ];
					$questions[ $inc[ $num ] ] = $questions[ $inc[ $perm ] ];
					$questions[ $inc[ $perm ] ] = $swap;
				}
			}
		}// End if().

		return $questions;

	}

	/**
	 * Retrieve the next unanswered question in the attempt
	 * @return   int|false
	 * @since    3.9.0
	 * @version  3.16.0
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
	 * @version  3.16.0
	 */
	public function get_permalink() {
		return add_query_arg( 'attempt_key', $this->get_key(), get_permalink( $this->get_quiz()->get( 'id' ) ) );
	}

	/**
	 * Get array of serialized questions
	 * @param    boolean    $cache  if true, save data to to the object for future gets
	 * @return   mixed
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_questions( $cache = true ) {

		$questions = $this->get( 'questions', $cache );
		if ( $questions ) {
			return unserialize( $questions );
		}
		return array();

	}

	/**
	 * Retrieve an array of attempt question objects
	 * @param    boolean    $cache  if true, save data to to the object for future gets
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_question_objects( $cache = true ) {

		$questions = array();
		foreach ( $this->get_questions( $cache ) as $qdata ) {
			$questions[] = new LLMS_Quiz_Attempt_Question( $qdata );
		}
		return $questions;

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
	 * Retrieve a title-like string
	 * @return   string
	 * @since    3.16.0
	 * @version  3.26.3
	 */
	public function get_title() {
		$student = $this->get_student();
		$name = $student ? $this->get_student()->get_name() : apply_filters( 'llms_quiz_attempt_deleted_student_name', __( '[Deleted]', 'lifterlms' ) );
		return sprintf( __( 'Quiz Attempt #%1$d by %2$s', 'lifterlms' ), $this->get( 'attempt' ), $name );
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
	 * @version  3.16.0
	 */
	public static function init( $quiz_id, $lesson_id, $student = null ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			throw new Exception( __( 'You must be logged in to take a quiz!', 'lifterlms' ) );
		}

		// initialize a new attempt
		$attempt = new self();
		$attempt->set( 'quiz_id', $quiz_id );
		$attempt->set( 'lesson_id', $lesson_id );
		$attempt->set( 'student_id', $student->get_id() );
		$attempt->set_status( 'incomplete' );
		$attempt->set_questions( $attempt->get_new_questions() );

		$number = 1;

		$last_attempt = $student->quizzes()->get_last_attempt( $quiz_id );
		if ( $last_attempt ) {
			$number = absint( $last_attempt->get( 'attempt' ) ) + 1;
		}
		$attempt->set( 'attempt', $number );

		return $attempt;

	}

	/**
	 * Determine if the attempt can be autograded
	 * @return   bool
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	private function is_auto_gradeable() {

		foreach ( $this->get_question_objects() as $question ) {

			if ( 'waiting' === $question->get_status() ) {
				return false;
			}
		}

		return true;

	}

	/**
	 * Determine if the attempt was passing
	 * @return   boolean
	 * @since    3.9.2
	 * @version  3.16.0
	 */
	public function is_passing() {
		return ( 'pass' === $this->get( 'status' ) );
	}

	/**
	 * Translate attempt related strings
	 * @param    string     $key  key to translate
	 * @return   string
	 * @since    3.9.0
	 * @version  3.16.0
	 */
	public function l10n( $key ) {

		switch ( $key ) {

			case 'passed': // deprecated
			case 'status':
				$statuses = llms_get_quiz_attempt_statuses();
				return $statuses[ $this->get( 'status' ) ];
			break;

		}

		return '';

	}

	/**
	 * Setter for serialized questions array
	 * @param    array      $questions  question data
	 * @param    boolean    $save       if true, immediately persists to database
	 * @return   self
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function set_questions( $questions = array(), $save = false ) {
		return $this->set( 'questions', serialize( $questions ), $save );
	}

	/**
	 * Set the status of the attempt
	 * @param    string     $status   status value
	 * @param    boolean    $save     if true, immediately persists to database
	 * @return   self
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function set_status( $status, $save = false ) {

		$statuses = array_keys( llms_get_quiz_attempt_statuses() );
		if ( ! in_array( $status , $statuses ) ) {
			return false;
		}
		return $this->set( 'status', $status );

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



	/*
		       /$$                                                               /$$                     /$$
		      | $$                                                              | $$                    | $$
		  /$$$$$$$  /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$$  /$$$$$$  /$$$$$$    /$$$$$$   /$$$$$$$
		 /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$__  $$ /$$_____/ |____  $$|_  $$_/   /$$__  $$ /$$__  $$
		| $$  | $$| $$$$$$$$| $$  \ $$| $$  \__/| $$$$$$$$| $$        /$$$$$$$  | $$    | $$$$$$$$| $$  | $$
		| $$  | $$| $$_____/| $$  | $$| $$      | $$_____/| $$       /$$__  $$  | $$ /$$| $$_____/| $$  | $$
		|  $$$$$$$|  $$$$$$$| $$$$$$$/| $$      |  $$$$$$$|  $$$$$$$|  $$$$$$$  |  $$$$/|  $$$$$$$|  $$$$$$$
		 \_______/ \_______/| $$____/ |__/       \_______/ \_______/ \_______/   \___/   \_______/ \_______/
		                    | $$
		                    | $$
		                    |__/
	*/

	/**
	 * Get the attempts status based on start and end dates
	 * @return   string
	 * @since      3.9.0
	 * @version    3.16.0
	 * @deprecated 3.16.0
	 */
	public function get_status() {
		llms_deprecated_function( 'LLMS_Quiz_Attempt::get_status()', '3.16.0', "LLMS_Quiz_Attempt::get( 'status' )" );
		return $this->get( 'status' );
	}

}
