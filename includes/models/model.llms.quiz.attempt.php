<?php
/**
 * Quiz Attempt Model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.9.0
 * @version 7.4.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Quiz_Attempt model class.
 *
 * @since 3.9.0
 * @since 3.9.2 Added `calculate_point_weight()`, `get_question_order()`, `is_passing()` methods.
 * @since 3.16.0 Unknown.
 * @since 3.16.7 Unknown.
 * @since 3.17.1 Unknown.
 * @since 3.19.2 Unknown.
 * @since 3.24.0 Unknown.
 * @since 3.26.3 Unknown.
 * @since 3.29.0 Unknown.
 * @since 4.0.0 Remove reliance on deprecated method `LLMS_Quiz::get_passing_percent()` & remove deprecated class method `get_status()`.
 *              Fix issue encountered when answering a question incorrectly after initially answering it correctly.
 * @since 4.2.0 Use strict type comparisons where possible.
 *              In the `l10n()` method, made sure the status key exists to avoid trying to access to array's undefined index.
 *              Added the public method `get_siblings()`.
 * @since 4.3.0 Added `$type` property declaration.
 */
class LLMS_Quiz_Attempt extends LLMS_Abstract_Database_Store {

	/**
	 * Array of table column name => format
	 *
	 * @since unknown
	 * @since 7.8.0 Added `can_be_resumed` column.
	 * @var array
	 */
	protected $columns = array(
		'student_id'          => '%d',
		'quiz_id'             => '%d',
		'lesson_id'           => '%d',
		'start_date'          => '%s',
		'update_date'         => '%s',
		'end_date'            => '%s',
		'status'              => '%s',
		'attempt'             => '%d',
		'grade'               => '%f',
		'questions'           => '%s',
		'can_be_resumed'      => '%d',
		'current_question_id' => '%d',
	);

	protected $date_created = 'start_date';
	protected $date_updated = 'update_date';

	/**
	 * Database Table Name
	 *
	 * @var string
	 */
	protected $table = 'quiz_attempts';

	/**
	 * The record type
	 *
	 * @var string
	 */
	protected $type = 'quiz_attempt';

	/**
	 * Constructor
	 *
	 * @since 3.9.0
	 * @since 3.16.0 Unknown.
	 *
	 * @param mixed $item Optional. Array/obj of attempt data or int. Default `null`.
	 * @return void
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
	 *
	 * Records the selected option and whether or not the selected option was the correct option.
	 *
	 * Automatically updates & saves the attempt to the database
	 *
	 * @since 3.9.0
	 * @since 3.16.0 Updated to accommodate quiz builder improvements.
	 * @since 4.0.0 Explicitly set earned points to `0` when answering incorrectly.
	 *              Exit the loop as soon as we find our question.
	 *              Use strict comparison for IDs.
	 *
	 * @param int      $question_id WP_Post ID of the LLMS_Question.
	 * @param string[] $answer      Array of selected choice IDs (for core question types) or an array containing the user-submitted answer(s).
	 * @return LLMS_Quiz_Attempt Instance of the current attempt.
	 */
	public function answer_question( $question_id, $answer ) {

		$questions = $this->get_questions();

		foreach ( $questions as $key => $data ) {

			if ( absint( $question_id ) !== absint( $data['id'] ) ) {
				continue;
			}

			$question                     = llms_get_post( $question_id );
			$graded                       = $question->grade( $answer );
			$questions[ $key ]['answer']  = $answer;
			$questions[ $key ]['correct'] = $graded;
			$questions[ $key ]['earned']  = llms_parse_bool( $graded ) ? $questions[ $key ]['points'] : 0;

			break;
		}

		$this->set_questions( $questions )->save();

		return $this;
	}

