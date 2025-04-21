<?php
/**
 * Form submission handler for forms on the Student Dashboard.
 *
 * @package LifterLMS/Forms/Controllers/Classes
 *
 * @since 3.7.0
 * @version 6.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Account class.
 *
 * @since 3.7.0
 * @since 3.35.0 Sanitize `$_POST` data.
 * @since 3.37.17 Refactored `lost_password()` and `reset_password()` methods.
 */
class LLMS_Controller_Account {

	/**
	 * Constructor
	 *
	 * @since 3.7.0
	 * @since 3.10.0 Add student subscription cancellation handler.
	 * @since 5.0.0 Add reset password link redirection handler.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'reset_password_link_redirect' ), 1 );

		add_action( 'init', array( $this, 'update' ) );
		add_action( 'init', array( $this, 'lost_password' ) );
		add_action( 'init', array( $this, 'reset_password' ) );
		add_action( 'init', array( $this, 'cancel_subscription' ) );
		add_action( 'init', array( $this, 'redeem_voucher' ) );
	}

	/**
	 * Lets student cancel recurring access plan subscriptions from the student dashboard view order screen
	 *
	 * @since 3.10.0
	 * @since 3.19.0 Unknown.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return void
	 */
	public function cancel_subscription() {

		// Invalid nonce or the form wasn't submitted.
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

		// Active subscriptions move to pending-cancel.
		// All other statuses are cancelled immediately.
		if ( 'llms-active' === $order->get( 'status' ) ) {
			$new_status = 'pending-cancel';
			$note      .= ' ' . __( 'Enrollment will be cancelled at the end of the prepaid period.', 'lifterlms' );
		} else {
			$new_status = 'cancelled';
		}

		$order->set_status( $new_status );
		$order->add_note( $note );

		/**
		 * Action triggered after a recurring subscription is cancelled from the student dashboard by the student.
		 *
		 * @since 3.17.8
		 *
		 * @param LLMS_Order $order The order object.
		 * @param integer    $uid   The WP_User ID the student who cancelled the subscription.
		 */
		do_action( 'llms_subscription_cancelled_by_student', $order, $uid );
	}

