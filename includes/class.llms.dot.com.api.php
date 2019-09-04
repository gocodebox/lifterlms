<?php
defined( 'ABSPATH' ) || exit;

/**
 * Interact with the LifterLMS.com API
 *
 * @since    3.22.0
 * @version  3.22.0
 */
class LLMS_Dot_Com_API extends LLMS_Abstract_API_Handler {

	/**
	 * Send requests in JSON format
	 *
	 * @var  bool
	 */
	protected $is_json = false;

	/**
	 * Determines if it's a request to the .com REST api
	 *
	 * @var  bool
	 */
	protected $is_rest = true;

	/**
	 * Construct an API call, parameters are passed to private `call()` function
	 *
	 * @param    stirng $resource  url endpoint or resource to make a request to
	 * @param    array  $data      array of data to pass in the body of the request
	 * @param    string $method    method of request (POST, GET, DELETE, PUT, etc...)
	 * @param    bool   $is_rest   if true adds wp-json rest to request url, otherwise requests to site base
	 * @return   void
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function __construct( $resource, $data, $method = null, $is_rest = true ) {

		$this->is_rest = $is_rest;
		parent::__construct( $resource, $data, $method );

	}

	/**
	 * Determine if the current request is a rest request
	 *
	 * @return   bool
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	public function is_rest_request() {
		return $this->is_rest;
	}

	/**
	 * Parse the body of the response and set a success/error
	 *
	 * @param    array $response  response data
	 * @return   void
	 * @since    3.22.0
	 * @version  3.22.0
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
	 *
	 * @param    array  $data      request body
	 * @param    string $method    request method
	 * @param    string $resource  requested resource
	 * @return   array
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	protected function set_request_body( $data, $method, $resource ) {
		return apply_filters( 'llms_dot_com_api_request_body', $data, $method, $resource, $this );
	}

	/**
	 * Set request headers
	 *
	 * @param    array  $headers   default request headers
	 * @param    string $resource  request resource
	 * @param    string $method    request method
	 * @return   array
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	protected function set_request_headers( $headers, $resource, $method ) {
		return apply_filters( 'llms_dot_com_api_request_headers', $headers, $resource, $method, $this );
	}

	/**
	 * Set the request URL
	 *
	 * @param    string $resource  requested resource
	 * @param    string $method    request method
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	protected function set_request_url( $resource, $method ) {

		$url = 'https://lifterlms.com';
		if ( $this->is_rest_request() ) {
			$url .= '/wp-json/llms/v3';
		}

		return apply_filters( 'llms_dot_com_api_request_url', $url . $resource, $resource, $method, $this );
	}

	/**
	 * Set the request User Agent
	 * Can be overridden by extending classes when necessary
	 *
	 * @param    string $user_agent  default user agent (LifterLMS {$version})
	 * @param    string $resource    requested resource
	 * @param    string $method      request method
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	protected function set_user_agent( $user_agent, $resource, $method ) {
		return sprintf( 'LifterLMS/%1$s (%2$s)', LLMS_VERSION, get_site_url() );
	}

}
