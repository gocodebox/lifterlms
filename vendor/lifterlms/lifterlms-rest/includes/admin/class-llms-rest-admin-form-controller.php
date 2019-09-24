<?php
/**
 * Handle admin form submissions.
 *
 * @package  LifterLMS_REST/Admin/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Admin_Form_Controller class..
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.3 Added API credential download methods.
 */
class LLMS_REST_Admin_Form_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'handle_events' ) );

	}

	/**
	 * Handles submission of admin forms & nonce links.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.3 Added logic for handling api key txt download via nonce link.
	 *
	 * @return false|void
	 */
	public function handle_events() {

		if ( llms_verify_nonce( 'key-revoke-nonce', 'revoke', 'GET' ) ) {
			$delete = LLMS_REST_API()->keys()->delete( llms_filter_input( INPUT_GET, 'revoke-key', FILTER_VALIDATE_INT ) );
			if ( $delete ) {
				LLMS_Admin_Notices::flash_notice( esc_html__( 'The API Key has been successfully deleted.', 'lifterlms' ), 'success' );
				return llms_redirect_and_exit( admin_url( 'admin.php?page=llms-settings&tab=rest-api&section=keys' ) );
			}
		} elseif ( llms_verify_nonce( 'llms_rest_webhook_nonce', 'create-update-webhook', 'POST' ) ) {
			return $this->handle_webhook_upsert();
		} elseif ( llms_verify_nonce( 'delete-webhook-nonce', 'delete', 'GET' ) ) {
			$delete = LLMS_REST_API()->webhooks()->delete( llms_filter_input( INPUT_GET, 'delete-webhook', FILTER_VALIDATE_INT ) );
			if ( $delete ) {
				LLMS_Admin_Notices::flash_notice( esc_html__( 'The webhook has been successfully deleted.', 'lifterlms' ), 'success' );
				return llms_redirect_and_exit( admin_url( 'admin.php?page=llms-settings&tab=rest-api&section=webhooks' ) );
			}
		} elseif ( llms_verify_nonce( 'dl-key-nonce', 'dl-key', 'GET' ) ) {
			return $this->handle_key_download();
		}

		return false;

	}

	/**
	 * Generate and download a api key credentials file.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @return false|void
	 */
	protected function handle_key_download() {

		$info = $this->prepare_key_download();
		if ( ! $info ) {
			return false;
		}

		header( 'Content-type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . $info['fn'] );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Translators: %s = Consumer Key.
		printf( __( 'Consumer Key: %s', 'lifterlms' ), $info['ck'] );
		echo "\r\n";
		// Translators: %s = Consumer Secret.
		printf( __( 'Consumer Secret: %s', 'lifterlms' ), $info['cs'] );
		die();

	}

	/**
	 * Handle creating/updating a webhook via admin interfaces
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return true|void|WP_Error true on update success, void (redirect) on creation success, WP_Error on failure.
	 */
	protected function handle_webhook_upsert() {

		$data = array(
			'name'         => llms_filter_input( INPUT_POST, 'llms_rest_webhook_name', FILTER_SANITIZE_STRING ),
			'status'       => llms_filter_input( INPUT_POST, 'llms_rest_webhook_status', FILTER_SANITIZE_STRING ),
			'topic'        => llms_filter_input( INPUT_POST, 'llms_rest_webhook_topic', FILTER_SANITIZE_STRING ),
			'delivery_url' => llms_filter_input( INPUT_POST, 'llms_rest_webhook_delivery_url', FILTER_SANITIZE_URL ),
			'secret'       => llms_filter_input( INPUT_POST, 'llms_rest_webhook_secret', FILTER_SANITIZE_STRING ),
		);

		if ( 'action' === $data['topic'] ) {
			$data['topic'] .= '.' . llms_filter_input( INPUT_POST, 'llms_rest_webhook_action', FILTER_SANITIZE_STRING );
		}

		$hook_id = llms_filter_input( INPUT_POST, 'llms_rest_webhook_id', FILTER_SANITIZE_NUMBER_INT );

		if ( ! $hook_id ) {

			$hook = LLMS_REST_API()->webhooks()->create( $data );
			if ( ! is_wp_error( $hook ) ) {
				return llms_redirect_and_exit( $hook->get_edit_link(), array( 'status' => 301 ) );
			}
		} else {

			$hook = LLMS_REST_API()->webhooks()->get( $hook_id );
			if ( ! $hook ) {

				// Translators: %s = Webhook ID.
				$hook = new WP_Error( 'llms_rest_api_webhook_not_found', sprintf( __( '"%s" is not a valid Webhook.', 'lifterlms' ), $hook_id ) );

			} else {

				$data['id'] = $hook_id;
				$hook       = LLMS_REST_API()->webhooks()->update( $data );

			}
		}

		if ( is_wp_error( $hook ) ) {
			// Translators: %1$s = error message; %2$s = error code.
			LLMS_Admin_Notices::flash_notice( sprintf( __( 'Error: %1$s [Code: %2$s]', 'lifterlms' ), $hook->get_error_message(), $hook->get_error_code() ), 'error' );
			return $hook;
		}

		return true;

	}

	/**
	 * Validates `GET` information from the credential download URL and prepares information for generating the file.
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @return false|array
	 */
	protected function prepare_key_download() {

		$key_id       = llms_filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
		$consumer_key = llms_filter_input( INPUT_GET, 'ck', FILTER_SANITIZE_STRING );

		// return if missing required fields.
		if ( ! $key_id || ! $consumer_key ) {
			return false;
		}

		// return if key doesn't exist.
		$key = LLMS_REST_API()->keys()->get( $key_id );
		if ( ! $key ) {
			return false;
		}

		// validate the decoded consumer key looks like the stored truncated key.
		$consumer_key = base64_decode( $consumer_key );
		if ( substr( $consumer_key, -7 ) !== $key->get( 'truncated_key' ) ) {
			return false;
		}

		return array(
			'fn' => sanitize_file_name( $key->get( 'description' ) ) . '.txt',
			'ck' => $consumer_key,
			'cs' => $key->get( 'consumer_secret' ),
		);

	}

}

return new LLMS_REST_Admin_Form_Controller();
