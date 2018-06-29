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
	 * Determines if it's a request to the .com REST api
	 * @var  bool
	 */
	protected $is_rest_request = true;

	/**
	 * Construct an API call, parameters are passed to private `call()` function
	 * @param    stirng $resource  url endpoint or resource to make a request to
	 * @param    array  $data      array of data to pass in the body of the request
	 * @param    string $method    method of request (POST, GET, DELETE, PUT, etc...)
	 * @param    bool   $is_rest   if true adds wp-json rest to request url, otherwise requests to site base
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function __construct( $resource, $data, $method = null, $is_rest = true ) {

		$this->is_rest_request = $is_rest;
		parent::__construct( $resource, $data, $method );

	}

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
			$this->set_error( $msg, isset( $body['code'] ) ? $body['code'] : $response['response']['code'], $body );

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

		$url = 'https://lifterlms.com/';
		if ( $this->is_rest_request ) {
			$url .= 'wp-json/llms/v3';
		}

		return apply_filters( 'llms_dot_com_api_request_url', $url . $resource, $resource, $method );
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
