<?php
/**
 * Manage imports from lifterlms.com export API
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 4.8.0
 * @version 4.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Export API Class
 *
 * @since 4.8.0
 */
class LLMS_Export_API {

	/**
	 * Make an GET request to the exports API
	 *
	 * @since 4.8.0
	 *
	 * @param array $args Array of query string arguments formatted as an associative array.
	 * @return array|WP_Error
	 */
	protected static function call_api( $args ) {

		/**
		 * Filter the url used to make requests to the LifterLMS.com "exports" api.
		 *
		 * @since 4.8.0
		 *
		 * @param string $url API request url.
		 */
		$base_url = apply_filters(
			'llms_export_api_url',
			'https://academy.lifterlms.com/wp-json/llms-academy/v1/exports'
		);

		$req  = wp_safe_remote_get(
			add_query_arg( $args, $base_url ),
			array(
				'timeout' => 15,
			)
		);
		$body = json_decode( wp_remote_retrieve_body( $req ), true );
		if ( 200 === wp_remote_retrieve_response_code( $req ) ) {
			return $body;
		}

		// If there's a body it's a json encoded error object, otherwise it's already an error object.
		return $body && ! empty( $body['code'] ) ? new WP_Error( $body['code'], $body['message'], $body['data'] ) : $req;

	}

	/**
	 * Retrieve an import array by export IDs.
	 *
	 * @since 4.8.0
	 *
	 * @param int[] $ids Array of export IDs.
	 * @return array|WP_Error
	 */
	public static function get( $ids ) {

		$ids = implode( ',', array_map( 'absint', $ids ) );
		return self::call_api( compact( 'ids' ) );

	}


	/**
	 * Retrieve a list of available exports
	 *
	 * @since 4.8.0
	 *
	 * @param int $page     Results page.
	 * @param int $per_page Results per page.
	 * @return array[]|WP_Error
	 */
	public static function list( $page = 1, $per_page = 10 ) {
		return self::call_api( compact( 'page', 'per_page' ) );
	}

}