	/**
	 * Calculate and the grade for a completed quiz
	 *
	 * @since 3.9.0
	 * @since 3.24.0 Unknown.
	 * @since 4.0.0 Remove reliance on deprecated method `LLMS_Quiz::get_passing_percent()`.
	 *
	 * @return LLMS_Quiz_Attempt Instance of the current quiz attempt.
	 */
	public function calculate_grade() {

		$status = 'pending';

		if ( $this->is_auto_gradeable() ) {

			$grade = llms()->grades()->round( $this->get_count( 'earned' ) * $this->calculate_point_weight() );

			$quiz      = $this->get_quiz();
			$min_grade = $quiz ? $quiz->get( 'passing_percent' ) : 100;

			$this->set( 'grade', $grade );
			$status = ( $min_grade <= $grade ) ? 'pass' : 'fail';

		}

		$this->set_status( $status );

		return $this;
	}

	/**
	 * Calculate the weight of each point
	 *
	 * @since 3.9.2
	 * @since 3.16.0 Unknown.
	 *
	 * @return float
	 */
	private function calculate_point_weight() {
		$available = $this->get_count( 'available_points' );
		return ( $available > 0 ) ? ( 100 / $available ) : 0;
	}

	/**
	 * Run actions designating quiz completion
	 *
	 * @since 3.16.0
	 * @since 3.17.1 Unknown.
	 *
	 * @return void
	 */
	public function do_completion_actions() {

		// Do quiz completion actions.
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
	 *
	 * Sets end date, unsets the quiz as the current quiz, and records a grade.
	 *
	 * @since 3.9.0
	 * @since 3.16.0 Unknown.
	 *
	 * @param boolean $silent Optional. If `true`, will not trigger actions or mark related lesson as complete. Default `false`.
	 * @return LLMS_Quiz_Attempt This quiz attempt instance (for chaining).
	 */
	public function end( $silent = false ) {

		$this->set( 'end_date', current_time( 'mysql' ) );
		$this->calculate_grade()->save();

		if ( ! $silent ) {

			$this->do_completion_actions();

		}

		// Clear "cached" grade so it's recalculated next time it's requested.
		$this->get_student()->set( 'overall_grade', '' );

		return $this;
	}

	/**
	 * Get sibling attempts
	 *
	 * @since 4.2.0
	 *
	 * @param array  $args Optional. List of args to be passed as params of the quiz attempts query. Default empty array.
	 *                     See `LLMS_Query_Quiz_Attempt` and `LLMS_Database_Query` for the list of args.
	 *                     By default the `per_page` param is set to 1000.
	 * @param string $return Optional. Type of return [ids|attempts]. Default 'attempts'.
	 * @return int[]|LLMS_Quiz_Attempt[] Type depends on value of `$return`.
	 */
	public function get_siblings( $args = array(), $return = 'attempts' ) {

		$defaults = array(
			'per_page' => 1000,
		);

		$args  = wp_parse_args( $args, $defaults );
		$query = new LLMS_Query_Quiz_Attempt(
			array_merge(
				$args,
				array(
					'student_id' => $this->get( 'student_id' ),
					'quiz_id'    => $this->get( 'quiz_id' ),
				)
			)
		);

		return 'ids' === $return ? wp_list_pluck( $query->get_results(), 'id' ) : $query->get_attempts();
	}

	/**
	 * Retrieve a count for various pieces of information related to the attempt
	 *
	 * @since 3.9.0
	 * @since 3.19.2 Unknown.
	 * @since 4.2.0 Ensure only one return point.
	 *
	 * @param string $key The key of the data to count.
	 * @return int
	 */
	public function get_count( $key ) {

		$count     = 0;
		$questions = $this->get_questions();

		switch ( $key ) {

			case 'available_points':
			case 'correct_answers':
			case 'earned':
			case 'gradeable_questions': // Like "questions" but excludes content questions.
			case 'points': // Legacy version of earned.
				foreach ( $questions as $data ) {
					// Get the total number of correct answers.
					if ( 'correct_answers' === $key ) {
						if ( 'yes' === $data['correct'] ) {
							++$count;
						}
					} elseif ( 'earned' === $key || 'points' === $key ) {
						$count += $data['earned'];
						// Get the total number of possible points.
					} elseif ( 'available_points' === $key ) {
						$count += $data['points'];
					} elseif ( 'gradeable_questions' === $key ) {
						if ( $data['points'] ) {
							++$count;
						}
					}
				}
				break;

			case 'questions':
				$count = count( $questions );
				break;

		}

		return $count;
	}

	/**
	 * Retrieve a formatted date
	 *
	 * @since 3.9.0
	 * @since 3.16.0 Unknown.
	 *
	 * @param string $key    'start' or 'end'.
	 * @param string $format Optional. Output date format (PHP), uses WordPress format options if none provided.
	 *                       If not provided defaults to WP date format options.
	 * @return string
	 */
	public function get_date( $key, $format = null ) {
		if ( ! $this->get( $key . '_date' ) ) {
			return '';
		}
		$date   = strtotime( $this->get( $key . '_date' ) );
		$format = ! $format ? get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) : $format;
		return date_i18n( $format, $date );
	}

