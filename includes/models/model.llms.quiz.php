<?php
/**
 * LifterLMS Quiz Model
 *
 * @package  LifterLMS/Models
 * @since    3.3.0
 * @version  3.19.2
 *
 * @property  $allowed_attempts  (int)  Number of times a student is allowed to take the quiz before being locked out of it
 * @property  $passing_percent  (float)  Grade required for a student to "pass" the quiz
 * @property  $random_answers  (yesno)  Whether or not to randomize the order of answers to the quiz questions
 * @property  $random_questions  (yesno)  Whether or not to randomize the order of questions for each attempt
 * @property  $show_correct_answer  (yesno)  Whether or not to show the correct answer(s) to students on the quiz results screen
 * @property  $show_options_description_right_answer  (yesno)  If yes, displays the question description when the student chooses the correct answer
 * @property  $show_options_description_wrong_answer  (yesno)  If yes, displays the question description when the student chooses the wrong answer
 * @property  $show_results  (yesno)  If yes, results will be shown to the student at the conclusion of the quiz
 * @property  $time_limit  (int)  Quiz time limit (in minutes), empty denotes unlimited (untimed) quiz
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Quiz model.
 */
class LLMS_Quiz extends LLMS_Post_Model {

	protected $db_post_type    = 'llms_quiz';
	protected $model_post_type = 'quiz';

	protected $properties = array(
		'lesson_id'           => 'absint',
		'allowed_attempts'    => 'int',
		'limit_attempts'      => 'yesno',
		'limit_time'          => 'yesno',
		'passing_percent'     => 'float',
		'random_questions'    => 'yesno',
		'show_correct_answer' => 'yesno',
		'time_limit'          => 'int',
	);

	/**
	 * Retrieve the LLMS_Course for the quiz
	 *
	 * @return   obj
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_course() {
		$lesson = $this->get_lesson();
		if ( $lesson ) {
			return $lesson->get_course();
		}
		return false;
	}

	/**
	 * Retrieve LLMS_Lesson for the quiz's parent lesson
	 *
	 * @return   obj
	 * @since    3.16.0
	 * @version  3.16.12
	 */
	public function get_lesson() {
		$id = $this->get( 'lesson_id' );
		if ( ! $id ) {
			return false;
		}
		return llms_get_post( $id );
	}

	/**
	 * Retrieve the quizzes child questions
	 *
	 * @param    string $return  type of return [ids|posts|questions]
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_questions( $return = 'questions' ) {
		return $this->questions()->get_questions( $return );
	}

	/**
	 * Retrieve the time limit formatted as a human readable string
	 *
	 * @return   string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_time_limit_string() {

		return LLMS_Date::convert_to_hours_minutes_string( $this->get( 'time_limit' ) );

	}

	/**
	 * Determine if the quiz defines limited attempts
	 *
	 * @return   bool
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function has_attempt_limit() {
		return ( 'yes' === $this->get( 'limit_attempts' ) );
	}

	/**
	 * Determine if a time limit is enabled for the quiz
	 *
	 * @return   bool
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function has_time_limit() {
		return ( 'yes' === $this->get( 'limit_time' ) );
	}

	/**
	 * Determine if the quiz is an orphan
	 *
	 * @return   bool
	 * @since    3.16.12
	 * @version  3.16.12
	 */
	public function is_orphan() {

		$parent_id = $this->get( 'lesson_id' );

		if ( ! $parent_id ) {
			return true;
		}

		return false;

	}

	/**
	 * Determine if a student can take the quiz
	 *
	 * @param    int $user_id   WP User ID, none supplied uses current user
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.16.0
	 */
	public function is_open( $user_id = null ) {

		$student = llms_get_student( $user_id );
		if ( ! $student ) {
			return false;
		}

		$remaining = $student->quizzes()->get_attempts_remaining_for_quiz( $this->get( 'id' ) );

		// string for "unlimited" or number of attempts
		if ( ! is_numeric( $remaining ) || $remaining > 0 ) {
			return true;
		}

		return false;

	}

