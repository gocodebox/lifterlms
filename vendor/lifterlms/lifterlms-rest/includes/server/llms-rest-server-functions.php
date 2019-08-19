<?php
/**
 * REST Server functions
 *
 * @package LifterLMS_REST/Functions
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return a WP_Error with proper code, message and status for unauthorized requests.
 *
 * @since 1.0.0-beta.1
 *
 * @param string $message Optional. The custom error message. Default empty string.
 *                        When no custom message is provided a predefined message will be used.
 * @return WP_Error
 */
function llms_rest_authorization_required_error( $message = '' ) {
	if ( is_user_logged_in() ) {
		// 403.
		$error_code = 'llms_rest_forbidden_request';
		$_message   = __( 'You are not authorized to perform this request.', 'lifterlms' );
	} else {
		// 401.
		$error_code = 'llms_rest_unauthorized_request';
		$_message   = __( 'The API credentials were invalid.', 'lifterlms' );
	}

	$message = ! $message ? $_message : $message;
	return new WP_Error( $error_code, $message, array( 'status' => rest_authorization_required_code() ) );
}

/**
 * Return a WP_Error with proper code, message and status for invalid or malformed request syntax.
 *
 * @since 1.0.0-beta.1
 *
 * @param string $message Optional. The custom error message. Default empty string.
 *                        When no custom message is provided a predefined message will be used.
 * @return WP_Error
 */
function llms_rest_bad_request_error( $message = '' ) {
	$message = ! $message ? __( 'Invalid or malformed request syntax.', 'lifterlms' ) : $message;
	return new WP_Error( 'llms_rest_bad_request', $message, array( 'status' => 400 ) );
}

/**
 * Return a WP_Error with proper code, message and status for not found resources.
 *
 * @since 1.0.0-beta.1
 *
 * @param string $message Optional. The custom error message. Default empty string.
 *                        When no custom message is provided a predefined message will be used.
 * @return WP_Error
 */
function llms_rest_not_found_error( $message = '' ) {
	$message = ! $message ? __( 'The requested resource could not be found.', 'lifterlms' ) : $message;
	return new WP_Error( 'llms_rest_not_found', $message, array( 'status' => 404 ) );
}

/**
 * Return a WP_Error for a 500 Internal Server Error.
 *
 * @since 1.0.0-beta.1
 *
 * @param string $message Optional. Custom error message. When none provided a predefined message is used.
 * @return WP_Error
 */
function llms_rest_server_error( $message = '' ) {
	$message = ! $message ? __( 'Internal Server Error.', 'lifterlms' ) : $message;
	return new WP_Error( 'llms_rest_server_error', $message, array( 'status' => 500 ) );
}


