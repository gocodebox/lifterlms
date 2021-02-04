<?php
/**
 * Quiz related controller
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 3.9.0
 * @version 4.14.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Quizzes class
 *
 * @since 3.9.0
 * @since 3.37.8 Add admin reporting actions handler.
 */
class LLMS_Controller_Quizzes {

	/**
	 * Constructor
	 *
	 * @since 3.9.0
	 * @since 3.37.8 Add reporting actions handler action.
	 * @since 4.14.0 Remove `add_action()` for deprecated `take_quiz()` method.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'maybe_handle_reporting_actions' ) );

	}

	/**
	 * Handle quiz reporting screen actions buttons
	 *
	 * On the quiz reporting screen this allows orphaned quizzes to be deleted.
	 *
	 * @since 3.37.8
	 *
	 * @return null|false|WP_Post `null` if the form wasn't submitted or the nonce couldn't be verified.
	 *                            `false` if an error was encountered.
	 *                            `WP_Post` of the deleted quiz on success.
	 */
	public function maybe_handle_reporting_actions() {

		if ( ! llms_verify_nonce( '_llms_quiz_actions_nonce', 'llms-quiz-actions' ) ) {
			return null;
		}

		$id = llms_filter_input( INPUT_POST, 'llms_del_quiz', FILTER_SANITIZE_NUMBER_INT );
		if ( $id && 'llms_quiz' === get_post_type( $id ) ) {
			$quiz = llms_get_post( $id );
			if ( $quiz && ( $quiz->is_orphan() || ! $quiz->get_course() ) ) {
				return wp_delete_post( $id, true );
			}
		}

		return false;

	}

	/**
	 * Handle form submission of the "take quiz" button attached to lessons with quizzes
	 *
	 * @since 1.0.0
	 * @since 3.9.0 Unknown.
	 * @deprecated 4.14.0 `LLMS_Controller_Quizzes::take_quiz()` is deprecated in favor of `LLMS_AJAX_Handler::quiz_start()`.
	 *
	 * @return void
	 */
	public function take_quiz() {

		_deprecated_function( 'LLMS_Controller_Quizzes::take_quiz()', '4.14.0', 'LLMS_AJAX_Handler::quiz_start()' );

		// Invalid nonce or the form wasn't submitted.
		if ( ! llms_verify_nonce( '_llms_take_quiz_nonce', 'take_quiz', 'POST' ) ) {
			return;
		}

		// Check required fields.
		if ( ! isset( $_POST['quiz_id'] ) || ! isset( $_POST['associated_lesson'] ) ) {
			return llms_add_notice( __( 'Could not proceed to the quiz because required information was missing.', 'lifterlms' ), 'error' );
		}

		$quiz   = absint( $_POST['quiz_id'] );
		$lesson = absint( $_POST['associated_lesson'] );

		try {
			$attempt = LLMS_Quiz_Attempt::init( $quiz, $lesson, get_current_user_id() )->save();
		} catch ( Exception $exception ) {
			return llms_add_notice( $exception->getMessage(), 'error' );
		}

		// Redirect user to quiz page.
		$redirect = add_query_arg(
			array(
				'attempt_key' => $attempt->get_key(),
			),
			get_permalink( $quiz )
		);
		wp_redirect( apply_filters( 'lifterlms_lesson_start_quiz_redirect', $redirect ) );
		exit;

	}

}

return new LLMS_Controller_Quizzes();
