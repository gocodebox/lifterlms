<?php
/**
 * LifterLMS Quiz Model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.3.0
 * @version 7.6.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Quiz model class.
 *
 * @property $allowed_attempts (int) Number of times a student is allowed to take the quiz before being locked out of it.
 * @property $passing_percent (float) Grade required for a student to "pass" the quiz.
 * @property $random_questions (yesno) Whether or not to randomize the order of questions for each attempt.
 * @property $show_correct_answer (yesno) Whether or not to show the correct answer(s) to students on the quiz results screen.
 * @property $show_options_description_right_answer (yesno) If yes, displays the question description when the student chooses the correct answer.
 * @property $show_options_description_wrong_answer (yesno) If yes, displays the question description when the student chooses the wrong answer.
 * @property $show_results (yesno) If yes, results will be shown to the student at the conclusion of the quiz.
 * @property $time_limit (int) Quiz time limit (in minutes), empty denotes unlimited (untimed) quiz.
 * @property $can_be_resumed (yesno) If yes, the latest incomplete quiz attempt can be resumed.
 *
 * @since 3.3.0
 * @since 3.19.2 Unkwnown.
 * @since 3.37.2 Added `llms_quiz_is_open` filter hook.
 * @since 3.38.0 Only add theme metadata to the quiz array when the `llms_get_quiz_theme_settings` filter is being used.
 * @since 4.0.0 Remove deprecated methods.
 * @since 4.2.0 Added a parameter to the `is_orphan()` method to deeply check the quiz is not really attached to any lesson.
 * @since 5.0.0 Remove previously deprecated method `LLMS_Quiz::get_lessons()`.
 */
class LLMS_Quiz extends LLMS_Post_Model {

	/**
	 * Post Type Database name (as registered via `register_post_type()`).
	 *
	 * @var string
	 */
	protected $db_post_type = 'llms_quiz';

	/**
	 * Post type name (without prefix).
	 *
	 * @var string
	 */
	protected $model_post_type = 'quiz';

	/**
	 * Post type meta properties.
	 *
	 * Array key is the meta_key and array values is property's type.
	 *
	 * @since Unknown.
	 * @since 7.6.2 Added the `disable_retake` property.
	 * @since 7.8.0 Added `can_be_resumed` property.
	 * @var string[] Array key is the meta_key and array values is property's type.
	 */
	protected $properties = array(
		'lesson_id'           => 'absint',
		'allowed_attempts'    => 'int',
		'limit_attempts'      => 'yesno',
		'limit_time'          => 'yesno',
		'passing_percent'     => 'float',
		'random_questions'    => 'yesno',
		'show_correct_answer' => 'yesno',
		'time_limit'          => 'int',
		'can_be_resumed'      => 'yesno',
		'disable_retake'      => 'yesno',
	);

	/**
	 * Retrieve the LLMS_Course for the quiz.
	 *
	 * @since 3.16.0
	 *
	 * @return LLMS_Course|false
	 */
	public function get_course() {
		$lesson = $this->get_lesson();
		if ( $lesson ) {
			return $lesson->get_course();
		}
		return false;
	}

	/**
	 * Retrieve LLMS_Lesson for the quiz's parent lesson.
	 *
	 * @since 3.16.0
	 * @since 3.16.12 Unknown.
	 *
	 * @return LLMS_Lesson|false|null The lesson object on success, `false` if no id stored, and `null` if the stored ID doesn't exist.
	 */
	public function get_lesson() {
		$id = $this->get( 'lesson_id' );
		if ( ! $id ) {
			return false;
		}
		return llms_get_post( $id );
	}

	/**
	 * Retrieve the quizzes child questions.
	 *
	 * @since 3.16.0
	 *
	 * @param string $return Optional. Type of return [ids|posts|questions]. Default `'questions'`.
	 * @return array
	 */
	public function get_questions( $return = 'questions' ) {
		return $this->questions()->get_questions( $return );
	}

