<?php
/**
 * REST Server functions
 *
 * @package LifterLMS_REST/Functions
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.18
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return a WP_Error with proper code, message and status for unauthorized requests.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.12 Added a second paramater to avoid checking if the user is logged in.
 * @since 1.0.0-beta.18 Use WP_Http constants for the error status.
 *
 * @param string  $message             Optional. The custom error message. Default empty string.
 *                                     When no custom message is provided a predefined message will be used.
 * @param boolean $check_authenticated Optional. Whether or not checking if the current user is logged in. Default `true`.
 * @return WP_Error
 */
function llms_rest_authorization_required_error( $message = '', $check_authenticated = true ) {
	if ( $check_authenticated && is_user_logged_in() ) {
		// 403.
		$error_code = 'llms_rest_forbidden_request';
		$_message   = __( 'You are not authorized to perform this request.', 'lifterlms' );
		$status     = WP_Http::FORBIDDEN; // 403.
	} else {
		// 401.
		$error_code = 'llms_rest_unauthorized_request';
		$_message   = __( 'The API credentials were invalid.', 'lifterlms' );
		$status     = WP_Http::UNAUTHORIZED; // 401.
	}

	$message = ! $message ? $_message : $message;
	return new WP_Error( $error_code, $message, array( 'status' => $status ) );
}

/**
 * Return a WP_Error with proper code, message and status for invalid or malformed request syntax.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.18 Use WP_Http constant for the error status.
 *
 * @param string $message Optional. The custom error message. Default empty string.
 *                        When no custom message is provided a predefined message will be used.
 * @return WP_Error
 */
function llms_rest_bad_request_error( $message = '' ) {
	$message = ! $message ? __( 'Invalid or malformed request syntax.', 'lifterlms' ) : $message;
	return new WP_Error( 'llms_rest_bad_request', $message, array( 'status' => WP_Http::BAD_REQUEST ) ); // 400.
}

/**
 * Return a WP_Error with proper code, message and status for not found resources.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.18 Use WP_Http constant for the error status.
 *
 * @param string $message Optional. The custom error message. Default empty string.
 *                        When no custom message is provided a predefined message will be used.
 * @return WP_Error
 */
function llms_rest_not_found_error( $message = '' ) {
	$message = ! $message ? __( 'The requested resource could not be found.', 'lifterlms' ) : $message;
	return new WP_Error( 'llms_rest_not_found', $message, array( 'status' => WP_Http::NOT_FOUND ) ); // 404.
}

/**
 * Return a WP_Error for a 500 Internal Server Error.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.18 Use WP_Http constant for the error status.
 *
 * @param string $message Optional. Custom error message. When none provided a predefined message is used.
 * @return WP_Error
 */
function llms_rest_server_error( $message = '' ) {
	$message = ! $message ? __( 'Internal Server Error.', 'lifterlms' ) : $message;
	return new WP_Error( 'llms_rest_server_error', $message, array( 'status' => WP_Http::INTERNAL_SERVER_ERROR ) ); // 500.
}

/**
 * Checks whether or not the passed object is a 401 (permission) or 403 (authorization) error.
 *
 * @since 1.0.0-beta.18
 *
 * @param WP_Error $wp_error The WP_Error object to check.
 * @return boolean
 */
function llms_rest_is_authorization_required_error( $wp_error ) {
	return ! empty( array_intersect( llms_rest_get_all_error_statuses( $wp_error ), array( WP_Http::FORBIDDEN, WP_Http::UNAUTHORIZED ) ) ); // 403, 401.
}

/**
 * Checks whether or not the passed object is a 400 bad request error.
 *
 * @since 1.0.0-beta.18
 *
 * @param WP_Error $wp_error The WP_Error object to check.
 * @return boolean
 */
function llms_rest_is_bad_request_error( $wp_error ) {
	return in_array( WP_Http::BAD_REQUEST, llms_rest_get_all_error_statuses( $wp_error ), true ); // 400.
}

/**
 * Checks whether or not the passed object is a 404 not found error.
 *
 * @since 1.0.0-beta.18
 *
 * @param WP_Error $wp_error The WP_Error object to check.
 * @return boolean
 */
function llms_rest_is_not_found_error( $wp_error ) {
	return in_array( WP_Http::NOT_FOUND, llms_rest_get_all_error_statuses( $wp_error ), true ); // 404.
}

/**
 * Checks whether or not the passed object is a 500 internal server error.
 *
 * @since 1.0.0-beta.18
 *
 * @param WP_Error $wp_error The WP_Error object to check.
 * @return boolean
 */
function llms_rest_is_server_error( $wp_error ) {
	return in_array( WP_Http::INTERNAL_SERVER_ERROR, llms_rest_get_all_error_statuses( $wp_error ), true ); // 500.
}

/**
 * Returns all the error statuses of a WP_Error.
 *
 * @since 1.0.0-beta.18
 *
 * @param WP_Error $wp_error The WP_Error object.
 * @return int[]
 */
function llms_rest_get_all_error_statuses( $wp_error ) {
	$statuses = array();

	if ( is_wp_error( $wp_error ) && ! empty( $wp_error->has_errors() ) ) {
		/**
		 * The method `get_all_error_data()` has been introduced in wp 5.6.0.
		 * TODO: remove bw compatibility when min wp version will be raised above 5.6.0.
		 */
		global $wp_version;
		$func = ( version_compare( $wp_version, 5.6, '>=' ) ) ? 'get_all_error_data' : 'get_error_data';

		foreach ( $wp_error->get_error_codes() as $code ) {
			$status = $wp_error->{$func}( $code );
			$status = 'get_error_data' === $func ? array( $status ) : $status;
			/**
			 * Use native `array_column()` in place of `wp_list_pluck()` as:
			 * 1) `$status` is fors ure an array (and not possibly an object);
			 * 2) `wp_list_pluck()` raises an error if the key ('status' in this case) is not found.
			 */
			$statuses = array_merge( $statuses, array_column( $status, 'status' ) );
		}
		$statuses = array_filter( array_unique( $statuses ) );
	}

	return $statuses;

}

