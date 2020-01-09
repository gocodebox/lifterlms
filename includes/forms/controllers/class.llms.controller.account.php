<?php
/**
 * Form submission handler for forms on the Student Dashboard.
 *
 * @since 3.7.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Account class.
 *
 * @since 3.7.0
 * @since 3.35.0 Sanitize `$_POST` data.
 * @since [version] Refactored `lost_password` and `reset_password` forms.
 */
class LLMS_Controller_Account {

	/**
	 * Constructor
	 *
	 * @since 3.7.0
	 * @since 3.10.0 Add student subscription cancellation handler.
	 * @since [version] Add reset password link redirection handler.
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'reset_password_link_redirect' ), 1 );

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
	 * @return null|WP_Error|true Returns `null` when the nonce can't be verified, on failure a `WP_Error` object, and `true` on success.

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
	 * @return null|WP_Error|void Returns `null` when the nonce can't be verified, on failure a `WP_Error` object, and void on success.
	 */
	public function reset_password() {

		$result = $this->reset_password_handler();

		if ( ! $result ) {
			return null;
		} elseif ( is_wp_error( $result ) ) {
			llms_add_notice( implode( '<br>', $result->get_error_messages() ), 'error' );
			return $result;
		}

		// Success.
		llms_add_notice( __( 'Your password has been updated.', 'lifterlms' ) );
		llms_redirect_and_exit( add_query_arg( 'password-reset', 1, llms_get_page_url( 'myaccount' ) ) );

	}

	/**
	 * Handle the submission of the password reset form.
	 *
	 * @since [version]
	 *
	 * @return null|WP_Error|true Returns `null` when the nonce can't be verified, on failure a `WP_Error` object, and `true` on success.
	 */
	private function reset_password_handler() {

		// Invalid nonce or the form wasn't submitted.
		if ( ! llms_verify_nonce( '_reset_password_nonce', 'llms_reset_password' ) ) {
			return null;
		}

		/**
		 * Fire an action before the user password reset form is handled.
		 *
		 * @since [version]
		 */
		do_action( 'llms_before_user_reset_password_submit' );

		/**
		 * Add custom validations to the password reset form.
		 *
		 * @since [version]
		 *
		 * @param WP_Error|true $valid Whether or not the submitted data is valid. Return `true` for valid data or a `WP_Error` when invalid.
		 */
		$valid = apply_filters( 'llms_validate_password_reset_form', $this->validate_password_reset( wp_unslash( $_POST ) ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$login = llms_filter_input( INPUT_POST, 'llms_reset_login', FILTER_SANITIZE_STRING );
		$key   = llms_filter_input( INPUT_POST, 'llms_reset_key', FILTER_SANITIZE_STRING );
		$user  = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			// Error code is either "llms_password_reset_invalid_key" or "llms_password_reset_expired_key".
			return new WP_Error( sprintf( 'llms_password_reset_%s', $user->get_error_code() ), __( 'This password reset key is invalid or has already been used. Please reset your password again if needed.', 'lifterlms' ) );
		}

		reset_password( $user, llms_filter_input( INPUT_POST, 'password', FILTER_SANITIZE_STRING ) );

		/**
		 * Fire an action the the user's password is reset.
		 *
		 * @since [version]
		 *
		 * @param WP_User $user User object.
		 */
		do_action( 'llms_user_password_reset', $user );

		return true;

	}

	/**
	 * Automatically redirect password rest links to the password reset form page.
	 *
	 * Strips the `key` and `login` query string parameters and sets them in a cookie
	 * (which is accessed later to populate the hidden fields on the reset form) and then
	 * redirect to the password reset form.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function reset_password_link_redirect() {

		if ( is_llms_account_page() && isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {

			$user = get_user_by( 'login', wp_unslash( llms_filter_input( INPUT_GET, 'login', FILTER_SANITIZE_STRING ) ) );
			$uid  = $user ? $user->ID : 0;
			$val  = sprintf( '%1$d:%2$s', $uid, wp_unslash( llms_filter_input( INPUT_GET, 'key', FILTER_SANITIZE_STRING ) ) );

			llms_set_password_reset_cookie( $val );
			llms_redirect_and_exit( add_query_arg( 'reset-pass', 1, llms_lostpassword_url() ) );
		}

	}

	/**
	 * Validates the password reset form.
	 *
	 * @since [version]
	 *
	 * @param array $posted_data User submitted data.
	 * @return WP_Error|true
	 */
	protected function validate_password_reset( $posted_data ) {

		$err = new WP_Error();

		$fields = LLMS_Person_Handler::get_password_reset_fields();

		// Validate required fields.
		foreach ( $fields as &$field ) {

			$obj   = new LLMS_Form_Field( $field );
			$field = $obj->get_settings();

			// Field is required, submittable, and wasn't posted.
			if ( ! empty( $field['required'] ) && ! empty( $field['name'] ) && empty( $posted_data[ $field['name'] ] ) ) {

				// Translators: %s = field label or id.
				$msg = sprintf( __( '%s is a required field.', 'lifterlms' ), isset( $field['label'] ) ? $field['label'] : $field['name'] );
				$err->add( 'llms-password-reset-missing-field', $msg );

			}
		}

		if ( count( $err->errors ) ) {
			return $err;
		}

		// If we have a password and password confirm and they don't match.
		if ( isset( $posted_data['password'] ) && isset( $posted_data['password_confirm'] ) && $posted_data['password'] !== $posted_data['password_confirm'] ) {

			$msg = __( 'The submitted passwords do must match.', 'lifterlms' );
			$err->add( 'llms-passwords-must-match', $msg );
			return $err;

		}

		return true;

	}

}

return new LLMS_Controller_Account();
