<?php
/**
 * LLMS_Controller_Quizzes class file
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 3.9.0
 * @version 5.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Quiz related form controller
 *
 * @since 3.9.0
 * @since 5.0.0 Removed previously deprecated method `LLMS_Controller_Quizzes::take_quiz()`.
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
	 * @since 5.1.0 Use a deep orphan check to determine if the quiz can be deleted.
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
			if ( $quiz && ( $quiz->is_orphan( true ) || ! $quiz->get_course() ) ) {
				return wp_delete_post( $id, true );
			}
		}

		return false;

	}

}

return new LLMS_Controller_Quizzes();
