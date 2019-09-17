<?php
/**
 * Front End Forms Class
 *
 * @since 1.0.0
 * @version 3.30.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front End Forms Class
 *
 * @since 1.0.0
 * @since 3.30.3 Fixed spelling errors.
 * @since 3.35.0 Sanitize `$_POST` data.
 */
class LLMS_Frontend_Forms {

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'voucher_check' ) );

	}

	/**
	 * Reset password form
	 *
	 * @since Unknown
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return void
	 */
	public function reset_password() {

		if ( ! isset( $_POST['llms_reset_password'] ) ) {
			return;
		}

		// process lost password form
		if ( isset( $_POST['user_login'] ) && isset( $_POST['_wpnonce'] ) ) {

			if ( ! llms_verify_nonce( '_wpnonce', 'lifterlms-lost_password' ) ) {
				return;
			}

			LLMS_Shortcode_My_Account::retrieve_password();

		}

		// process reset password form
		if ( isset( $_POST['password_1'] )
			&& isset( $_POST['password_2'] )
			&& isset( $_POST['reset_key'] )
			&& isset( $_POST['reset_login'] )
			&& isset( $_POST['_wpnonce'] )
		) {

			$key   = llms_filter_input( INPUT_POST, 'reset_key', FILTER_SANITIZE_STRING );
			$login = llms_filter_input( INPUT_POST, 'reset_key', FILTER_SANITIZE_STRING );

			// verify reset key again
			$user = LLMS_Shortcode_My_Account::check_password_reset_key( $key, $login );

			if ( is_object( $user ) ) {

				// save these values into the form again in case of errors
				$args['key']   = $key;
				$args['login'] = $login;

				if ( ! llms_verify_nonce( '_wpnonce', 'lifterlms-reset_password' ) ) {
					return;
				}

				if ( empty( $_POST['password_1'] ) || empty( $_POST['password_2'] ) ) {

					llms_add_notice( __( 'Please enter your password.', 'lifterlms' ), 'error' );
					$args['form'] = 'reset_password';

				}

				if ( $_POST['password_1'] !== $_POST['password_2'] ) {

					llms_add_notice( __( 'Passwords do not match.', 'lifterlms' ), 'error' );
					$args['form'] = 'reset_password';

				}

				$errors = new WP_Error();
				do_action( 'validate_password_reset', $errors, $user );

				if ( $errors->get_error_messages() ) {

					foreach ( $errors->get_error_messages() as $error ) {

						llms_add_notice( $error, 'error' );
					}
				}

				if ( 0 == llms_notice_count( 'error' ) ) {

					LLMS_Shortcode_My_Account::reset_password( $user, llms_filter_input( INPUT_POST, 'password_1', FILTER_SANITIZE_STRING ) );

					do_action( 'lifterlms_person_reset_password', $user );

					wp_redirect( add_query_arg( 'reset', 'true', remove_query_arg( array( 'key', 'login' ) ) ) );

					exit;
				}
			}// End if().
		}// End if().

	}

	/**
	 * Check voucher and use it if valid
	 *
	 * @since Unknown
	 * @since 3.30.3 Fixed spelling errors.
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return bool
	 */
	public function voucher_check() {

		if ( ! llms_verify_nonce( 'lifterlms_voucher_nonce', 'lifterlms_voucher_check' ) ) {
			return false;
		}

		if ( isset( $_POST['llms_voucher_code'] ) && ! empty( $_POST['llms_voucher_code'] ) ) {

			$voucher  = new LLMS_Voucher();
			$redeemed = $voucher->use_voucher( llms_filter_input( INPUT_POST, 'llms_voucher_code', FILTER_SANITIZE_STRING ), get_current_user_id() );

			if ( is_wp_error( $redeemed ) ) {

				llms_add_notice( $redeemed->get_error_message(), 'error' );

			} else {

				llms_add_notice( __( 'Voucher redeemed successfully!', 'lifterlms' ), 'success' );

			}
		}
	}

}

new LLMS_Frontend_Forms();
