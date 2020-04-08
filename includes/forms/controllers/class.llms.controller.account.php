<?php
/**
 * User Account Edit Forms
 *
 * @param LifterLMS/Classes/Forms/Controllers
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
 * @since [version] Refactored `lost_password()` method.
 */
class LLMS_Controller_Account {

	/**
	 * Constructor
	 *
	 * @since 3.7.0
	 * @since 3.10.0 Added `cancel_subscription()` handler.
	 *
	 * @return void
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
	 * @since [version] Refactored for readability and added new hooks.
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

		$err = new WP_Error();

		/**
		 * Fire an action immediately prior to the lost password form submission processing.
		 *
		 * @since [version]
		 */
		do_action( 'llms_before_lost_password_form_submit' );

		$login = llms_filter_input( INPUT_POST, 'llms_login', FILTER_SANITIZE_STRING );

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

			} else {

				/**
				 * Mimic WordPress core functionality to prevent resetting of password generally or for a specific user.
				 *
				 * Returning `false` or a `WP_Error` will prevent password resetting.
				 *
				 * If a `WP_Error` is returned, the first error message from the object will be output as an error notice.
				 *
				 * @since [version]
				 *
				 * @link https://developer.wordpress.org/reference/hooks/allow_password_reset/
				 *
				 * @param bool $allow   Whether or not to allow password reset.
				 * @param int  $user_id WP_User ID of the user who's password is being reset.
				 */
				$allow = apply_filters( 'allow_password_reset', true, $user->ID ); // WP core filter.

				if ( ! $allow ) {
					$err->add( 'llms_pass_reset_disabled', __( 'Password reset is not allowed for this user.', 'lifterlms' ) );
				} elseif ( is_wp_error( $allow ) ) {
					$err = $allow;
				}
			}
		}

		/**
		 * Fires before errors are returned from a password reset request.
		 *
		 * Mimics WordPress core behavior so 3rd parties don't need to add special handlers for LifterLMS
		 * password reset flows.
		 *
		 * @since [version]
		 *
		 * @link https://developer.wordpress.org/reference/hooks/lostpassword_post/
		 *
		 * @param WP_Error $err A WP_Error object containing any errors generated by using invalid credentials.
		 */
		do_action( 'lostpassword_post', $err );

		// If we have errors, output them and return.
		if ( ! empty( $err->errors ) ) { // @todo: When we can drop support for WP 5.0 and earlier we can switch to $err->has_errors().
			foreach ( $err->get_error_messages() as $message ) {
				llms_add_notice( $message, 'error' );
			}
			return $err;
		}

		// @todo: this probably doesn't belong here.
		do_action( 'retrieve_password', $user->user_login ); // WP Core Hook.

		// Set the user's password reset key.
		$key = llms_set_user_password_rest_key( $user->ID );

		// Setup the email.
		$email = LLMS()->mailer()->get_email(
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
	 * Handle form submission of the Reset Password form
	 *
	 * This is the form that actually updates a users password.
	 *
	 * @since 3.8.0
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return void
	 */
	public function reset_password() {

		// Invalid nonce or the form wasn't submitted.
		if ( ! llms_verify_nonce( '_reset_password_nonce', 'llms_reset_password', 'POST' ) ) {
			return;
		}

		$valid = LLMS_Person_Handler::validate_fields( $_POST, 'reset_password' );

		// Validation or registration issues.
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
