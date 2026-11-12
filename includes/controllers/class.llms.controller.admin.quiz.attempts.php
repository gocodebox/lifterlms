<?php
/**
 * Quiz Attempt Forms on the admin panel
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 3.16.0
 * @version 7.8.0
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
	 * Run actions on form submission.
	 *
	 * @since 3.16.0
	 * @since 3.16.9 Unknown.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 * @since 4.4.4 Made sure to exit after redirecting on attempt deletion.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 7.8.0 Added `llms_quiz_resumable_attempt_action` action and single resumable attempt delete.
	 *
	 * @return void
	 */
	public function maybe_run_actions() {

		if ( ! isset( $_REQUEST['_llms_quiz_attempt_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_llms_quiz_attempt_nonce'] ) ), 'llms_quiz_attempt_actions' ) ) {
			return;
		}

		if ( isset( $_POST['llms_quiz_attempt_action'] ) && isset( $_POST['llms_attempt_id'] ) ) {

			$action  = llms_filter_input( INPUT_POST, 'llms_quiz_attempt_action' );
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
				exit();
			} elseif ( 'llms_attempt_grade' === $action && ( isset( $_POST['remarks'] ) || isset( $_POST['points'] ) ) ) {
				$this->save_grade( $attempt );
			} elseif ( in_array( $action, array( 'llms_attempt_late_penalty', 'llms_attempt_late_penalty_remove' ), true ) ) {
				$this->save_late_penalty( $attempt, 'llms_attempt_late_penalty_remove' === $action );
			} elseif ( 'llms_disable_resume_attempt' === $action ) {
				$attempt->set( 'can_be_resumed', false );
				$attempt->save();
				$attempt->end();
			}
		}

		if ( isset( $_POST['llms_quiz_resumable_attempt_action'] ) ) {

			$action = llms_filter_input( INPUT_POST, 'llms_quiz_resumable_attempt_action' );

			if ( 'llms_clear_resumable_attempts' === $action ) {

				$quiz_id = llms_filter_input( INPUT_POST, 'llms_quiz_id' );

				if ( ! current_user_can( 'edit_post', $quiz_id ) ) {
					return;
				}

				$resumable_attempts = $this->get_resumable_attempts( $quiz_id );

				foreach ( $resumable_attempts as $attempt_id ) {
					$attempt = new LLMS_Quiz_Attempt( $attempt_id );
					$attempt->set( 'can_be_resumed', false );
					$attempt->save();

					$attempt->end();
				}
			}
		}
	}

	/**
	 * Get resumable attempts for a quiz.
	 *
	 * @since 7.8.0
	 *
	 * @param int $quiz_id Quiz ID.
	 */
	public function get_resumable_attempts( $quiz_id ) {

		// Query to get all resumable attempts.
		$query = new LLMS_Query_Quiz_Attempt(
			array(
				'quiz_id'        => $quiz_id,
				'can_be_resumed' => true,
				'status'         => 'incomplete',
			)
		);

		return wp_list_pluck( $query->get_results(), 'id' );
	}

	/**
	 * Saves changes to a quiz
	 *
	 * @since 3.16.0
	 * @since 3.30.3 Strip slashes on remarks.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 * @since 4.4.4 Use strict type comparisons where needed.
	 *
	 * @param LLMS_Quiz_Attempt $attempt Quiz attempt instance.
	 * @return void
	 */
	private function save_grade( $attempt ) {

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in `maybe_run_actions()` method.

		$remarks = isset( $_POST['remarks'] ) ? llms_filter_input_sanitize_string( INPUT_POST, 'remarks', array( FILTER_REQUIRE_ARRAY ) ) : array();
		$points  = isset( $_POST['points'] ) ? llms_filter_input_sanitize_string( INPUT_POST, 'points', array( FILTER_REQUIRE_ARRAY ) ) : array();

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

		// Update the attempt with new questions.
		$attempt->set_questions( $questions, true );

		$this->regrade( $attempt );

		// phpcs:enable
	}

	/**
	 * Saves the late penalty on a quiz attempt and regrades it.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz_Attempt $attempt Quiz attempt instance.
	 * @param bool              $remove  Whether to remove the penalty instead of saving the posted one.
	 * @return void
	 */
	private function save_late_penalty( $attempt, $remove = false ) {

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in `maybe_run_actions()` method.

		$penalty = 0.0;
		$note    = '';
		if ( ! $remove ) {
			$penalty = isset( $_POST['llms_late_penalty'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['llms_late_penalty'] ) ) : 0;
			$penalty = llms()->grades()->round( max( 0, min( $penalty, 100 ) ) );
			$note    = $penalty && isset( $_POST['llms_late_penalty_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['llms_late_penalty_note'] ) ) : '';
		}

		// phpcs:enable

		if ( $penalty === $attempt->get_late_penalty() && $note === (string) $attempt->get( 'late_penalty_note' ) ) {
			return;
		}

		$attempt->set( 'late_penalty', $penalty );
		$attempt->set( 'late_penalty_note', $note );

		$this->regrade( $attempt );
	}

	/**
	 * Recalculates a quiz attempt's grade after an instructor changes it, then runs the same actions as a quiz submission.
	 *
	 * Completion actions (such as the quiz passed and quiz failed notifications) only run once every question is graded.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Quiz_Attempt $attempt Quiz attempt instance.
	 * @return void
	 */
	private function regrade( $attempt ) {

		// Attempt to calculate the grade.
		$attempt->calculate_grade()->save();

		// If all questions were graded the grade will have been calculated and we can trigger completion actions.
		if ( in_array( $attempt->get( 'status' ), array( 'fail', 'pass' ), true ) ) {
			$attempt->do_completion_actions();
		}

		do_action( 'llms_quiz_graded', $attempt->get_student()->get_id(), $attempt->get( 'quiz_id' ), $attempt );
	}
}

return new LLMS_Controller_Admin_Quiz_Attempts();