	/**
	 * Get questions count.
	 *
	 * @since 7.4.0
	 *
	 * @return int Question Count.
	 */
	public function get_questions_count() {

		/**
		 * Filter the count of questions in a quiz.
		 *
		 * @since 7.4.0
		 *
		 * @param int       $questions_count Number of questions in a quiz.
		 * @param LLMS_Quiz $quiz            Current quiz object.
		 */
		return apply_filters( 'llms_quiz_questions_count', count( $this->get_questions( 'ids' ) ), $this );
	}

	/**
	 * Retrieve the time limit formatted as a human readable string.
	 *
	 * @since 3.16.0
	 *
	 * @return string
	 */
	public function get_time_limit_string() {
		return LLMS_Date::convert_to_hours_minutes_string( $this->get( 'time_limit' ) );
	}

	/**
	 * Determine if the quiz defines limited attempts.
	 *
	 * @since 3.16.0
	 *
	 * @return bool
	 */
	public function has_attempt_limit() {
		return ( 'yes' === $this->get( 'limit_attempts' ) );
	}

	/**
	 * Determine if a time limit is enabled for the quiz.
	 *
	 * @since 3.16.0
	 *
	 * @return bool
	 */
	public function has_time_limit() {
		return ( 'yes' === $this->get( 'limit_time' ) );
	}