	/**
	 * Retrieve the first question for the attempt
	 *
	 * @since 3.9.0
	 * @since 3.16.0 Unknown.
	 *
	 * @return int|false
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
	 *
	 * @since 3.9.2
	 * @since 3.16.0 Unknown.
	 * @since 4.2.0 Use strict type comparison.
	 *
	 * @param int $question_id WP Post ID of the LLMS_Question.
	 * @return int
	 */
	public function get_question_order( $question_id ) {

		foreach ( $this->get_questions() as $order => $data ) {

			if ( absint( $data['id'] ) === $question_id ) {
				return $order + 1;
			}
		}

		return 0;
	}

	/**
	 * Get an encoded attempt key that can be passed in URLs and the like
	 *
	 * @since 3.9.0
	 * @since 3.16.7 Unknown.
	 *
	 * @return string
	 */
	public function get_key() {
		return LLMS_Hasher::hash( $this->get( 'id' ) );
	}

	/**
	 * Retrieve an array of blank questions for insertion into a new attempt during initialization.
	 *
	 * @since 3.9.0
	 * @since 3.16.0 Unknown.
	 * @since 7.4.1 Moved randomization into `LLMS_Quiz_Attempt::randomize_attempt_questions()`.
	 *
	 * @return array
	 */
	private function get_new_questions() {

		$quiz = llms_get_post( $this->get( 'quiz_id' ) );

		$questions = array();

		if ( $quiz ) {

			/**
			 * Filter randomize value for quiz questions.
			 *
			 * @since 7.4.0
			 *
			 * @param bool              $randomize The randomize boolean value.
			 * @param LLMS_Quiz         $quiz      LLMS_Quiz instance.
			 * @param LLMS_Quiz_Attempt $attempt   LLMS_Quiz_Attempt instance.
			 */
			$randomize = apply_filters( 'llms_quiz_attempt_questions_randomize', llms_parse_bool( $quiz->get( 'random_questions' ) ), $quiz, $this );

			/**
			 * Filter questions for the quiz.
			 *
			 * Sets the questions to be used for the quiz.
			 *
			 * @since 7.4.0
			 *
			 * @param array             $questions Array of LLMS_Question objects.
			 * @param LLMS_Quiz         $quiz      LLMS_Quiz instance.
			 * @param LLMS_Quiz_Attempt $attempt   LLMS_Quiz_Attempt instance.
			 */
			$quiz_questions = apply_filters( 'llms_quiz_attempt_questions', $quiz->get_questions(), $quiz, $this );

			foreach ( $quiz_questions as $index => $question ) {

				$questions[] = array(
					'id'      => $question->get( 'id' ),
					'earned'  => 0,
					'points'  => $question->supports( 'points' ) ? $question->get( 'points' ) : 0,
					'answer'  => null,
					'correct' => null,
				);

			}

			/**
			 * Filter attempt's questions array for the quiz.
			 *
			 * @since 7.4.1
			 *
			 * @param array             $questions Array of question (each question is an array itself).
			 * @param LLMS_Quiz         $quiz      LLMS_Quiz instance.
			 * @param LLMS_Quiz_Attempt $attempt   LLMS_Quiz_Attempt instance.
			 */
			$questions = apply_filters( 'llms_quiz_attempt_questions_array', $questions, $quiz, $this );

			if ( $randomize ) {
				$questions = self::randomize_attempt_questions( $questions );
			}
		}

		return $questions;
	}

