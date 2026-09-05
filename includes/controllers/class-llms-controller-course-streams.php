<?php
/**
 * Course Streams Controller.
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Course_Streams class.
 *
 * @since [version]
 */
class LLMS_Controller_Course_Streams {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'handle_stream_form' ) );
	}

	/**
	 * Handle stream selection form submission.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function handle_stream_form() {

		if ( ! isset( $_POST['llms_change_stream'] ) ) {
			return;
		}

		if ( ! isset( $_POST['llms_change_stream_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['llms_change_stream_nonce'] ) ), 'llms_change_stream' ) ) {
			return;
		}

		$course_id = absint( llms_filter_input( INPUT_POST, 'llms_stream_course_id', FILTER_SANITIZE_NUMBER_INT ) );
		$stream_id = llms_filter_input_sanitize_string( INPUT_POST, 'llms_stream_id' );
		$user_id   = get_current_user_id();

		if ( ! $user_id || ! $course_id || ! $stream_id || ! llms_is_user_enrolled( $user_id, $course_id ) ) {
			return;
		}

		llms_set_student_stream( $user_id, $course_id, $stream_id );

		$redirect = llms_filter_input( INPUT_POST, 'llms_stream_redirect', FILTER_UNSAFE_RAW );
		$redirect = $redirect ? wp_validate_redirect( esc_url_raw( wp_unslash( $redirect ) ), false ) : false;
		if ( ! $redirect ) {
			$redirect = get_permalink( $course_id );
		}

		if ( $redirect ) {
			llms_redirect_and_exit( $redirect );
		}
	}
}

return new LLMS_Controller_Course_Streams();