	/**
	 * Handle submission of user account edit form
	 *
	 * @since 3.7.0
	 * @since 3.24.0 Unknown.
	 *
	 * @return void
	 */
	public function update() {

		if ( ! llms_verify_nonce( '_llms_update_person_nonce', 'llms_update_person' ) ) {
			return;
		}

		do_action( 'llms_before_user_account_update_submit' );

		// No user logged in, can't update!
		// This shouldn't happen but let's check anyway.
		if ( ! get_current_user_id() ) {
			return llms_add_notice( __( 'Please log in and try again.', 'lifterlms' ), 'error' );
		}

		$person_id = llms_update_user( $_POST, 'account' );

		// Validation or update issues.
		if ( is_wp_error( $person_id ) ) {

			foreach ( $person_id->get_error_messages() as $msg ) {
				llms_add_notice( $msg, 'error' );
			}
			return;

		} elseif ( ! is_numeric( $person_id ) ) {

			return llms_add_notice( __( 'An unknown error occurred when attempting to create an account, please try again.', 'lifterlms' ), 'error' );

		} else {

			llms_add_notice( __( 'Your account information has been saved.', 'lifterlms' ), 'success' );

			// Handle redirect.
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
	 * @since 3.37.17 Refactored for readability and added new hooks.
	 * @since 4.21.3 Increase 3rd party support for WP core hooks.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return null|WP_Error|true `null` when nonce cannot be verified.
	 *                            `WP_Error` when an error is encountered.
	 *                            `true` on success.
	 */
	public function lost_password() {

		// Invalid nonce or the form wasn't submitted.
		if ( ! llms_verify_nonce( '_lost_password_nonce', 'llms_lost_password', 'POST' ) ) {
			return null;
		}

		/**
		 * Fire an action immediately prior to the lost password form submission processing.
		 *
		 * @since 3.37.17
		 */
		do_action( 'llms_before_lost_password_form_submit' );

		$err   = new WP_Error();
		$user  = false;
		$login = llms_filter_input_sanitize_string( INPUT_POST, 'llms_login' );

		// Login is required.
		if ( empty( $login ) ) {
			$err->add( 'llms_pass_reset_missing_login', __( 'Enter a username or e-mail address.', 'lifterlms' ) );
		} else {

			// Locate the user.
			$field = strpos( $login, '@' ) ? 'email' : 'login';
			$user  = get_user_by( $field, $login );

			// No user found.
			if ( ! $user ) {
				$err->add( 'llms_pass_reset_invalid_login', __( 'Invalid username or e-mail address.', 'lifterlms' ) );
			}
		}

		/**
		 * Ensure 3rd parties that don't use the 2nd param of `lostpassword_post` still work with our reset functionality.
		 *
		 * This specifically adds support for WordFence's "max allowed password resets" under brute force protection, but
		 * might be useful in other scenarios.
		 */
		$_POST['user_login'] = $login;

		/**
		 * Fires before errors are returned from a password reset request.
		 *
		 * Mimics WordPress core behavior so 3rd parties don't need to add special handlers for LifterLMS
		 * password reset flows.
		 *
		 * @since 3.37.17
		 *
		 * @link https://developer.wordpress.org/reference/hooks/lostpassword_post/
		 *
		 * @param WP_Error      $err  A WP_Error object containing any errors generated by using invalid credentials.
		 * @param WP_User|false $user WP_User object if found, false if the user does not exist.
		 */
		do_action( 'lostpassword_post', $err, $user );

		// If we have errors, output them and return.
		if ( ! empty( $err->errors ) ) { // @todo: When we can drop support for WP 5.0 and earlier we can switch to $err->has_errors().
			foreach ( $err->get_error_messages() as $message ) {
				llms_add_notice( $message, 'error' );
			}
			return $err;
		}

		// Set the user's password reset key.
		$key = get_password_reset_key( $user );
		if ( is_wp_error( $key ) ) {
			llms_add_notice( $key->get_error_message(), 'error' );
			return $key;
		}

		// Setup the email.
		$email = llms()->mailer()->get_email(
			'reset_password',
			array(
				'key'           => $key,
				'user'          => $user,
				'login_display' => 'email' === $field ? $user->user_email : $user->user_login,
			)
		);

		// Error generating or sending the email.
		if ( ! $email || ! $email->send() ) {

			$err->add( 'llms_pass_reset_email_failure', __( 'Unable to reset password due to an unknown error. Please try again.', 'lifterlms' ) );
			llms_add_notice( $err->get_error_message(), 'error' );
			return $err;

		}

		// Success.
		llms_add_notice( __( 'Check your e-mail for the confirmation link.', 'lifterlms' ) );
		return true;
	}

	/**
	 * Redeem a voucher from the "Redeem Voucher" endpoint of the student dashboard
	 *
	 * @since 4.12.0
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @return null|true|WP_Error Returns `null` when the form hasn't been submitted, there's a nonce error, or there's no logged in user.
	 *                            Returns `true` on success and an error object when an error is encountered redeeming the voucher.
	 */
	public function redeem_voucher() {

		if ( ! llms_verify_nonce( 'lifterlms_voucher_nonce', 'lifterlms_voucher_check' ) || ! get_current_user_id() ) {
			return null;
		}

		$voucher  = new LLMS_Voucher();
		$redeemed = $voucher->use_voucher( llms_filter_input_sanitize_string( INPUT_POST, 'llms_voucher_code' ), get_current_user_id() );

		if ( is_wp_error( $redeemed ) ) {
			llms_add_notice( $redeemed->get_error_message(), 'error' );
			return $redeemed;
		}

		llms_add_notice( __( 'Voucher redeemed successfully!', 'lifterlms' ), 'success' );
		return true;
	}

	/**
	 * Handle form submission of the Reset Password form
	 *
	 * This is the form that actually updates a users password.
	 *
	 * @since 3.8.0
	 * @since 3.35.0 Sanitize `$_POST` data.
	 * @since 3.37.17 Use WP core functions in favor of their (deprecated) LifterLMS clones.
	 * @since 4.21.0 Use `addslashes()` and `FILTER_UNSAFE_RAW` to mimic magic quotes behavior of the WP core reset flow.
	 * @since 5.0.0 Refactored to move reset logic into it's own method.
	 *
	 * @return null|WP_Error|true Returns `null` for nonce errors or when the form hasn't been submitted, an error object when
	 *                            errors are encountered, and `true` on success.
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
	 * @since 5.0.0
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
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
		 * @since 5.0.0
		 */
		do_action( 'llms_before_user_reset_password_submit' );

		/**
		 * Add custom validations to the password reset form.
		 *
		 * @since 5.0.0
		 *
		 * @param WP_Error|true $valid Whether or not the submitted data is valid. Return `true` for valid data or a `WP_Error` when invalid.
		 */
		$valid = apply_filters( 'llms_validate_password_reset_form', $this->validate_password_reset( wp_unslash( $_POST ) ) );
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		$login = llms_filter_input_sanitize_string( INPUT_POST, 'llms_reset_login' );
		$key   = llms_filter_input_sanitize_string( INPUT_POST, 'llms_reset_key' );
		$user  = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			// Error code is either "llms_password_reset_invalid_key" or "llms_password_reset_expired_key".
			return new WP_Error( sprintf( 'llms_password_reset_%s', $user->get_error_code() ), __( 'This password reset key is invalid or has already been used. Please reset your password again if needed.', 'lifterlms' ) );
		}

		reset_password( $user, addslashes( llms_filter_input( INPUT_POST, 'password' ) ) );

		/**
		 * Send the WP Core admin notification when a user's password is changed via the password reset form.
		 *
		 * @since 3.37.17
		 *
		 * @param bool    $notify_admin If `true`, the admin will be notified.
		 * @param WP_User $user         User object.
		 */
		$notify_admin = apply_filters( 'llms_password_reset_send_admin_notification', true, $user );
		if ( $notify_admin ) {
			wp_password_change_notification( $user );
		}

		/**
		 * Fire an action the the user's password is reset.
		 *
		 * @since 5.0.0
		 *
		 * @param WP_User $user User object.
		 */
		do_action( 'llms_user_password_reset', $user );

		return true;
	}

	/**
	 * Automatically redirect password reset links to the password reset form page.
	 *
	 * Strips the `key` and `login` query string parameters and sets them in a cookie
	 * (which is accessed later to populate the hidden fields on the reset form) and then
	 * redirect to the password reset form.
	 *
	 * @since 5.0.0
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 * @since 6.6.0 Prevented client and server caching of the password reset form page.
	 *
	 * @return void
	 */
	public function reset_password_link_redirect() {

		if ( is_llms_account_page() && isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {

			$user = get_user_by( 'login', wp_unslash( llms_filter_input_sanitize_string( INPUT_GET, 'login' ) ) );
			$uid  = $user ? $user->ID : 0;
			$val  = sprintf( '%1$d:%2$s', $uid, wp_unslash( llms_filter_input_sanitize_string( INPUT_GET, 'key' ) ) );

			( new LLMS_Cache_Helper() )->maybe_no_cache();
			llms_set_password_reset_cookie( $val );
			llms_redirect_and_exit( add_query_arg( 'reset-pass', 1, wp_lostpassword_url() ) );
		}
	}

	/**
	 * Validates the password reset form.
	 *
	 * @since 5.0.0
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