	/**
	 * Retrieve the next unanswered question in the attempt
	 *
	 * @since 3.9.0
	 * @since 3.16.0 Unknown.
	 * @since 4.2.0 Use strict type comparison.
	 * @since 7.8.0 Added `$return` param, by default `"id"`.
	 *
	 * @param int    $last_question Optional. WP Post ID of the current LLMS_Question the "next" refers to. Default `null`.
	 * @param string $return        Optional. Return type 'id|array'. Default 'id'.
	 * @return int|array|false
	 */
	public function get_next_question( $last_question = null, $return = 'id' ) {

		$next = false;

		foreach ( $this->get_questions() as $question ) {

			if ( $next || is_null( $question['answer'] ) ) {

				return 'id' === $return ? $question['id'] : $question;

				// When rewinding and moving back through we don't want to skip questions.
			} elseif ( $last_question && absint( $last_question ) === absint( $question['id'] ) ) {
				$next = true;
			}
		}

		return false;
	}

	/**
	 * Retrieve the previous question in the attempt relative to a given question ID.
	 *
	 * @since 7.8.0
	 *
	 * @param int $question_id WP Post ID of the current LLMS_Question.
	 * @return int|false
	 */
	public function get_previous_question( $question_id ) {

		$next = false;

		foreach ( array_reverse( $this->get_questions() ) as $question ) {

			if ( $next ) {

				return $question['id'];

			} elseif ( $question_id && absint( $question_id ) === absint( $question['id'] ) ) {
				$next = true;
			}
		}

		return false;
	}


	/**
	 * Retrieve the question in the attempt.
	 *
	 * @since 7.8.0
	 *
	 * @param int    $question_id WP Post ID of the required LLMS_Question.
	 * @param string $return      Optional. Return type 'id|array'. Default 'id'.
	 * @return int|array|false
	 */
	public function get_question( $question_id, $return = 'id' ) {

		$ids   = array_map( 'intval', array_column( $this->get_questions(), 'id' ) );
		$index = array_search( (int) $question_id, $ids, true );
		return $index >= 0 ?
			'id' === $return ? $question_id : $this->get_questions()[ $index ]
			:
			false;
	}

	/**
	 * Retrieve a permalink for the attempt
	 *
	 * @since 3.9.0
	 *
	 * @return string
	 */
	public function get_permalink() {
		if ( ! $this->get_quiz() ) {
			return '';
		}
		return add_query_arg( 'attempt_key', $this->get_key(), get_permalink( $this->get_quiz()->get( 'id' ) ) );
	}

	/**
	 * Get array of serialized questions
	 *
	 * @since 3.16.0
	 *
	 * @param boolean $cache Optional. If `true`, save data to to the object for future gets. Default `true`.
	 * @return array
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
	 *
	 * @since 3.16.0
	 * @since 5.3.0 Add a parameter to filter out removed questions.
	 *
	 * @param boolean $cache          Optional. If `true`, save data to to the object for future gets. Default `true`.
	 *                                Cached questions won't take into account the `$filte_removed` parameter.
	 * @param boolean $filter_removed Optional. If `true`, removed questions will be filtered out. Default `false`.
	 * @return array
	 */
	public function get_question_objects( $cache = true, $filter_removed = false ) {

		$questions = array();
		foreach ( $this->get_questions( $cache ) as $qdata ) {
			$question = new LLMS_Quiz_Attempt_Question( $qdata );
			if ( ! $filter_removed || $question->get_question() instanceof LLMS_Question ) {
				$questions[] = $question;
			}
		}
		return $questions;
	}

