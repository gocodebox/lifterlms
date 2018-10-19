<?php
defined( 'ABSPATH' ) || exit;

/**
 * User Registration Forms (excludes checkout registration)
 *
 * @since   3.0.0
 * @version 3.24.0
 */
class LLMS_Controller_Registration {

	/**
	 * Constructor
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register' ) );
		add_action( 'lifterlms_user_registered', array( $this, 'voucher' ), 10, 3 );

	}

	/**
	 * Attempt to redeem a voucher on user registration
	 * if a voucher was submitted during registration
	 * @param    int        $person_id  WP_User ID of the newly registered user
	 * @param    array      $data       $_POST
	 * @param    string     $screen     screen user registered from [checkout|registration]
	 * @return   void
	 * @since    3.0.0
	 * @version  3.19.4
	 */
	public function voucher( $person_id, $data, $screen ) {

		if ( 'registration' === $screen && ! empty( $data['llms_voucher'] ) ) {

			$voucher = new LLMS_Voucher();
			$redeemed = $voucher->use_voucher( $data['llms_voucher'], $person_id );

			if ( is_wp_error( $redeemed ) ) {

				llms_add_notice( $redeemed->get_error_message(), 'error' );

			}
		}

	}

	/**
	 * Handle submission of user registrration forms
	 * @return   void
	 * @since    3.0.0
	 * @version  3.24.0
	 */
	public function register() {

		if ( ! llms_verify_nonce( '_llms_register_person_nonce', 'llms_register_person' ) ) {
			return;
		}

		do_action( 'lifterlms_before_new_user_registration' );

		// already logged in can't register!
		// this shouldn't happen but let's check anyway
		if ( get_current_user_id() ) {
			return llms_add_notice( __( 'Already logged in! Please log out and try again.', 'lifterlms' ), 'error' );
		}

		$person_id = llms_register_user( $_POST, 'registration', true );

		// validation or registration issues
		if ( is_wp_error( $person_id ) ) {

			foreach ( $person_id->get_error_messages() as $msg ) {
				llms_add_notice( $msg, 'error' );
			}
			return;

		} elseif ( ! is_numeric( $person_id ) ) {

			// catch unexpected returns from llms_register_user()
			return llms_add_notice( __( 'An unknown error occurred when attempting to create an account, please try again.', 'lifterlms' ), 'error' );

		} else {

			// handle redirect
			llms_redirect_and_exit( apply_filters( 'lifterlms_registration_redirect', llms_get_page_url( 'myaccount' ) ) );

		}

	}

}

return new LLMS_Controller_Registration();
