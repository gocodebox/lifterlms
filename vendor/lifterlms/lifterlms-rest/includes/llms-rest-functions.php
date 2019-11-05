<?php
/**
 * REST functions
 *
 * @package LifterLMS_REST/Functions
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Generate a keyed hash value using the HMAC method with the key `llms-rest-api`
 *
 * @since 1.0.0-beta.1
 *
 * @param string $data Message to be hashed.
 * @return string
 */
function llms_rest_api_hash( $data ) {
	return hash_hmac( 'sha256', $data, 'llms-rest-api' );
}

/**
 * Wrapper function to execute async delivery of webhooks.
 *
 * Hooked to `lifterlms_rest_deliver_webhook_async`.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.2 Fixed incorrect reference.
 *
 * @see LLMS_REST_Webhook::schedule()
 *
 * @param int   $webhook_id Webhook id.
 * @param array $args Numeric array of arguments from the originating hook.
 * @return void
 */
function llms_rest_deliver_webhook_async( $webhook_id, $args ) {

	$webhook = LLMS_REST_API()->webhooks()->get( $webhook_id );
	if ( $webhook ) {
		$webhook->deliver( $args );
	}

}
add_action( 'lifterlms_rest_deliver_webhook_async', 'llms_rest_deliver_webhook_async', 10, 2 );

/**
 * Get data from a WP Rest API endpoint.
 *
 * @since 1.0.0-beta.1
 *
 * @param string $endpoint API endpoint, eg "/llms/v1/courses".
 * @param array  $params Query params to add to the request.
 * @return array|WP_Error
 */
function llms_rest_get_api_endpoint_data( $endpoint, $params = array() ) {

	$req = new WP_Rest_Request( 'GET', $endpoint );
	if ( $params ) {
		$req->set_query_params( $params );
	}

	$res    = rest_do_request( $req );
	$server = rest_get_server();
	$json   = wp_json_encode( $server->response_to_data( $res, false ) );

	return json_decode( $json, true );

}

/**
 * Generate a random hash.
 *
 * @since 1.0.0-beta.1
 *
 * @return string
 */
function llms_rest_random_hash() {
	if ( ! function_exists( 'openssl_random_pseudo_bytes' ) ) {
		return sha1( wp_rand() );
	}
	return bin2hex( openssl_random_pseudo_bytes( 20 ) );
}


