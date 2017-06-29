<?php
/**
 * User Account Edit Forms
 *
 * @since   3.7.0
 * @version 3.9.5
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Controller_Account {

	public function __construct() {

		add_action( 'init', array( $this, 'update' ) );
		add_action( 'init', array( $this, 'lost_password' ) );
		add_action( 'init', array( $this, 'reset_password' ) );
		add_action( 'init', array( $this, 'cancel_subscription' ) );

	}

	/**
	 * Lets student cancel recurring access plan subscriptions from the student dashboard view order screen
	 * @return   void
	 * @since    3.10.0
	 * @version  3.10.0
	 */
	public function cancel_subscription() {

		// invalid nonce or the form wasn't submitted
		if ( ! llms_verify_nonce( '_cancel_sub_nonce', 'llms_cancel_subscription', 'POST' ) ) {
			return;
		}

		// verify required field
		if ( empty( $_POST['order_id'] ) ) {
			return llms_add_notice( __( 'Something went wrong. Please try again.', 'lifterlms' ), 'error' );
		}

		$order = llms_get_post( $_POST['order_id'] );
		$uid = get_current_user_id();

		if ( $uid != $order->get( 'user_id' ) ) {
			return llms_add_notice( __( 'Something went wrong. Please try again.', 'lifterlms' ), 'error' );
		}

		$order->set_status( 'cancelled' );
		$order->add_note( __( 'Cancelled by student from account page.', 'lifterlms' ) );

	}

	/**
	 * Handle submission of user account edit form
	 * @return   void
	 * @since    3.7.0
	 * @version  3.7.0
	 */
	public function update() {

		if ( 'POST' !== strtoupper( getenv( 'REQUEST_METHOD' ) ) || empty( $_POST['action'] ) || 'llms_update_person' !== $_POST['action'] || empty( $_POST['_wpnonce'] ) ) { return; }

		wp_verify_nonce( $_POST['_wpnonce'], 'llms_update_person' );

		// no user logged in, can't update!
		// this shouldn't happen but let's check anyway
		if ( ! get_current_user_id() ) {
			return llms_add_notice( __( 'Please log in and try again.', 'lifterlms' ), 'error' );
		} // End if().
		else {
			$person_id = llms_update_user( $_POST, 'account' );
		}

		// validation or update issues
		if ( is_wp_error( $person_id ) ) {
			foreach ( $person_id->get_error_messages() as $msg ) {
				llms_add_notice( $msg, 'error' );
			}
			return;
		} // End if().
		elseif ( ! is_numeric( $person_id ) ) {

			return llms_add_notice( __( 'An unknown error occurred when attempting to create an account, please try again.', 'lirterlms' ), 'error' );

		} else {

			llms_add_notice( __( 'Your account information has been saved.', 'lifterlms' ), 'success' );

			// handle redirect
			wp_safe_redirect( apply_filters( 'lifterlms_update_account_redirect', llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) ) ) );
			exit;

		}

	}

	/**
	 * Handle form submission of the Lost Password form
	 * This is the form that sends a password recovery email with a link to reset the password
	 * @return   void
	 * @since    3.8.0
	 * @version  3.9.5
	 */
	public function lost_password() {

		// invalid nonce or the form wasn't submitted
		if ( ! llms_verify_nonce( '_lost_password_nonce', 'llms_lost_password', 'POST' ) ) {
			return;
		}

		// verify required field
		if ( empty( $_POST['llms_login'] ) ) {
			return llms_add_notice( __( 'Enter a username or e-mail address.', 'lifterlms' ), 'error' );
		}

		$login = trim( $_POST['llms_login'] );

		// always check email
		$get_by = array( 'email' );
		// add login if username generation is disabled (eg users create their own usernames)
		if ( 'no' === get_option( 'lifterlms_registration_generate_username' ) ) {
			$get_by[] = 'login';
		}

		$user = null;
		// check each field to find the user
		foreach ( $get_by as $field ) {
			$user = get_user_by( $field, $login );
			// if we find a user skip the next check
			if ( $user ) {
				break;
			}
		}

		// if we don't have a user return an error
		if ( ! $user ) {
			return llms_add_notice( __( 'Invalid username or e-mail address.', 'lifterlms' ), 'error' );
		}

		do_action( 'retrieve_password', $user->user_login ); // wp core hook

		// ensure that password resetting is allowed based on core filters & settings
		$allow = apply_filters( 'allow_password_reset', true, $user->ID ); // wp core filter

		if ( ! $allow ) {

			return llms_add_notice( __( 'Password reset is not allowed for this user', 'lifterlms' ), 'error' );

		} elseif ( is_wp_error( $allow ) ) {

			return llms_add_notice( $allow->get_error_message(), 'error' );

		}

		$key = llms_set_user_password_rest_key( $user->ID );

		// setup the email
		$email = LLMS()->mailer()->get_email( 'reset_password', array(
			'key' => $key,
			'user' => $user,
			'login_display' => 'email' === $get_by ? $user->user_email : $user->user_login,
		) );

		// send the email
		if ( $email ) {

			if ( $email->send() ) {
				return llms_add_notice( __( 'Check your e-mail for the confirmation link.', 'lifterlms' ) );
			}
		}

		return llms_add_notice( __( 'Unable to reset password due to an unknown error. Please try again.', 'lifterlms' ), 'error' );

	}

	/**
	 * Handle form submission of the Reset Password form
	 * This is the form that actually updates a users password
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function reset_password() {

		// invalid nonce or the form wasn't submitted
		if ( ! llms_verify_nonce( '_reset_password_nonce', 'llms_reset_password', 'POST' ) ) {
			return;
		}

		$valid = LLMS_Person_Handler::validate_fields( $_POST, 'reset_password' );

		// validation or registration issues
		if ( is_wp_error( $valid ) ) {
			foreach ( $valid->get_error_messages() as $msg ) {
				llms_add_notice( $msg, 'error' );
			}
			return;
		}

		$login = trim( sanitize_text_field( $_POST['llms_reset_login'] ) );

		if ( ! llms_verify_password_reset_key( trim( sanitize_text_field( $_POST['llms_reset_key'] ) ), $login ) ) {
			return llms_add_notice( __( 'Invalid Key', 'lifterlms' ), 'error' );
		}

		$pass = $_POST['password'];
		$user = get_user_by( 'login', $login );

		if ( ! $user ) {
			return llms_add_notice( __( 'Invalid Key', 'lifterlms' ), 'error' );
		}

		do_action( 'password_reset', $user, $pass );

		wp_set_password( $pass, $user->ID );

		wp_password_change_notification( $user );

		llms_add_notice( sprintf( __( 'Your password has been updated. %1$sClick here to login%2$s', 'lifterlms' ), '<a href="' . esc_url( llms_get_page_url( 'myaccount' ) ) . '">', '</a>' ) );

	}

}

return new LLMS_Controller_Account();
