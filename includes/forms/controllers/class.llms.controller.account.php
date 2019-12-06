<?php
/**
 * User Account Edit Forms
 *
 * @since 3.7.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Account class.
 *
 * @since 3.7.0
 * @since 3.35.0 Sanitize `$_POST` data.
 */
class LLMS_Controller_Account {

	/**
	 * Constructor
	 *
	 * @since    3.7.0
	 * @version  3.10.0
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'update' ) );
		add_action( 'init', array( $this, 'lost_password' ) );
		add_action( 'init', array( $this, 'reset_password' ) );
		add_action( 'init', array( $this, 'cancel_subscription' ) );

	}

	/**
	 * Lets student cancel recurring access plan subscriptions from the student dashboard view order screen
	 *
	 * @since 3.10.0
	 * @since 3.19.0 Unknown.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return   void
	 */
	public function cancel_subscription() {

		// invalid nonce or the form wasn't submitted
		if ( ! llms_verify_nonce( '_cancel_sub_nonce', 'llms_cancel_subscription', 'POST' ) ) {
			return;
		} elseif ( empty( $_POST['order_id'] ) ) {
			return llms_add_notice( __( 'Something went wrong. Please try again.', 'lifterlms' ), 'error' );
		}

		$order = llms_get_post( llms_filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT ) );
		$uid   = get_current_user_id();

		if ( ! $order || $uid != $order->get( 'user_id' ) ) {
			return llms_add_notice( __( 'Something went wrong. Please try again.', 'lifterlms' ), 'error' );
		}

		$note = __( 'Subscription cancelled by student from account page.', 'lifterlms' );

		// active subscriptions move to pending-cancel
		// all other statuses are cancelled immediately
		if ( 'llms-active' === $order->get( 'status' ) ) {
			$new_status = 'pending-cancel';
			$note      .= ' ' . __( 'Enrollment will be cancelled at the end of the prepaid period.', 'lifterlms' );
		} else {
			$new_status = 'cancelled';
		}

		$order->set_status( $new_status );
		$order->add_note( $note );

		do_action( 'llms_subscription_cancelled_by_student', $order, $uid );

	}

	/**
	 * Handle submission of user account edit form
	 *
	 * @return   void
	 * @since    3.7.0
	 * @version  3.24.0
	 */
	public function update() {

		if ( ! llms_verify_nonce( '_llms_update_person_nonce', 'llms_update_person' ) ) {
			return;
		}

		do_action( 'llms_before_user_account_update_submit' );

		// no user logged in, can't update!
		// this shouldn't happen but let's check anyway
		if ( ! get_current_user_id() ) {
			return llms_add_notice( __( 'Please log in and try again.', 'lifterlms' ), 'error' );
		}

		$person_id = llms_update_user( $_POST, 'account' );
		// validation or update issues
		if ( is_wp_error( $person_id ) ) {

			foreach ( $person_id->get_error_messages() as $msg ) {
				llms_add_notice( $msg, 'error' );
			}
			return;

		} elseif ( ! is_numeric( $person_id ) ) {

			return llms_add_notice( __( 'An unknown error occurred when attempting to create an account, please try again.', 'lifterlms' ), 'error' );

		} else {

			llms_add_notice( __( 'Your account information has been saved.', 'lifterlms' ), 'success' );

			// handle redirect
			llms_redirect_and_exit( apply_filters( 'lifterlms_update_account_redirect', llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) ) ) );

		}

	}

	/**
	 * Handle form submission of the Lost Password form
	 *
	 * This is the form that sends a password recovery email with a link to reset the password.
	 *
	 * @since 3.8.0
	 * @since 3.9.5 Unknown.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 * @since [version]
	 *
	 * @return WP_Error|true
	 */
	public function lost_password() {

		// Invalid nonce or the form wasn't submitted.
		if ( ! llms_verify_nonce( '_lost_password_nonce', 'llms_lost_password' ) ) {
			return null;
		}

		/**
		 * Fire an action immediately prior to the lost password form submission processing.
		 *
		 * @since [version]
		 */
		do_action( 'llms_before_lost_password_form_submit' );

		$login = trim( llms_filter_input( INPUT_POST, 'llms_login', FILTER_SANITIZE_STRING ) );

		// Verify required field.
		if ( ! $login ) {
			$msg = __( 'Enter a username or email address.', 'lifterlms' );
			llms_add_notice( $msg, 'error' );
			return new WP_Error( 'llms_lost_password_missing_login', $msg );
		}

		// Locate the user.
		$field = strpos( $login, '@' ) ? 'email' : 'login';
		$user  = get_user_by( $field, $login );

		// If we don't have a user return an error.
		if ( ! $user ) {
			$msg = __( 'Invalid username or email address.', 'lifterlms' );
			llms_add_notice( $msg, 'error' );
			return new WP_Error( 'llms_lost_password_invalid_login', $msg );
		}

		// Set/Retrieve the password reset key.
		$key = get_password_reset_key( $user );
		if ( is_wp_error( $key ) ) {
			llms_add_notice( $key->get_error_message(), 'error' );
			return $key;
		}

		// Setup the email.
		$email = LLMS()->mailer()->get_email(
			'reset_password',
			array(
				'key'           => $key,
				'user'          => $user,
				'login_display' => 'email' === $field ? $user->user_email : $user->user_login,
			)
		);

		// Email error.
		if ( ! $email->send() ) {
			$msg = __( 'The password reset email could not be sent. An error was encountered while attempting to send mail.', 'lifterlms' );
			llms_add_notice( $msg, 'error' );
			return new WP_Error( 'llms_lost_password_email_send', $msg );
		}

		// Success.
		llms_add_notice( __( 'Check your inbox for an email with instructions on how to reset your password.', 'lifterlms' ) );
		return true;

	}

	/**
	 * Handle form submission of the Reset Password form
	 * This is the form that actually updates a users password
	 *
	 * @since 3.8.0
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return   void
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

		$login = llms_filter_input( INPUT_POST, 'llms_reset_login', FILTER_SANITIZE_STRING );
		if ( ! llms_verify_password_reset_key( llms_filter_input( INPUT_POST, 'llms_reset_key', FILTER_SANITIZE_STRING ), $login ) ) {
			return llms_add_notice( __( 'Invalid Key', 'lifterlms' ), 'error' );
		}

		$pass = llms_filter_input( INPUT_POST, 'password', FILTER_SANITIZE_STRING );
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
