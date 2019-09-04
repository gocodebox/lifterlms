<?php
/**
 * Quiz Attempt Forms on the admin panel
 *
 * @package LifterLMS/Classes/Controllers
 *
 * @since 3.16.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Quiz Attempt Forms on the admin panel
 *
 * Allows admins to grade, leave remarks, and delete quiz attempts.
 *
 * @since 3.16.0
 * @since 3.30.3 Fixed an issue causing backlashes to be saved around escaped characters when leaving remarks.
 * @since 3.35.0 Sanitize `$_POST` data.
 */
class LLMS_Controller_Admin_Quiz_Attempts {

	public function __construct() {

		add_action( 'admin_init', array( $this, 'maybe_run_actions' ) );

	}

	/**
	 * Run actions on form submission
	 *
	 * @since 3.16.0
	 * @since 3.16.9 Unknown.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return void
	 */
	public function maybe_run_actions() {

		if ( ! llms_verify_nonce( '_llms_quiz_attempt_nonce', 'llms_quiz_attempt_actions', 'POST' ) ) {
			return;
		}

		if ( isset( $_POST['llms_quiz_attempt_action'] ) && isset( $_POST['llms_attempt_id'] ) ) {

			$action  = llms_filter_input( INPUT_POST, 'llms_quiz_attempt_action', FILTER_SANITIZE_STRING );
			$attempt = new LLMS_Quiz_Attempt( absint( $_POST['llms_attempt_id'] ) );

			if ( ! current_user_can( 'edit_post', $attempt->get( 'quiz_id' ) ) ) {
				return;
			}

			if ( 'llms_attempt_delete' === $action ) {
				$url = add_query_arg(
					array(
						'page'    => 'llms-reporting',
						'tab'     => 'quizzes',
						'quiz_id' => $attempt->get( 'quiz_id' ),
						'stab'    => 'attempts',
					),
					admin_url( 'admin.php' )
				);
				$attempt->delete();
				wp_safe_redirect( $url );
			} elseif ( 'llms_attempt_grade' === $action && ( isset( $_POST['remarks'] ) || isset( $_POST['points'] ) ) ) {
				$this->save_grade( $attempt );
			}
		}

	}

	/**
	 * Saves changes to a quiz
	 *
	 * @since 3.16.0
	 * @since 3.30.3 Strip slashes on remarks.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @param LLMS_Quiz_Attempt $attempt Quiz attempt instance.
	 * @return void
	 */
	private function save_grade( $attempt ) {

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in `maybe_run_actions()` method.

		$remarks = isset( $_POST['remarks'] ) ? llms_filter_input( INPUT_POST, 'remarks', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY ) : array();
		$points  = isset( $_POST['points'] ) ? llms_filter_input( INPUT_POST, 'points', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY ) : array();

		$questions = $attempt->get_questions();
		foreach ( $questions as &$question ) {

			if ( isset( $remarks[ $question['id'] ] ) ) {
				$question['remarks'] = wp_kses_post( nl2br( stripslashes( $remarks[ $question['id'] ] ) ) );
			}

			if ( isset( $points[ $question['id'] ] ) ) {
				$earned             = absint( $points[ $question['id'] ] );
				$question['earned'] = $earned;
				if ( ( $earned / $question['points'] ) >= 0.5 ) {
					$question['correct'] = 'yes';
				} else {
					$question['correct'] = 'no';
				}
			}
		}

		// update the attempt with new questions
		$attempt->set_questions( $questions, true );

		// attempt to calculate the grade
		$attempt->calculate_grade()->save();

		// if all questions were graded the grade will have been calculated and we can trigger completion actions
		if ( in_array( $attempt->get( 'status' ), array( 'fail', 'pass' ) ) ) {
			$attempt->do_completion_actions();
		}

		do_action( 'llms_quiz_graded', $attempt->get_student()->get_id(), $attempt->get( 'quiz_id' ), $attempt );

		// phpcs:enable

	}

}

return new LLMS_Controller_Admin_Quiz_Attempts();
