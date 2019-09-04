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
	 * This is the form that sends a password recovery email with a link to reset the password
	 *
	 * @since 3.8.0
	 * @since 3.9.5 Unknown.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return   void
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

		$login = llms_filter_input( INPUT_POST, 'llms_login', FILTER_SANITIZE_STRING );

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
		$email = LLMS()->mailer()->get_email(
			'reset_password',
			array(
				'key'           => $key,
				'user'          => $user,
				'login_display' => 'email' === $get_by ? $user->user_email : $user->user_login,
			)
		);

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
