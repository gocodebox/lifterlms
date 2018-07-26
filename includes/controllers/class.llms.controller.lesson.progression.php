<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lesson Progression Actions
 * @since    3.17.1
 * @version  3.17.1
 */
class LLMS_Controller_Lesson_Progression {

	/**
	 * Constructor
	 * @since    3.17.1
	 * @version  3.17.1
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'handle_complete_form' ) );
		add_action( 'init', array( $this, 'handle_incomplete_form' ) );

		add_action( 'lifterlms_quiz_completed', array( $this, 'quiz_complete' ), 10, 3 );
		add_filter( 'llms_allow_lesson_completion', array( $this, 'quiz_maybe_prevent_lesson_completion' ), 10, 5 );

		add_action( 'llms_trigger_lesson_completion', array( $this, 'mark_complete' ), 10, 4 );

	}

	/**
	 * Handle completion of lesson via `llms_trigger_lesson_completion` action
	 * @param    int        $user_id    User ID
	 * @param    int        $lesson_id  Lesson ID
	 * @param    string     $trigger    Optional trigger description string
	 * @param    array      $args       Optional arguments
	 * @return   void
	 * @since    3.17.1
	 * @version  3.17.1
	 */
	public function mark_complete( $user_id, $lesson_id, $trigger = '', $args = array() ) {

		if ( apply_filters( 'llms_allow_lesson_completion', true, $user_id, $lesson_id, $trigger, $args ) ) {

			llms_mark_complete( $user_id, $lesson_id, 'lesson', $trigger );

		}

	}

	/**
	 * Mark Lesson as complete
	 * Complete Lesson form post
	 * Marks lesson as complete and returns completion message to user
	 * Autoadvances to next lesson if completion is succesful
	 * @return   void
	 * @since    3.17.1
	 * @version  3.17.1
	 */
	public function handle_complete_form() {

		if ( ! llms_verify_nonce( '_wpnonce', 'mark_complete', 'POST' ) ) {
			return;
		}

		// required fields
		if ( ! isset( $_POST['mark_complete'] ) || ! isset( $_POST['mark-complete'] ) ) {
			return;
		}

		$lesson_id = absint( $_POST['mark-complete'] );
		if ( ! $lesson_id || ! is_numeric( $lesson_id ) ) {

			llms_add_notice( __( 'An error occurred, please try again.', 'lifterlms' ), 'error' );

		} else {

			do_action( 'llms_trigger_lesson_completion', get_current_user_id(), $lesson_id, 'lesson_' . $lesson_id );

			if ( apply_filters( 'lifterlms_autoadvance', true ) ) {

				$lesson = new LLMS_Lesson( $lesson_id );
				$next_lesson_id = $lesson->get_next_lesson();
				if ( $next_lesson_id ) {

					wp_redirect( apply_filters( 'llms_lesson_complete_redirect', get_permalink( $next_lesson_id ) ) );
					exit;

				}
			}
		}

	}

	/**
	 * Mark Lesson as incomplete
	 * Incomplete Lesson form post
	 * Marks lesson as incomplete and returns incompletion message to user
	 * @return   void
	 * @since    3.17.1
	 * @version  3.17.1
	 */
	public function handle_incomplete_form() {

		if ( ! llms_verify_nonce( '_wpnonce', 'mark_incomplete', 'POST' ) ) {
			return;
		}

		// required fields
		if ( ! isset( $_POST['mark_incomplete'] ) || ! isset( $_POST['mark-incomplete'] ) ) {
			return;
		}

		$lesson_id = absint( $_POST['mark-incomplete'] );
		if ( ! $lesson_id || ! is_numeric( $lesson_id ) ) {
			llms_add_notice( __( 'An error occurred, please try again.', 'lifterlms' ), 'error' );
		} else {

			// mark incomplete
			$incompleted = llms_mark_incomplete( get_current_user_id(), $lesson_id, 'lesson', 'lesson_' . $lesson_id );

			// if $incompleted is 'yes'
			if ( strcmp( $incompleted, 'yes' ) === 0 ) {

				llms_add_notice( sprintf( __( '%s is now incomplete.', 'lifterlms' ), get_the_title( $lesson_id ) ) );

			}
		}

	}

	/**
	 * Trigger lesson completion when a quiz is completed
	 * @param    int     $student_id  WP User ID
	 * @param    int     $quiz_id     WP Post ID of the quiz
	 * @param    obj     $attempt     Instance of the LLMS_Quiz_Attempt
	 * @return   void
	 * @since    3.17.1
	 * @version  3.17.1
	 */
	public function quiz_complete( $student_id, $quiz_id, $attempt ) {

		do_action( 'llms_trigger_lesson_completion', $student_id, $attempt->get( 'lesson_id' ), 'quiz_' . $quiz_id, array(
			'attempt' => $attempt,
		) );

	}

	/**
	 * Before a lesson is marked as complete, check if all the lesson's quiz requirements are met
	 * @filter   llms_allow_lesson_completion
	 * @param    bool     $allow_completion  whether or not to allow completion (true by default, false if something else has already prevented)
	 * @param    int      $user_id           WP User ID of the student completing the lesson
	 * @param    int      $lesson_id         WP Post ID of the lesson to be completed
	 * @param    string   $trigger           text string to record the reason why the lesson is being completed
	 * @param    array    $args              optional additional arguements from the triggering function
	 * @return   bool
	 * @since    3.17.1
	 * @version  3.17.1
	 */
	public function quiz_maybe_prevent_lesson_completion( $allow_completion, $user_id, $lesson_id, $trigger, $args ) {

		// if allow completion is already false, we don't need to run any quiz checks
		if ( ! $allow_completion ) {
			return $allow_completion;
		}

		$lesson = llms_get_post( $lesson_id );
		$passing_required = llms_parse_bool( $lesson->get( 'require_passing_grade' ) );

		// if the lesson is being completed by a quiz
		if ( 0 === strpos( $trigger, 'quiz_' ) ) {

			// passing is required AND the attempt was a failure
			if ( $passing_required && ! $args['attempt']->is_passing() ) {
				$allow_completion = false;
			}
		} elseif ( $lesson->is_quiz_enabled() ) {

			$quiz_id = $lesson->get( 'quiz' );
			$student = llms_get_student( $user_id );
			$attempt = $student->quizzes()->get_best_attempt( $quiz_id );

			// passing is not required but there's not attempts yet
			// at least one attempt (passing or otherwise) is required!
			if ( ! $passing_required && ! $attempt ) {
				$allow_completion = false;

				// passing is required and there's no attempts or the best attempt is not passing
			} elseif ( $passing_required && ( ! $attempt || ! $attempt->is_passing() ) ) {
				$allow_completion = false;
			}
		}

		return $allow_completion;

	}

}

return new LLMS_Controller_Lesson_Progression();
