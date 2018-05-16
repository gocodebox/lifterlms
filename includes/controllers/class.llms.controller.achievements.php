<?php
defined( 'ABSPATH' ) || exit;

/**
 * Achievement Forms
 * @since   3.18.0
 * @version 3.18.0
 */
class LLMS_Controller_Achievements {

	/**
	 * Constructor
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'maybe_handle_reporting_actions' ) );

	}

	/**
	 * Handle certificate form actions to download (for students and admins) and to delete (admins only)
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function maybe_handle_reporting_actions() {

		if ( ! llms_verify_nonce( '_llms_achievement_actions_nonce', 'llms-achievement-actions' ) ) {
			return;
		}

		$cert_id = absint( $_POST['achievement_id'] );

		if ( isset( $_POST['llms_delete_achievement'] ) ) {
			$this->delete( $cert_id );
		}

	}

	/**
	 * Delete a cert
	 * @param    int     $cert_id  WP Post ID of the llms_my_certificate
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private function delete( $cert_id ) {

		if ( ! is_admin() ) {
			return;
		}

		$cert = new LLMS_User_Achievement( $cert_id );
		$cert->delete();

	}

}

return new LLMS_Controller_Achievements();
