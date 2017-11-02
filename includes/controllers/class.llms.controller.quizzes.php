<?php
/**
 * Quiz related con
 *
 * @since   3.9.0
 * @version 3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Controller_Quizzes {

	public function __construct() {

		add_action( 'init', array( $this, 'take_quiz' ) );

	}

	/**
	 * Handle form submission of the "take quiz" button attached to lessons with quizzes
	 * @return  void
	 * @since   1.0.0
	 * @version 3.9.0
	 */
	public function take_quiz() {

		// invalid nonce or the form wasn't submitted
		if ( ! llms_verify_nonce( '_llms_take_quiz_nonce', 'take_quiz', 'POST' ) ) {
			return;
		}

		// check required fields
		if ( ! isset( $_POST['quiz_id'] ) || ! isset( $_POST['associated_lesson'] ) ) {
			return llms_add_notice( __( 'Could not proceed to the quiz because required information was missing.', 'lifterlms' ), 'error' );
		}

		$quiz = absint( $_POST['quiz_id'] );
		$lesson = absint( $_POST['associated_lesson'] );

		try {
			$attempt = LLMS_Quiz_Attempt::init( $quiz, $lesson, get_current_user_id() )->save();
		} catch ( Exception $exception ) {
			return llms_add_notice( $exception->getMessage(), 'error' );
		}

		//redirect user to quiz page
		$redirect = add_query_arg( array(
			'attempt_key' => $attempt->get_key(),
		), get_permalink( $quiz ) );
		wp_redirect( apply_filters( 'lifterlms_lesson_start_quiz_redirect', $redirect ) );
		exit;

	}

}

return new LLMS_Controller_Quizzes();
