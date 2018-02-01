<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Quiz Attempt Forms
 * Grading, Deleting, Etc...
 *
 * @since   [version]
 * @version [version]
 */
class LLMS_Controller_Admin_Quiz_Attempts {

	public function __construct() {

		add_action( 'admin_init', array( $this, 'maybe_run_actions' ) );

	}

	/**
	 * Run actions on form submission
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function maybe_run_actions() {

		if ( ! llms_verify_nonce( '_llms_quiz_attempt_nonce', 'llms_quiz_attempt_actions', 'POST' ) ) {
			return;
		}

		if ( isset( $_POST['llms_quiz_attempt_action'] ) && isset( $_POST['llms_attempt_id'] ) ) {

			$action = $_POST['llms_quiz_attempt_action'];

			$attempt = new LLMS_Quiz_Attempt( absint( $_POST['llms_attempt_id'] ) );

			if ( ! current_user_can( 'edit_post', $attempt->get( 'quiz_id' ) ) ) {
				return;
			}

			if ( 'llms_attempt_recalc' === $action ) {
				$attempt->calculate_grade()->save();
			} elseif ( 'llms_attempt_delete' === $action ) {
				$url = add_query_arg( array(
					'page' => 'llms-reporting',
					'tab' => 'quizzes',
					'quiz_id' => $attempt->get( 'quiz_id' ),
					'stab' => 'attempts',
				), admin_url( 'admin.php' ) );
				$attempt->delete();
				wp_safe_redirect( $url );
			} elseif ( 'llms_attempt_grade' === $action && ( isset( $_POST['remarks'] ) || isset( $_POST['points'] )) ) {
				$this->save_grade( $attempt );
			}
		}

	}

	/**
	 * Saves changes to a quiz
	 * @param    obj     $attempt  LLMS_Quiz_Attempt instance
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	private function save_grade( $attempt ) {

		$remarks = isset( $_POST['remarks'] ) ? (array) $_POST['remarks'] : array();
		$points = isset( $_POST['points'] ) ? (array) $_POST['points'] : array();

		$questions = $attempt->get_questions();
		foreach ( $questions as &$question ) {

			if ( isset( $remarks[ $question['id'] ] ) ) {
				$question['remarks'] = wp_kses_post( nl2br( $remarks[ $question['id'] ] ) );
			}

			if ( isset( $points[ $question['id'] ] ) ) {
				$points = absint( $points[ $question['id'] ] );
				$question['earned'] = $points;
				if ( ( $points / $question['points'] ) >= 0.5 ) {
					$question['correct'] = 'yes';
				} else {
					$question['correct'] = 'no';
				}
			}
		}

		$attempt->set_questions( $questions, true );
		$attempt->calculate_grade()->save();

	}


}

return new LLMS_Controller_Admin_Quiz_Attempts();
