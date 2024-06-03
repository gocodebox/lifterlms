<?php
/**
 * Lesson Progression Actions
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 3.17.1
 * @version 6.10.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Lesson_Progression class
 *
 * @since 3.17.1
 */
class LLMS_Controller_Lesson_Progression {

	/**
	 * Constructor
	 *
	 * @since 3.17.1
	 * @since 3.29.0 Unknown
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'handle_admin_managment_forms' ) );

		add_action( 'init', array( $this, 'handle_complete_form' ) );
		add_action( 'init', array( $this, 'handle_incomplete_form' ) );

		add_action( 'lifterlms_quiz_completed', array( $this, 'quiz_complete' ), 10, 3 );
		add_filter( 'llms_allow_lesson_completion', array( $this, 'quiz_maybe_prevent_lesson_completion' ), 10, 5 );

		add_action( 'llms_trigger_lesson_completion', array( $this, 'mark_complete' ), 10, 4 );

	}

	/**
	 * Retrieve a lesson ID from form data for the mark complete / incomplete forms
	 *
	 * @since 3.29.0
	 *
	 * @param string $action Form action, either "complete" or "incomplete".
	 * @return int|null Returns `null` when either required post fields are missing or if the lesson_id is non-numeric, int (lesson id) on success.
	 */
	private function get_lesson_id_from_form_data( $action ) {

		if ( ! llms_verify_nonce( '_wpnonce', 'mark_' . $action, 'POST' ) ) {
			return null;
		}

		$submitted = llms_filter_input( INPUT_POST, 'mark_' . $action );
		$lesson_id = llms_filter_input( INPUT_POST, 'mark-' . $action );

		// Required fields.
		if ( is_null( $submitted ) || is_null( $lesson_id ) ) {
			return null;
		}

		$lesson_id = absint( $lesson_id );

		// Invalid lesson ID.
		if ( ! $lesson_id || ! is_numeric( $lesson_id ) ) {

			llms_add_notice( __( 'An error occurred, please try again.', 'lifterlms' ), 'error' );
			return null;

		}

		return $lesson_id;

	}

	/**
	 * Handle form submission from the Student -> Courses -> Course table where admins can toggle completion of lessons for a student.
	 *
	 * @since 3.29.0
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 6.10.0 Check the current user can edit the lesson they're going to mark complete/incomplete.
	 *
	 * @return void
	 */
	public function handle_admin_managment_forms() {

		if ( ! llms_verify_nonce( 'llms-admin-progression-nonce', 'llms-admin-lesson-progression', 'POST' ) ) {
			return;
		}

		$action     = llms_filter_input( INPUT_POST, 'llms-lesson-action' );
		$lesson_id  = absint( llms_filter_input( INPUT_POST, 'lesson_id' ) );
		$student_id = absint( llms_filter_input( INPUT_POST, 'student_id' ) );

		// Missing required data.
		if ( empty( $action ) || empty( $lesson_id ) || empty( $student_id ) || ! current_user_can( 'edit_post', $lesson_id ) ) {
			return;
		}

		$trigger = 'admin_' . get_current_user_id();

		if ( 'complete' === $action ) {
			$this->mark_complete( $student_id, $lesson_id, $trigger );
		} elseif ( 'incomplete' === $action ) {
			llms_mark_incomplete( $student_id, $lesson_id, 'lesson', $trigger );
		}

	}

	/**
	 * Mark Lesson as complete
	 *
	 * + Complete Lesson form post.
	 * + Marks lesson as complete and returns completion message to user.
	 * + Autoadvances to next lesson if completion is successful.
	 *
	 * @since 3.17.1
	 * @since 3.29.0 Unknown.
	 *
	 * @return void
	 */
	public function handle_complete_form() {

		$lesson_id = $this->get_lesson_id_from_form_data( 'complete' );

		if ( is_null( $lesson_id ) ) {
			return;
		}

		/**
		 * Filter to modify the user id instead of current logged in user id.
		 *
		 * @param int $user_id User id to mark lesson as complete.
		 *
		 * @since 5.4.0
		 */
		$user_id = apply_filters( 'llms_lesson_completion_user_id', get_current_user_id() );

		do_action( 'llms_trigger_lesson_completion', $user_id, $lesson_id, 'lesson_' . $lesson_id );

		if ( apply_filters( 'lifterlms_autoadvance', true ) ) {

			$lesson         = new LLMS_Lesson( $lesson_id );
			$next_lesson_id = $lesson->get_next_lesson();
			if ( $next_lesson_id ) {

				wp_redirect( apply_filters( 'llms_lesson_complete_redirect', get_permalink( $next_lesson_id ) ) );
				exit;

			}
		}

	}

