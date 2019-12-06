<?php
/**
 * Certificate Forms
 *
 * @since 3.18.0
 * @version [version]
 */
defined( 'ABSPATH' ) || exit;

/**
 * @since 3.18.0
 * @since 3.35.0 Sanitize `$_POST` data.
 * @since [version] Modify `llms_certificate` post type registration to allow certificate templates to be exported.
 *               When exporting a certificate template, use the `post_author` for the certificate's WP User ID.
 */
class LLMS_Controller_Certificates {

	/**
	 * Constructor
	 *
	 * @since 3.18.0
	 * @since [version] Add filter hook for `lifterlms_register_post_type_llms_certificate`.
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'lifterlms_register_post_type_llms_certificate', array( $this, 'maybe_allow_public_query' ) );

		add_action( 'init', array( $this, 'maybe_handle_reporting_actions' ) );
		add_action( 'wp', array( $this, 'maybe_authenticate_export_generation' ) );

	}

	/**
	 * Modify certificate post type registration data during a certificate template export.
	 *
	 * Fixes issue https://github.com/gocodebox/lifterlms/issues/776
	 *
	 * @since [version]
	 *
	 * @param array $post_type_args Array of `llms_certificate` post type registration arguments.
	 * @return array
	 */
	public function maybe_allow_public_query( $post_type_args ) {

		if ( ! empty( $_REQUEST['_llms_cert_auth'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$auth = llms_filter_input( INPUT_GET, '_llms_cert_auth', FILTER_SANITIZE_STRING );

			global $wpdb;
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_llms_auth_nonce' AND meta_value = %s", $auth ) ); // db call ok; no-cache ok
			if ( $post_id && 'llms_certificate' === get_post_type( $post_id ) ) {
				$post_type_args['publicly_queryable'] = true;
			}
		}

		return $post_type_args;

	}

	/**
	 * Allow cURL requests to view a certificate to be authenticated via a nonce.
	 *
	 * cURL request is used to scrape the HTML and this will authenticate the scrape
	 *
	 * @since 3.18.0
	 * @since 3.24.0 Unknown.
	 * @since [version] Use the `post_author` as the WP_User ID when exporting a certificate template.
	 *
	 * @return void
	 */
	public function maybe_authenticate_export_generation() {

		if ( empty( $_REQUEST['_llms_cert_auth'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$post_id   = get_the_ID();
		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, array( 'llms_my_certificate', 'llms_certificate' ), true ) ) {
			return;
		}

		if ( get_post_meta( $post_id, '_llms_auth_nonce', true ) !== $_REQUEST['_llms_cert_auth'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$cert = new LLMS_User_Certificate( $post_id );
		$uid  = ( 'llms_certificate' === $post_type ) ? get_post_field( 'post_author', $post_id ) : $cert->get_user_id();
		wp_set_current_user( $uid );

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
	 * Delete a certificate
	 *
	 * @since 3.18.0
	 *
	 * @param int $cert_id WP Post ID of the llms_my_certificate
	 * @return void
	 */
	private function delete( $cert_id ) {

		if ( ! is_admin() ) {
			return;
		}

		$cert = new LLMS_User_Certificate( $cert_id );
		$cert->delete();

	}

	/**
	 * Download a Certificate.
	 *
	 * Generates an HTML export of the certificate from the "Download" button
	 * on the View Certificate front end & on reporting backend for admins.
	 *
	 * @since 3.18.0
	 *
	 * @return void
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