/**
 * Validate submitted array of integers is an array of real user ids.
 *
 * @since 1.0.0-beta.9
 *
 * @param array $instructors Array of instructors id.
 * @return boolean
 */
function llms_validate_instructors( $instructors ) {
	return ! empty( $instructors ) ? count( array_filter( array_map( 'get_userdata', $instructors ) ) ) === count( $instructors ) : false;
}

/**
 * Validate strict positive integer number.
 *
 * @since 1.0.0-beta.18
 *
 * @param integer $number Integer number to validate.
 * @return boolean
 */
function llms_rest_validate_strictly_positive_int( $number ) {
	return llms_rest_validate_positive_int( $number, false );
}

/**
 * Validate positive integer number including zero.
 *
 * @since 1.0.0-beta.18
 *
 * @param integer $number Integer number to validate.
 * @return boolean
 */
function llms_rest_validate_positive_int_w_zero( $number ) {
	return llms_rest_validate_positive_int( $number );
}


/**
 * Validate positive integer number.
 *
 * @since 1.0.0-beta.18
 *
 * @param integer $number       Integer number to validate.
 * @param boolean $include_zero Optional. Whether or not 0 is included. Default is `true`.
 * @return boolean
 */
function llms_rest_validate_positive_int( $number, $include_zero = true ) {
	return false !== filter_var(
		$number,
		FILTER_VALIDATE_INT,
		array(
			'options' => array(
				'min_range' => $include_zero ? 0 : 1,
			),
		)
	);
}

/**
 * Validate strict positive float number.
 *
 * @since 1.0.0-beta.18
 *
 * @param integer $number Float number to validate.
 * @return boolean
 */
function llms_rest_validate_strictly_positive_float( $number ) {
	return llms_rest_validate_positive_float( $number, false );
}

/**
 * Validate strict positive float number including zero.
 *
 * @since 1.0.0-beta.18
 *
 * @param integer $number Float number to validate.
 * @return boolean
 */
function llms_rest_validate_positive_float_w_zero( $number ) {
	return llms_rest_validate_positive_float( $number );
}

/**
 * Validate strict positive float number.
 *
 * @since 1.0.0-beta.18
 *
 * @param integer $number       Float number to validate.
 * @param boolean $include_zero Optional. Whether or not 0 is included. Default is `true`.
 * @return boolean
 */
function llms_rest_validate_positive_float( $number, $include_zero = true ) {
	// @TODO min_range and max_range options for FILTER_VALIDATE_FLOAT are only available since PHP 7.4.
	$is_float = false !== filter_var( (float) $number, FILTER_VALIDATE_FLOAT );
	return $is_float && ( $include_zero ? $number >= 0 : $number > 0 );
}


/**
 * Validate submitted integer, or array of integers is an array of real memberships id, or empty.
 *
 * @since 1.0.0-beta.18
 *
 * @param int|int[] $memberships Array of memberships id.
 * @param boolean   $allow_empty Optional. Whether or not allowing empty lists. Default false.
 * @return boolean
 */
function llms_rest_validate_memberships( $memberships, $allow_empty = false ) {
	return llms_rest_validate_post_types( $memberships, 'llms_membership', $allow_empty );
}


/**
 * Validate submitted array of integers is an array of real courses id, or empty.
 *
 * @since 1.0.0-beta.18
 *
 * @param int|int[] $courses     Array of courses id.
 * @param boolean   $allow_empty Optional. Whether or not allowing empty lists. Default false.
 * @return boolean
 */
function llms_rest_validate_courses( $courses, $allow_empty = false ) {
	return llms_rest_validate_post_types( $courses, 'course', $allow_empty );
}

/**
 * Validate submitted array of integers is an array of real courses/memberships id, or empty.
 *
 * @since 1.0.0-beta.18
 *
 * @param int|int[] $products    Array of courses/memberships id.
 * @param boolean   $allow_empty Optional. Whether or not allowing empty lists. Default false.
 * @return boolean
 */
function llms_rest_validate_products( $products, $allow_empty = false ) {
	return llms_rest_validate_post_types( $products, array( 'course', 'llms_membership' ), $allow_empty );
}

/**
 * Validate submitted array of integers is an array of real post types id, or empty.
 *
 * @param int|int[]       $ids         A single or a list of post IDs.
 * @param string|string[] $post_types  A single or a list of post types to check against.
 * @param boolean         $allow_empty Optional. Whether or not allowing empty lists. Default false.
 * @return boolean
 */
function llms_rest_validate_post_types( $ids, $post_types, $allow_empty = false ) {

	$ids = is_array( $ids ) ? $ids : array( $ids );
	$ids = array_filter( $ids );

	if ( empty( $ids ) ) {
		return $allow_empty;
	}

	$valid      = true;
	$post_types = is_array( $post_types ) ? $post_types : array( $post_types );

	if ( ! empty( $ids ) ) {
		$real_post_types = array_filter(
			$ids,
			function( $id ) use ( $post_types ) {
				return ( is_numeric( $id ) && in_array( get_post_type( (int) $id ), $post_types, true ) );
			}
		);

		$valid = count( $real_post_types ) === count( $ids );
	}

	return $valid;

}