	/**
	 * Retrieve an an answer to a specific question.
	 *
	 * @since 7.8.0
	 *
	 * @param int     $question_id    Question ID.
	 * @param boolean $cache          Optional. If `true`, save data to to the object for future gets. Default `true`.
	 *                                Cached questions won't take into account the `$filte_removed` parameter.
	 * @param boolean $filter_removed Optional. If `true`, removed questions will be filtered out. Default `false`.
	 * @return mixed
	 */
	public function get_question_answer( $question_id, $cache = true, $filter_removed = false ) {

		$question_objects = $this->get_question_objects( $cache, $filter_removed );
		if ( ! $question_objects ) {
			return array();
		}
		foreach ( $question_objects as $attempt_question ) {
			$quiz_question = $attempt_question->get_question();
			if ( $attempt_question->get_question()->get( 'id' ) === $question_id ) {
				$answer = $attempt_question->get( 'answer' );
				break;
			}
		}

		return $answer ?? array();
	}

	/**
	 * Get an instance of the LLMS_Quiz for the attempt
	 *
	 * @since 3.9.0
	 *
	 * @return LLMS_Quiz
	 */
	public function get_quiz() {
		return llms_get_post( $this->get( 'quiz_id' ) );
	}

	/**
	 * Get an LLMS_Student for the quiz
	 *
	 * @since 3.9.0
	 *
	 * @return LLMS_Student
	 */
	public function get_student() {
		return llms_get_student( $this->get( 'student_id' ) );
	}

	/**
	 * Get the time spent on the quiz from start to end
	 *
	 * @since 3.9.0
	 *
	 * @param integer $precision Precision passed to `llms_get_date_diff()`.
	 * @return string
	 */
	public function get_time( $precision = 2 ) {
		if ( ! $this->get( 'end_date' ) ) {
			return esc_html__( 'Unknown', 'lifterlms' );
		}
		return llms_get_date_diff( $this->get_date( 'start', 'U' ), $this->get_date( 'end', 'U' ), $precision );
	}

	/**
	 * Retrieve a title-like string
	 *
	 * @since 3.16.0
	 * @since 3.26.3 Unknown.
	 *
	 * @return string
	 */
	public function get_title() {
		$student = $this->get_student();
		$name    = $student ? $this->get_student()->get_name() : apply_filters( 'llms_quiz_attempt_deleted_student_name', __( '[Deleted]', 'lifterlms' ) );
		return sprintf( __( 'Quiz Attempt #%1$d by %2$s', 'lifterlms' ), $this->get( 'attempt' ), $name );
	}

	/**
	 * Initialize a new quiz attempt by quiz and lesson for a user.
	 *
	 * If no user found throws an Exception.
	 *
	 * @since 3.9.0
	 * @since 3.16.0 Unknown.
	 * @since 7.8.0 Set the property `can_be_resumed` on init.
	 *
	 * @throws Exception When the user is not logged in.
	 *
	 * @param int   $quiz_id   WP Post ID of the quiz.
	 * @param int   $lesson_id WP Post ID of the lesson.
	 * @param mixed $student   Optional. Accepts anything that can be passed to llms_get_student.
	 *                         If no user is passed the current user will be used. Default `null`.
	 *
	 * @return obj
	 */
	public static function init( $quiz_id, $lesson_id, $student = null ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			throw new Exception( esc_html__( 'You must be logged in to take a quiz!', 'lifterlms' ) );
		}

