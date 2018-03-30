<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lesson Progression Actions
 * @since    [version]
 * @version  [version]
 */
class LLMS_Controller_Lesson_Progression {

	/**
	 * Constructor
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'handle_complete_form' ) );
		add_action( 'init', array( $this, 'handle_incomplete_form' ) );

		add_action( 'llms_trigger_lesson_completion', array( $this, 'mark_complete' ), 10, 4 );

	}

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
	 * @since    [version]
	 * @version  [version]
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

			// llms_add_notice( sprintf( __( 'Congratulations! You have completed %s', 'lifterlms' ), get_the_title( $lesson_id ) ) );

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
	 * @since    [version]
	 * @version  [version]
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

}

return new LLMS_Controller_Lesson_Progression();
