<?php
defined( 'ABSPATH' ) || exit;

/**
 * Certificate Forms
 * @since   [version]
 * @version [version]
 */
class LLMS_Controller_Certificates {

	/**
	 * Constructor
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'maybe_generate_export' ) );
		add_action( 'wp', array( $this, 'maybe_authenticate_export_generation' ) );

	}

	/**
	 * Utilizes a nonce to display a certificate
	 * cURL request is used to scrape the HTML and this will authenticate the scrape
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function maybe_authenticate_export_generation() {

		if ( empty( $_REQUEST['_llms_cert_auth'] ) ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! in_array( get_post_type( $post_id ), array( 'llms_my_certificate', 'llms_certificate' ) ) ) {
			return;
		}

		if ( $_REQUEST['_llms_cert_auth'] !== get_post_meta( $post_id, '_llms_auth_nonce', true ) ) {
			return;
		}

		$cert = new LLMS_User_Certificate( $post_id );
		wp_set_current_user( $cert->get_user_id() );

	}

	/**
	 * Generates an HTML export of the certificate from the "Download" button
	 * on the View Certificate front end & on reporting backend for admins
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function maybe_generate_export() {

		if ( ! llms_verify_nonce( '_llms_gen_cert_nonce', 'llms-generate-cert' ) ) {
			return;
		}

		$filepath = LLMS()->certificates()->get_export( absint( $_POST['certificate_id'] ) );
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