		// Initialize a new attempt.
		$attempt = new self();
		$quiz    = llms_get_post( $quiz_id );
		$attempt->set( 'quiz_id', $quiz_id );
		$attempt->set( 'lesson_id', $lesson_id );
		$attempt->set( 'student_id', $student->get_id() );
		$attempt->set_status( 'incomplete' );
		$attempt->set( 'can_be_resumed', $quiz->can_be_resumed() );
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
	 * Randomize attempt questions.
	 *
	 * Logic moved from `LLMS_Quiz_Attempt::get_new_questions()`.
	 *
	 * @since 7.4.1
	 *
	 * @param array $questions Array of attempt's questions (each question is an array itself).
	 * @return array.
	 */
	public static function randomize_attempt_questions( $questions ) {

		if ( empty( $questions ) ) {
			return $questions;
		}

		// Array of indexes that will be locked during shuffling.
		$locks = array();
		foreach ( $questions as $index => $question_array ) {
			$question = llms_get_post( $question_array['id'] );
			// If randomization is enabled, store the questions index so we can lock it during randomization.
			if ( $question->supports( 'random_lock' ) ) {
				$locks[] = $index;
			}
		}

		// Lifted from https://stackoverflow.com/a/28491007/400568.
		// I generally comprehend this code but also in a truer way i have no idea...
		$inc = array();
		$i   = 0;
		$j   = 0;
		$l   = count( $questions );
		$le  = count( $locks );
		while ( $i < $l ) {
			if ( $j >= $le || $i < $locks[ $j ] ) {
				$inc[] = $i;
			} else {
				++$j;
			}
			++$i;
		}

		// Fisher-yates-knuth shuffle variation O(n).
		$num = count( $inc );
		while ( $num-- ) {
			$perm                       = wp_rand( 0, $num );
			$swap                       = $questions[ $inc[ $num ] ];
			$questions[ $inc[ $num ] ]  = $questions[ $inc[ $perm ] ];
			$questions[ $inc[ $perm ] ] = $swap;
		}

		return $questions;
	}

	/**
	 * Determine if the attempt can be autograded.
	 *
	 * @since 3.16.0
	 *
	 * @return bool
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
	 * Determine if the attempt is the last attempt of its quiz.
	 *
	 * @since 7.8.0
	 *
	 * @return bool
	 */
	public function is_last_attempt() {

		$student = llms_get_student( $this->get( 'student_id' ) );
		if ( ! $student ) {
			return false;
		}

		$last_attempt = $student->quizzes()->get_last_attempt( $this->get( 'quiz_id' ) );
		return $last_attempt && ( $this->get( 'id' ) === $last_attempt->get( 'id' ) );
	}

	/**
	 * Determine if the attempt was passing.
	 *
	 * @since 3.9.2
	 * @since 3.16.0 Unknown.
	 *
	 * @return boolean
	 */
	public function is_passing() {
		return ( 'pass' === $this->get( 'status' ) );
	}

	/**
	 * Determine if the attempt can be resumed.
	 *
	 * @since 7.8.0
	 *
	 * @return boolean
	 */
	public function can_be_resumed() {

		return 1 === (int) $this->get( 'can_be_resumed' ) && 'incomplete' === $this->get( 'status' ) && ! $this->has_resume_attempt_time_expired() && $this->is_last_attempt();
	}

	/**
	 * Determine if the student's resume quiz attempt time limit is expired.
	 *
	 * The default resume quiz attempt time limit for a student is 1 day.
	 *
	 * @since 7.8.0
	 *
	 * @return bool
	 */
	public function has_resume_attempt_time_expired() {

		$student = llms_get_student();

		if ( ! $student ) {
			return false;
		}

		if ( ! $this->get( 'start_date' ) ) {
			return false;
		}
		$start_date = $this->get( 'start_date' );

		/**
		 * Filters the X time for resuming quiz.
		 *
		 * @since 7.8.0
		 *
		 * @param int $resume_time_period The time period in hours.
		 */
		$resume_time_period = apply_filters( 'llms_quiz_attempt_resume_time_period', 24, $this );

		$start_date_obj   = new DateTime( $start_date, wp_timezone() );
		$current_date_obj = current_datetime();

		return $current_date_obj > $start_date_obj->modify( sprintf( '+%d hours', intval( $resume_time_period ) ) );
	}

