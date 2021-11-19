<?php
/**
 * Certificate Forms
 *
 * @package LifterLMS/Controllers/Classes
 *
 * @since 3.18.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Controller_Certificates class
 *
 * @since 3.18.0
 * @since 3.35.0 Sanitize `$_POST` data.
 * @since 3.37.4 Modify `llms_certificate` post type registration to allow certificate templates to be exported.
 *               When exporting a certificate template, use the `post_author` for the certificate's WP User ID.
 * @since 4.3.1 Properly use an `error` notice to display a WP_Error when trying to download a certificate.
 */
class LLMS_Controller_Certificates {

	/**
	 * Constructor
	 *
	 * @since 3.18.0
	 * @since 3.37.4 Add filter hook for `lifterlms_register_post_type_llms_certificate`.
	 * @since 5.5.0 Drop usage of deprecated `lifterlms_register_post_type_llms_certificate` in favor of `lifterlms_register_post_type_certificate`.
	 * @since [version] Handle awarded certificates sync actions.
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'lifterlms_register_post_type_certificate', array( $this, 'maybe_allow_public_query' ) );

		add_action( 'init', array( $this, 'maybe_handle_reporting_actions' ) );
		add_action( 'init', array( $this, 'maybe_handle_awarded_certificates_sync_actions' ) );
		add_action( 'wp', array( $this, 'maybe_authenticate_export_generation' ) );

	}

	/**
	 * Modify certificate post type registration data during a certificate template export.
	 *
	 * Fixes issue https://github.com/gocodebox/lifterlms/issues/776
	 *
	 * @since 3.37.4
	 *
	 * @param array $post_type_args Array of `llms_certificate` post type registration arguments.
	 * @return array
	 */
	public function maybe_allow_public_query( $post_type_args ) {

		if ( ! empty( $_REQUEST['_llms_cert_auth'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$auth = llms_filter_input( INPUT_GET, '_llms_cert_auth', FILTER_SANITIZE_STRING );

			global $wpdb;
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_llms_auth_nonce' AND meta_value = %s", $auth ) ); // db call ok; no-cache ok.
			if ( $post_id && 'llms_certificate' === get_post_type( $post_id ) ) {
				$post_type_args['publicly_queryable'] = true;
			}
		}

		return $post_type_args;

	}

	/**
	 * Allow cURL requests to view a certificate to be authenticated via a nonce.
	 *
	 * A cURL request is used to scrape the HTML and this will authenticate the scrape.
	 *
	 * @since 3.18.0
	 * @since 3.24.0 Unknown.
	 * @since 3.37.4 Use the `post_author` as the WP_User ID when exporting a certificate template.
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
	 * Handle certificate form actions
	 *
	 * Manages frontend actions to download and manage certificate sharing settings and reporting (admin)
	 * actions to download and delete.
	 *
	 * The method name is a misnomer as this method handles actions on reporting screens as well as
	 * on the site's frontend when actually viewing a certificate
	 *
	 * @since 3.18.0
	 * @since 3.35.0 Sanitize `$_POST` data.
	 * @since 4.5.0 Add handler for changing certificate sharing settings.
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
		} elseif ( isset( $_POST['llms_enable_cert_sharing'] ) ) {
			$this->change_sharing_settings( $cert_id, (bool) $_POST['llms_enable_cert_sharing'] );
		}

	}

	/**
	 * Handle awrded certificates sync actions.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function maybe_handle_awarded_certificates_sync_actions() {

		if ( ! llms_verify_nonce( '_llms_cert_sync_actions_nonce', 'llms-cert-sync-actions', 'GET' ) || ! isset( $_GET['action'] ) ) {
			return;
		}

		$cert_id = llms_filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		if ( empty( $cert_id ) || ! current_user_can( get_post_type_object( 'llms_my_certificate' )->cap->edit_post, $cert_id ) ) {
			return;
		}

		if ( 'sync_awarded_certificate' === $_GET['action'] ) {
			( new LLMS_User_Certificate( $cert_id ) )->sync();
			wp_safe_redirect( get_edit_post_link( $cert_id, 'raw' ) );
			exit;
		}

	}

	/**
	 * Change shareable settings of a certificate.
	 *
	 * @since 4.5.0
	 *
	 * @param int  $cert_id    WP Post ID of the llms_my_certificate.
	 * @param bool $is_allowed Allow share the certificate or not.
	 * @return WP_Error|boolean Returns `true` on success and `false` on failure or an error object when the user does not have sufficient privileges.
	 */
	private function change_sharing_settings( $cert_id, $is_allowed ) {

		$cert = new LLMS_User_Certificate( $cert_id );

		if ( ! $cert->can_user_manage() ) {
			return new WP_Error( 'insufficient-permissions', __( 'You are not allowed to manage this certificate.', 'lifterlms' ) );
		}

		return $cert->set( 'allow_sharing', $is_allowed ? 'yes' : 'no' );

	}

	/**
	 * Delete a certificate.
	 *
	 * @since 3.18.0
	 * @since [version] Permanently delete certificate via wp_delete_post().
	 *
	 * @param int $cert_id WP Post ID of the llms_my_certificate.
	 * @return void
	 */
	private function delete( $cert_id ) {

		if ( ! is_admin() ) {
			return;
		}

		wp_delete_post( $cert_id, true );

	}

	/**
	 * Download a Certificate.
	 *
	 * Generates an HTML export of the certificate from the "Download" button
	 * on the View Certificate front end & on reporting backend for admins.
	 *
	 * @since 3.18.0
	 * @since 4.3.1 Properly use an `error` notice to display a WP_Error.
	 *
	 * @return void
	 */
	private function download( $cert_id ) {

		$filepath = LLMS()->certificates()->get_export( $cert_id );
		if ( is_wp_error( $filepath ) ) {
			// @todo Need to handle errors differently on admin panel.
			return llms_add_notice( $filepath->get_error_message(), 'error' );
		}

		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . basename( $filepath ) . '"' );

		readfile( $filepath ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile

		// Delete file after download.
		ignore_user_abort( true );
		wp_delete_file( $filepath );
		exit;
	}

}

return new LLMS_Controller_Certificates();
