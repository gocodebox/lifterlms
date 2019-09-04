<?php
/**
 * Certificate Forms
 *
 * @since   3.18.0
 * @version 3.24.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * @since 3.18.0
 * @since 3.35.0 Sanitize `$_POST` data.
 */
class LLMS_Controller_Certificates {

	/**
	 * Constructor
	 *
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'maybe_handle_reporting_actions' ) );
		add_action( 'wp', array( $this, 'maybe_authenticate_export_generation' ) );

	}

	/**
	 * Utilizes a nonce to display a certificate
	 * cURL request is used to scrape the HTML and this will authenticate the scrape
	 *
	 * @return   void
	 * @since    3.18.0
	 * @version  3.24.0
	 */
	public function maybe_authenticate_export_generation() {

		if ( empty( $_REQUEST['_llms_cert_auth'] ) ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! in_array( get_post_type( $post_id ), array( 'llms_my_certificate', 'llms_certificate' ) ) ) {
			return;
		}

		if ( get_post_meta( $post_id, '_llms_auth_nonce', true ) !== $_REQUEST['_llms_cert_auth'] ) {
			return;
		}

		$cert = new LLMS_User_Certificate( $post_id );
		wp_set_current_user( $cert->get_user_id() );

	}

	/**
	 * Handle certificate form actions to download (for students and admins) and to delete (admins only)
	 *
	 * @since 3.18.0
	 * @since 3.35.0 Sanitize `$_POST` data.
	 *
	 * @return   void
	 */
	public function maybe_handle_reporting_actions() {

		if ( ! llms_verify_nonce( '_llms_cert_actions_nonce', 'llms-cert-actions' ) ) {
			return;
		}

		$cert_id = llms_filter_input( INPUT_POST, 'certificate_id', FILTER_SANITIZE_STRING );
		if ( isset( $_POST['llms_generate_cert'] ) ) {
			$this->download( $cert_id );
		} elseif ( isset( $_POST['llms_delete_cert'] ) ) {
			$this->delete( $cert_id );
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

		$cert = new LLMS_User_Certificate( $cert_id );
		$cert->delete();

	}

	/**
	 * Generates an HTML export of the certificate from the "Download" button
	 * on the View Certificate front end & on reporting backend for admins
	 *
	 * @return   void
	 * @since    3.18.0
	 * @version  3.18.0
	 */
	private function download( $cert_id ) {

		$filepath = LLMS()->certificates()->get_export( $cert_id );
		if ( is_wp_error( $filepath ) ) {
			// @todo need to handle errors differently on admin panel
			return llms_add_notice( $filepath->get_error_message() );
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . basename( $filepath ) . '"' );

		readfile( $filepath );

		// delete file after download
		ignore_user_abort( true );
		wp_delete_file( $filepath );
		exit;
	}

}

return new LLMS_Controller_Certificates();
