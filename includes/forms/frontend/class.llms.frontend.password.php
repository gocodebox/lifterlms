<?php
/**
 * Front End Password handler
 *
 * @package LifterLMS/Forms/Frontend/Classes
 *
 * @since 1.0.0
 * @version [vesion]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Frontend_Password class
 *
 * Class used managing front end password functionality
 *
 * @since 1.0.0
 * @since 3.35.0 Sanitize `$_POST` data.
 * @deprecated 4.21.3 LLMS_Frontend_Password is deprecated, functionality is available via LLMS_Controller_Account.
 */
class LLMS_Frontend_Password {

	/**
	 * Lost password template
	 *
	 * @since 1.0.0
	 * @since 3.35.0 Sanitize `$_POST` data.
	 * @deprecated 4.21.3 `LLMS_Frontend_Password::retrieve_password()` is deprecated in favor of `LLMS_Controller_Account::lost_password()`.
	 *
	 * @return void
	 */
	public static function retrieve_password() {

		llms_deprecated_function( 'LLMS_Frontend_Password::retrieve_password()', '4.21.3', 'LLMS_Controller_Account::lost_password()' );

		global $wpdb;

		$login = trim( llms_filter_input( INPUT_POST, 'user_login', FILTER_SANITIZE_STRING ) );

		if ( $login ) {

			llms_add_notice( __( 'Enter a username or e-mail address.', 'lifterlms' ), 'error' );

		} elseif ( strpos( $login, '@' ) && apply_filters( 'lifterlms_get_username_from_email', true ) ) {

			$user_data = get_user_by( 'email', $login );

			if ( empty( $user_data ) ) {

				llms_add_notice( __( 'The email address entered is not associated with an account.', 'lifterlms' ), 'error' );
			}
		} else {
			$user_data = get_user_by( 'login', $login );
		}

		do_action( 'lostpassword_post' );

		if ( llms_notice_count( 'error' ) > 0 ) {

			return false;

		}

		if ( ! $user_data ) {
			llms_add_notice( __( 'Invalid username or e-mail.', 'lifterlms' ), 'error' );

			return false;
		}

		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {

			llms_add_notice( __( 'Could not reset password.', 'lifterlms' ), 'error' );

			return false;

		} elseif ( is_wp_error( $allow ) ) {

			llms_add_notice( $allow->get_error_message, 'error' );

			return false;

		}

		$key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );

		if ( empty( $key ) ) {

			// Generate something random for a key...
			$key = wp_generate_password( 20, false );

			do_action( 'retrieve_password_key', $user_login, $key );

			// Now insert the new md5 key into the db.
			$wpdb->update(
				$wpdb->users,
				array(
					'user_activation_key' => $key,
				),
				array(
					'user_login' => $user_login,
				)
			);

		}

		$mailer = llms()->mailer();
		do_action( 'lifterlms_reset_password_notification', $user_login, $key );
		llms_add_notice( __( 'Check your e-mail for the account confirmation link.', 'lifterlms' ) );
		return true;
	}

	/**
	 * Checks the password reset key
	 *
	 * @since 1.0.0
	 * @deprecated 4.21.3 `LLMS_Frontend_Password::check_password_reset_key()` is deprecated in favor of `check_password_reset_key()`.
	 *
	 * @return string $user
	 */
	public static function check_password_reset_key( $key, $login ) {

		llms_deprecated_function( 'LLMS_Frontend_Password::check_password_reset_key()', '4.21.3', 'check_password_reset_key()' );
		return check_password_reset_key( $key, $login );

	}

	/**
	 * Reset the users password
	 *
	 * @since 1.0.0
	 * @deprecated 4.21.3 `LLMS_Frontend_Password::reset_password()` is deprecated in favor of `reset_password()`.
	 *
	 * @return void
	 */
	public static function reset_password( $user, $new_pass ) {

		llms_deprecated_function( 'LLMS_Frontend_Password::reset_password()', '4.21.3', 'reset_password()' );
		reset_password( $user, $new_pass );

	}

}

new LLMS_Frontend_Password();