	/**
	 * Retrieve an instance of the question manager for the quiz
	 *
	 * @return   obj
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function questions() {
		return new LLMS_Question_Manager( $this );
	}

	/**
	 * Called before data is sorted and returned by $this->toArray()
	 * Extending classes should override this data if custom data should
	 * be added when object is converted to an array or json
	 *
	 * @param    array $arr   array of data to be serialized
	 * @return   array
	 * @since    3.3.0
	 * @version  3.19.2
	 */
	protected function toArrayAfter( $arr ) {

		$arr['questions'] = array();

		// builder lazy loads questions via ajax
		global $llms_builder_lazy_load;
		if ( ! $llms_builder_lazy_load ) {
			foreach ( $this->get_questions() as $question ) {
				$arr['questions'][] = $question->toArray();
			}
		}

		// if theme support quizzes, add theme metadata to the array
		if ( get_theme_support( 'lifterlms-quizzes' ) ) {
			$layout = llms_get_quiz_theme_setting( 'layout' );
			if ( $layout ) {
				$arr[ $layout['id'] ] = get_post_meta( $this->get( 'id' ), $layout['id'], true );
			}
		}

		return $arr;

	}











	/**
	 * Retrieve lessons this quiz is assigned to
	 *
	 * @param    string $return  format of the return [ids|lessons]
	 * @return   array              array of WP_Post IDs (lesson post types)
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function get_lessons( $return = 'ids' ) {

		global $wpdb;
		$query = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id
			 FROM {$wpdb->postmeta}
			 WHERE meta_key = '_llms_assigned_quiz'
			   AND meta_value = %d;",
				$this->get( 'id' )
			)
		);

		// return just the ids
		if ( 'ids' === $return ) {
			return $query;
		}

		// setup lesson objects
		$ret = array();
		foreach ( $query as $id ) {
			$ret[] = llms_get_post( $id );
		}
		return $ret;

	}


	/**
	 * Get the (points) value of a question
	 *
	 * @param    int $question_id  WP Post ID of the LLMS_Question
	 * @return   int
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	public function get_question_value( $question_id ) {

		foreach ( $this->get_questions_raw() as $q ) {
			if ( $question_id == $q['id'] ) {
				return absint( $q['points'] );
			}
		}

		return 0;

	}

	/**
	 * Retrieve the array of raw question data from the postmeta table
	 *
	 * @return   array
	 * @since    3.3.0
	 * @version  3.3.0
	 */
	private function get_questions_raw() {

		$q = get_post_meta( $this->get( 'id' ), $this->meta_prefix . 'questions', true );
		return $q ? $q : array();

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
	 * Get remaining quiz attempts
	 *
	 * @param      int $user_id   WP_User ID, if not supplied uses current user
	 * @return     int
	 * @since      1.0.0
	 * @version    3.16.0
	 * @deprecated 3.16.0
	 */
	public function get_remaining_attempts_by_user( $user_id = null ) {

		llms_deprecated_function( 'LLMS_Quiz::get_remaining_attempts_by_user()', '3.16.0', 'LLMS_Student::quizzes()->get_attempts_remaining_for_quiz( $quiz_id )' );

		$student = llms_get_student( $user_id );
		if ( $student ) {
			return $student->quizzes()->get_attempts_remaining_for_quiz( $this->get( 'id' ) );
		}

		return 0;

	}


	/**
	 * Retrieve the configured time limit
	 *
	 * @return      int
	 * @since       1.0.0
	 * @version     3.16.0
	 * @deprecated  3.16.0
	 */
	public function get_time_limit() {
		llms_deprecated_function( 'LLMS_Quiz::get_time_limit()', '3.16.0', 'LLMS_Quiz::get( "time_limit" )' );
		return $this->get( 'time_limit' );
	}

	/**
	 * Retrieve the configured time limit
	 *
	 * @return      int
	 * @since       1.0.0
	 * @version     3.16.0
	 * @deprecated  3.16.0
	 */
	public function get_total_allowed_attempts() {
		llms_deprecated_function( 'LLMS_Quiz::get_total_allowed_attempts()', '3.16.0', 'LLMS_Quiz::get( "allowed_attempts" )' );
		return $this->get( 'allowed_attempts' );
	}

	/**
	 * Get total attempts by user
	 *
	 * @param       int $user_id  a WP_User ID, if not supplied uses current user
	 * @return      int
	 * @since       1.0.0
	 * @version     3.16.0
	 * @deprecated  3.16.0
	 */
	public function get_total_attempts_by_user( $user_id = null ) {

		llms_deprecated_function( 'LLMS_Quiz::get_total_attempts_by_user()', '3.16.0', 'LLMS_Student::quizzes()->count_attempts_by_quiz( $quiz_id )' );

		$student = llms_get_student( $user_id );
		if ( ! $student ) {
			return 0;
		}

		return $student->quizzes()->count_attempts_by_quiz( $this->get( 'id' ) );

	}

	public function get_passing_percent() {
		// deprecate
		return $this->get( 'passing_percent' );

	}

	public function get_assoc_lesson() {
		// deprecate
		return $this->get( 'lesson_id' );
	}

}
