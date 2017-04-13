<?php
/**
 * User Account Edit Forms
 *
 * @since   3.7.0
 * @version 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Controller_Account {

	public function __construct() {

		add_action( 'init', array( $this, 'update' ) );

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
		} // attempt to update new user (performs validations)
		else {
			$person_id = llms_update_user( $_POST, 'account' );
		}

		// validation or update issues
		if ( is_wp_error( $person_id ) ) {
			foreach ( $person_id->get_error_messages() as $msg ) {
				llms_add_notice( $msg, 'error' );
			}
			return;
		} // update should be a user_id at this point, if we're not numeric we have a problem...
		elseif ( ! is_numeric( $person_id ) ) {

			return llms_add_notice( __( 'An unknown error occurred when attempting to create an account, please try again.', 'lirterlms' ), 'error' );

		} else {

			llms_add_notice( __( 'Your account information has been saved.', 'lifterlms' ), 'success' );

			// handle redirect
			wp_safe_redirect( apply_filters( 'lifterlms_update_account_redirect', llms_get_endpoint_url( 'edit-account', '', llms_get_page_url( 'myaccount' ) ) ) );
			exit;

		}

	}

}

return new LLMS_Controller_Account();