	/**
	 * Determine if the quiz is an orphan.
	 *
	 * @since 3.16.12
	 * @since 4.2.0 Added the $deep parameter.
	 *
	 * @param bool $deep Optional. Whether or not deeply check this quiz is orphan. Default `false`.
	 *                   When set to true will ensure not only that this quiz as a `lesson_id` property set
	 *                   But also that the lesson with id `lesson_id` has a `quiz` property as equal as this quiz id.
	 * @return bool
	 */
	public function is_orphan( $deep = false ) {

		$parent_id = $this->get( 'lesson_id' );

		if ( ! $parent_id ) {
			return true;
		}

		/**
		 * This is to take into account possible data inconsistency.
		 *
		 * @link https://github.com/gocodebox/lifterlms/issues/1039
		 */
		if ( $deep ) {
			$lesson = llms_get_post( $parent_id );
			// Both the ids are already absint, see LLMS_Post_Model::___get().
			if ( ! $lesson || $this->get( 'id' ) !== $lesson->get( 'quiz' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if a quiz can be resumed.
	 *
	 * A quiz can only be resumed if it's set to be resumed
	 * and has no time limit.
	 *
	 * @since 7.8.0
	 *
	 * @return bool
	 */
	public function can_be_resumed() {

		return llms_parse_bool( $this->get( 'can_be_resumed' ) ) && ! $this->has_time_limit();
	}

	/**
	 * Determine if a student can resume the quiz.
	 *
	 * A student can resume the quiz only if their latest attempt can be resumed.
	 *
	 * @since 7.8.0
	 *
	 * @param int $user_id Optional. WP User ID, none supplied uses current user. Default `null`.
	 * @return bool
	 */
	public function can_be_resumed_by_student( $user_id = null ) {

		$can_be_resumed_by_student = false;

		$student = llms_get_student( $user_id );
		if ( $student ) {
			$last_attempt              = $student->quizzes()->get_last_attempt( $this->get( 'id' ) );
			$can_be_resumed_by_student = $last_attempt && $last_attempt->can_be_resumed();
		}

		return $can_be_resumed_by_student;
	}

	/**
	 * Gets quiz's last attempt key of a user.
	 *
	 * @since 7.8.0
	 *
	 * @param int $user_id Optional. WP User ID, none supplied uses current user. Default `null`.
	 * @return string|bool
	 */
	public function get_student_last_attempt_key( $user_id = null ) {

		$student          = llms_get_student( $user_id );
		$last_attempt_key = false;

		if ( $student ) {
			$last_attempt = $student->quizzes()->get_last_attempt( $this->get( 'id' ) );
			if ( $last_attempt ) {
				$last_attempt_key = $last_attempt->get_key();
			}
		}

		return $last_attempt_key;
	}

	/**
	 * Determine if a student can take the quiz.
	 *
	 * @since 3.0.0
	 * @since 3.16.0 Unkwnown.
	 * @since 3.37.2 Added `llms_quiz_is_open` filter hook.
	 *
	 * @param int $user_id Optional. WP User ID, none supplied uses current user. Default `null`.
	 * @return boolean
	 */
	public function is_open( $user_id = null ) {

		$student = llms_get_student( $user_id );
		if ( ! $student ) {
			$quiz_open = false;
		} else {

			$remaining = $student->quizzes()->get_attempts_remaining_for_quiz( $this->get( 'id' ) );

			// string for "unlimited" or number of attempts.
			$quiz_open = ! is_numeric( $remaining ) || $remaining > 0;

			// Check for a passed attempt and disable the quiz.
			if ( $quiz_open && llms_parse_bool( $this->get( 'disable_retake' ) ) ) {
				$passed_attempts = $student->quizzes()->get_attempts_by_quiz(
					$this->get( 'id' ),
					array(
						'status' => array( 'pass' ),
					)
				);

				if ( count( $passed_attempts ) ) {
					$quiz_open = false;
				}
			}
		}

		/**
		 * Filters whether the quiz is open to a student or not.
		 *
		 * @param boolean            $quiz_open Whether the quiz is open.
		 * @param int|null           $user_id   WP User ID, can be `null`.
		 * @param int                $quiz_id   The Quiz id.
		 * @param LLMS_Quiz          $quiz      The LLMS_Quiz instance.
		 * @param LLMS_Student|false $student   LLMS_Student instance or false if user not found.
		 */
		return apply_filters( 'llms_quiz_is_open', $quiz_open, $user_id, $this->get( 'id' ), $this, $student );
	}

	/**
	 * Retrieve an instance of the question manager for the quiz.
	 *
	 * @since 3.16.0
	 *
	 * @return LLMS_Question_Manager
	 */
	public function questions() {
		return new LLMS_Question_Manager( $this );
	}

	/**
	 * Called before data is sorted and returned by $this->toArray().
	 * Extending classes should override this data if custom data should
	 * be added when object is converted to an array or json.
	 *
	 * @since 3.3.0
	 * @since 3.19.2 Unknown.
	 * @since 3.38.0 Only add theme metadata to the quiz array when the `llms_get_quiz_theme_settings` filter is being used.
	 *
	 * @param array $arr Array of data to be serialized.
	 * @return array
	 */
	protected function toArrayAfter( $arr ) {

		$arr['questions'] = array();

		// Builder lazy loads questions via ajax.
		global $llms_builder_lazy_load;
		if ( ! $llms_builder_lazy_load ) {
			foreach ( $this->get_questions() as $question ) {
				$arr['questions'][] = $question->toArray();
			}
		}

		// If theme has legacy support quiz layouts, add theme metadata to the array.
		if ( get_theme_support( 'lifterlms-quizzes' ) && has_filter( 'llms_get_quiz_theme_settings' ) ) {
			$layout = llms_get_quiz_theme_setting( 'layout' );
			if ( $layout ) {
				$arr[ $layout['id'] ] = get_post_meta( $this->get( 'id' ), $layout['id'], true );
			}
		}

		return $arr;
	}

	/**
	 * Get the (points) value of a question.
	 *
	 * @since 3.3.0
	 * @since 3.37.2 Use strict comparison '===' in place of '=='.
	 *
	 * @param int $question_id  WP Post ID of the LLMS_Question.
	 * @return int
	 */
	public function get_question_value( $question_id ) {

		foreach ( $this->get_questions_raw() as $q ) {
			if ( $question_id === $q['id'] ) {
				return absint( $q['points'] );
			}
		}

		return 0;
	}

	/**
	 * Retrieve the array of raw question data from the postmeta table.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	private function get_questions_raw() {

		$q = get_post_meta( $this->get( 'id' ), $this->meta_prefix . 'questions', true );
		return $q ? $q : array();
	}
}
