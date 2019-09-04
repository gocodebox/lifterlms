<?php
/**
 * User Login Form Controller
 *
 * @since 3.19.4
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Login
 *
 * @since 3.19.4
 * @since 3.35.0 Sanitize `$_POST` data.
 */
class LLMS_Controller_Login {

	/**
	 * Constructor
	 *
	 * @since 3.19.4
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'login' ) );

	}

	/**
	 * Handle Login Form Submission
	 *
	 * @since 3.19.4
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return   void
	 */
	public function login() {

		if ( ! llms_verify_nonce( '_llms_login_user_nonce', 'llms_login_user' ) ) {
			return;
		}

		$login = LLMS_Person_Handler::login( $_POST );

		// validation or login issues
		if ( is_wp_error( $login ) ) {
			foreach ( $login->get_error_messages() as $msg ) {
				llms_add_notice( $msg, 'error' );
			}
			return;
		}

		$redirect = isset( $_POST['redirect'] ) ? llms_filter_input( INPUT_POST, 'redirect', FILTER_SANITIZE_URL ) : get_permalink( llms_get_page_id( 'myaccount' ) );

		llms_redirect_and_exit( apply_filters( 'lifterlms_login_redirect', $redirect, $login ) );

	}

}

return new LLMS_Controller_Login();
