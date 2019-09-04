<?php
/**
 * Achievement Forms
 *
 * @since   3.18.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Achievements
 *
 * @since 3.18.0
 * @since 3.35.0 Sanitize `$_POST` data.
 */
class LLMS_Controller_Achievements {

	/**
	 * Constructor
	 *
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'maybe_handle_reporting_actions' ) );

	}

	/**
	 * Handle certificate form actions to download (for students and admins) and to delete (admins only)
	 *
	 * @since 3.18.0
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return void
	 */
	public function maybe_handle_reporting_actions() {

		if ( ! llms_verify_nonce( '_llms_achievement_actions_nonce', 'llms-achievement-actions' ) ) {
			return;
		}

		if ( isset( $_POST['llms_delete_achievement'] ) ) {
			$this->delete( llms_filter_input( INPUT_POST, 'achievement_id', FILTER_SANITIZE_NUMBER_INT ) );
		}

	}

	/**
	 * Delete a cert
	 *
	 * @param    int $cert_id  WP Post ID of the llms_my_certificate
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
