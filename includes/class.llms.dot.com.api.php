<?php
defined( 'ABSPATH' ) || exit;

/**
 * Interact with the LifterLMS.com API
 * @since    [version]
 * @version  [version]
 */
class LLMS_Dot_Com_API extends LLMS_Abstract_API_Handler {

	/**
	 * Send requests in JSON format
	 * @var  bool
	 */
	protected $is_json = false;

	/**
	 * Parse the body of the response and set a success/error
	 * @param    array     $response  response data
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function parse_response( $response ) {

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $response['response'] ) && isset( $response['response']['code'] ) && ! in_array( $response['response']['code'], array( 200, 201 ) ) ) {

			$msg = isset( $body['message'] ) ? $body['message'] : $response['response']['message'];
			$this->set_error( $msg, $body['code'], $body );

		} else {

			$this->set_result( $body );

		}

	}

	/**
	 * Set request body
	 * @param    array      $data      request body
	 * @param    string     $method    request method
	 * @param    string     $resource  requested resource
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_request_body( $data, $method, $resource ) {
		return apply_filters( 'llms_dot_com_api_request_body', $data );
	}

	/**
	 * Set request headers
	 * @param    array      $headers   default request headers
	 * @param    string     $resource  request resource
	 * @param    string     $method    request method
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_request_headers( $headers, $resource, $method ) {
		return apply_filters( 'llms_dot_com_api_request_headers', $headers );
	}

	/**
	 * Set the request URL
	 * @param    string     $resource  requested resource
	 * @param    string     $method    request method
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_request_url( $resource, $method ) {
		return apply_filters( 'llms_dot_com_api_request_url', 'https://lifterlms.com/wp-json/llms/v3' . $resource, $resource, $method );
	}

	/**
	 * Set the request User Agent
	 * Can be overridden by extending classes when necessary
	 * @param    string     $user_agent  default user agent (LifterLMS {$version})
	 * @param    string     $resource    requested resource
	 * @param    string     $method      request method
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_user_agent( $user_agent, $resource, $method ) {
		return sprintf( 'LifterLMS/%1$s (%2$s)', LLMS_VERSION, get_site_url() );
	}

}