	/**
	 * Translate attempt related strings.
	 *
	 * @since 3.9.0
	 * @since 3.16.0 Unknown.
	 * @since 4.2.0 Made sure the status key exists to avoid trying to access to array's undefined index.
	 *
	 * @param string $key Key to translate.
	 * @return string
	 */
	public function l10n( $key ) {

		$tkey = '';

		switch ( $key ) {

			case 'passed': // Deprecated.
			case 'status':
				$statuses = llms_get_quiz_attempt_statuses();
				$status   = $this->get( 'status' );
				$tkey     = ( $status && isset( $statuses[ $status ] ) ) ? $statuses[ $status ] : $tkey;
				break;

		}

		return $tkey;
	}

	/**
	 * Setter for serialized questions array.
	 *
	 * @since 3.16.0
	 *
	 * @param array   $questions Question data.
	 * @param boolean $save      Optional. If `true`, immediately persists to database. Default `false`.
	 * @return LLMS_Quiz_Attempt This quiz attempt instance (for chaining).
	 */
	public function set_questions( $questions = array(), $save = false ) {
		return $this->set( 'questions', serialize( $questions ), $save );
	}

	/**
	 * Set the status of the attempt.
	 *
	 * @since 3.16.0
	 * @since 4.0.0 Use strict comparisons.
	 *
	 * @param string  $status Status value.
	 * @param boolean $save   If `true`, immediately persists to database.
	 * @return false|LLMS_Quiz_Attempt
	 */
	public function set_status( $status, $save = false ) {

		$statuses = array_keys( llms_get_quiz_attempt_statuses() );
		if ( ! in_array( $status, $statuses, true ) ) {
			return false;
		}
		return $this->set( 'status', $status );
	}

	/**
	 * Record the attempt as started
	 *
	 * @since 3.9.0
	 *
	 * @return LLMS_Quiz_Attempt Instance of the current quiz attempt object.
	 */
	public function start() {

		$this->set( 'start_date', current_time( 'mysql' ) );
		$this->save();
		return $this;
	}

	/**
	 * Retrieve the private data array
	 *
	 * @since 3.9.0
	 *
	 * @return array
	 */
	public function to_array() {
		return $this->data;
	}

	/**
	 * Delete the object from the database.
	 *
	 * Overrides the parent method to perform other actions before deletion.
	 *
	 * @since 4.2.0
	 *
	 * @return bool `true` on success, `false` otherwise.
	 */
	public function delete() {

		if ( ! $this->id ) {
			return false;
		}

		$lesson = llms_get_post( $this->get( 'lesson_id' ) );

		// No lesson, or lesson incomplete, nothing special to do here.
		if ( ! $lesson || ! ( $lesson instanceof LLMS_Lesson ) || ! llms_is_complete( $this->get( 'student_id' ), $this->get( 'lesson_id' ), 'lesson' ) ) {
			return parent::delete();
		}

		/**
		 * Prepare the query args to retrieve at least another sibling attempt,
		 * excluding the current one.
		 */
		$sibling_query_args = array(
			'exclude'  => $this->get_id( 'id' ),
			'per_page' => 1,
		);

		/**
		 * If this lesson requires a passing grade, then retrieve only the possible passed sibling
		 * that might have been triggered the lesson completion.
		 */
		if ( llms_parse_bool( $lesson->get( 'require_passing_grade' ) ) ) {
			$sibling_query_args['status'] = array(
				'pass',
			);
		}

		$sibling_attempts = $this->get_siblings( $sibling_query_args, 'ids' );

		// If this is the only one relevant left attempt.
		if ( empty( $sibling_attempts ) ) {
			llms_mark_incomplete(
				$this->get( 'student_id' ),
				$this->get( 'lesson_id' ),
				'lesson',
				'quiz_' . $this->get( 'quiz_id' )
			);
		}

		return parent::delete();
	}
}