	/**
	 * Mark Lesson as incomplete
	 *
	 * + Incomplete Lesson form post.
	 * + Marks lesson as incomplete and returns incompletion message to user.
	 *
	 * @since 3.17.1
	 * @since 3.29.0 Unknown.
	 *
	 * @return void
	 */
	public function handle_incomplete_form() {

		$lesson_id = $this->get_lesson_id_from_form_data( 'incomplete' );

		if ( is_null( $lesson_id ) ) {
			return;
		}

		/**
		 * Filter to modify the user id instead of current logged in user id.
		 *
		 * @param int $user_id User id to mark lesson as incomplete.
		 *
		 * @since 5.4.0
		 */
		$user_id = apply_filters( 'llms_lesson_incomplete_user_id', get_current_user_id() );

		// Mark incomplete and add a notice on success.
		if ( llms_mark_incomplete( $user_id, $lesson_id, 'lesson', 'lesson_' . $lesson_id ) ) {
			// Translators: %s is the title of the lesson.
			llms_add_notice( sprintf( __( 'The lesson %s is now marked as incomplete.', 'lifterlms' ), get_the_title( $lesson_id ) ) );
		}

	}

	/**
	 * Handle completion of lesson via `llms_trigger_lesson_completion` action
	 *
	 * @since 3.17.1
	 * @since 3.29.0 Unknown.
	 *
	 * @param int    $user_id   User ID.
	 * @param int    $lesson_id Lesson ID.
	 * @param string $trigger   Optional trigger description string.
	 * @param array  $args      Optional arguments.
	 * @return void
	 */
	public function mark_complete( $user_id, $lesson_id, $trigger = '', $args = array() ) {

		if ( llms_allow_lesson_completion( $user_id, $lesson_id, $trigger, $args ) ) {

			llms_mark_complete( $user_id, $lesson_id, 'lesson', $trigger );

		}

	}

	/**
	 * Trigger lesson completion when a quiz is completed
	 *
	 * @since 3.17.1
	 *
	 * @param int $student_id WP User ID.
	 * @param int $quiz_id    WP Post ID of the quiz.
	 * @param obj $attempt    Instance of the LLMS_Quiz_Attempt.
	 * @return void
	 */
	public function quiz_complete( $student_id, $quiz_id, $attempt ) {

		do_action(
			'llms_trigger_lesson_completion',
			$student_id,
			$attempt->get( 'lesson_id' ),
			'quiz_' . $quiz_id,
			array(
				'attempt' => $attempt,
			)
		);

	}

	/**
	 * Before a lesson is marked as complete, check if all the lesson's quiz requirements are met
	 *
	 * @since 3.17.1
	 *
	 * @param bool   $allow_completion Whether or not to allow completion (true by default, false if something else has already prevented).
	 * @param int    $user_id          WP User ID of the student completing the lesson.
	 * @param int    $lesson_id        WP Post ID of the lesson to be completed.
	 * @param string $trigger          Text string to record the reason why the lesson is being completed.
	 * @param array  $args             Optional additional arguments from the triggering function.
	 * @return bool
	 */
	public function quiz_maybe_prevent_lesson_completion( $allow_completion, $user_id, $lesson_id, $trigger, $args ) {

		// If allow completion is already false, we don't need to run any quiz checks.
		if ( ! $allow_completion ) {
			return $allow_completion;
		}

		$lesson           = llms_get_post( $lesson_id );
		$passing_required = llms_parse_bool( $lesson->get( 'require_passing_grade' ) );

		// If the lesson is being completed by a quiz.
		if ( 0 === strpos( $trigger, 'quiz_' ) ) {

			// Passing is required AND the attempt was a failure.
			if ( $passing_required && ! $args['attempt']->is_passing() ) {
				$allow_completion = false;
			}
		} elseif ( $lesson->is_quiz_enabled() ) {

			$quiz_id = $lesson->get( 'quiz' );
			$student = llms_get_student( $user_id );
			$attempt = $student->quizzes()->get_best_attempt( $quiz_id );

			// Passing is not required but there's not attempts yet.
			// At least one attempt (passing or otherwise) is required!.
			if ( ! $passing_required && ! $attempt ) {
				$allow_completion = false;

				// Passing is required and there's no attempts or the best attempt is not passing.
			} elseif ( $passing_required && ( ! $attempt || ! $attempt->is_passing() ) ) {
				$allow_completion = false;
			}
		}

		return $allow_completion;

	}

}

return new LLMS_Controller_Lesson_Progression();
