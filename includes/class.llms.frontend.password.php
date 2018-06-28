<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Front End Password handler
*
* Class used managing front end password functionality
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Frontend_Password {

	/**
	* Lost password template
	*
	* @return void
	*/
	public static function retrieve_password() {

		global $lifterlms,$wpdb;

		if ( empty( $_POST['user_login'] ) ) {

			llms_add_notice( __( 'Enter a username or e-mail address.', 'lifterlms' ), 'error' );

		} elseif ( strpos( $_POST['user_login'], '@' ) && apply_filters( 'lifterlms_get_username_from_email', true ) ) {

			$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );

			if ( empty( $user_data ) ) {

				llms_add_notice( __( 'The email address entered is not associated with an account.', 'lifterlms' ), 'error' ); }
		} else {

			$login = trim( $_POST['user_login'] );

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

			// Now insert the new md5 key into the db
			$wpdb->update( $wpdb->users, array(
				'user_activation_key' => $key,
				), array(
				'user_login' => $user_login,
			) );

		}

		$mailer = llms()->mailer();
		do_action( 'lifterlms_reset_password_notification', $user_login, $key );
		llms_add_notice( __( 'Check your e-mail for the account confirmation link.', 'lifterlms' ) );
		return true;
	}

	/**
	* Checks the password reset key
	*
	* @return string $user
	*/
	public static function check_password_reset_key( $key, $login ) {
		global $lifterlms,$wpdb;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || ! is_string( $key ) ) {

			llms_add_notice( __( 'Invalid key', 'lifterlms' ), 'error' );
			return false;

		}

		if ( empty( $login ) || ! is_string( $login ) ) {

			llms_add_notice( __( 'Invalid key', 'lifterlms' ), 'error' );
			return false;

		}

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login ) );

		if ( empty( $user ) ) {

			llms_add_notice( __( 'Invalid key', 'lifterlms' ), 'error' );
			return false;

		}

		return $user;
	}

	/**
	* Reset the users password
	*
	* @return void
	*/
	public static function reset_password( $user, $new_pass ) {

		do_action( 'password_reset', $user, $new_pass );
		wp_set_password( $new_pass, $user->ID );
		wp_password_change_notification( $user );

	}

}

new LLMS_Frontend_Password();
