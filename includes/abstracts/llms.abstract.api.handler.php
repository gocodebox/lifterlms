<?php
defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS API Request Handler Abstract
 * @since   3.11.2
 * @version 3.24.0
 */
abstract class LLMS_Abstract_API_Handler {

	/**
	 * Default request method
	 * @var  string
	 */
	protected $default_request_method = 'POST';

	/**
	 * Send requests in JSON format
	 * @var  bool
	 */
	protected $is_json = true;

	/**
	 * Request timeout in seconds
	 * @var  integer
	 */
	protected $request_timeout = 60;

	private $result = null;
	private $error_message = null;
	private $error_object = null;
	private $error_type = null;

	/**
	 * Construct an API call, parameters are passed to private `call()` function
	 * @param    stirng $resource  url endpoint or resource to make a request to
	 * @param    array  $data      array of data to pass in the body of the request
	 * @param    string $method    method of request (POST, GET, DELETE, PUT, etc...)
	 * @return   void
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	public function __construct( $resource, $data, $method = null ) {

		$this->call( $resource, $data, $method );

	}

	/**
	 * Make an API call to stripe
	 * @param    stirng $resource  url endpoint or resource to make a request to
	 * @param    array  $data      array of data to pass in the body of the request
	 * @param    string $method    method of request (POST, GET, DELETE, PUT, etc...)
	 * @return   void
	 * @since    3.11.2
	 * @version  3.24.0
	 */
	private function call( $resource, $data, $method = null ) {

		$method = is_null( $method ) ? $this->default_request_method : $method;

		// setup the body
		$body = $this->set_request_body( $data, $method, $resource );
		if ( $this->is_json ) {
			$body = json_encode( $body );
		}

		// setup headers
		$headers = array();
		if ( $this->is_json ) {
			$headers['content-type'] = 'application/json; charset=utf-8';
		}

		// attempt to call the API
		$response = wp_safe_remote_request(
			$this->set_request_url( $resource, $method ),
			array(
				'body' => $body,
				'headers' => $this->set_request_headers( $headers, $resource, $method ),
				'method' => $method,
				'timeout' => $this->request_timeout,
				'user-agent' => $this->set_user_agent( 'LifterLMS ' . LLMS_VERSION, $resource, $method ),
			)
		);

		// connection error
		if ( is_wp_error( $response ) ) {

			return $this->set_error( __( 'There was a problem connecting to the payment gateway.', 'lifterlms' ), 'api_connection', $response );

		}

		// empty body
		if ( empty( $response['body'] ) ) {

			return $this->set_error( __( 'Empty Response.', 'lifterlms' ), 'empty_response', $response );

		}

		$this->parse_response( $response );

	}

	/**
	 * Retrive the private "error_message" variable
	 * @return   string
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	public function get_error_message() {

		return $this->error_message;

	}

	/**
	 * Get the private "error_object" variable
	 * @return   mixed
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	public function get_error_object() {

		return $this->error_object;

	}

	/**
	 * Retrive the private "error_type" variable
	 * @return   string
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	public function get_error_type() {

		return $this->error_type;

	}

	/**
	 * Retrive the private "result" variable
	 * @return   mixed
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	public function get_result() {

		return $this->result;

	}

	/**
	 * Determine if the response is an error
	 * @return   boolean
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	public function is_error() {

		return is_wp_error( $this->get_result() );

	}

	/**
	 * Parse the body of the response and set a success/error
	 * @param    array     $response  response data
	 * @return   array
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	abstract protected function parse_response( $response );

	/**
	 * Set an Error
	 * Sets all error variables and sets the result as a WP_Error so the result can always be tested with `is_wp_error()`
	 *
	 * @param    string $message  error message
	 * @param    string $type     error code or type
	 * @param    object $obj      full error object or api response
	 * @return   void
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	protected function set_error( $message, $type, $obj ) {

		$this->result = new WP_Error( $type, $message, $obj );
		$this->error_type = $type;
		$this->error_message = $message;
		$this->error_object = $obj;

	}

	/**
	 * Set the result
	 * @param    mixed     $result  result data
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	protected function set_result( $result ) {
		$this->result = $result;
	}

	/**
	 * Set request body
	 * @param    array      $data      request body
	 * @param    string     $method    request method
	 * @param    string     $resource  requested resource
	 * @return   array
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	abstract protected function set_request_body( $data, $method, $resource );

	/**
	 * Set request headers
	 * @param    array      $headers   default request headers
	 * @param    string     $resource  request resource
	 * @param    string     $method    request method
	 * @return   array
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	protected function set_request_headers( $headers, $resource, $method ) {
		return $headers;
	}

	/**
	 * Set the request URL
	 * @param    string     $resource  requested resource
	 * @param    string     $method    request method
	 * @return   string
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	abstract protected function set_request_url( $resource, $method );

	/**
	 * Set the request User Agent
	 * Can be overridden by extending classes when necessary
	 * @param    string     $user_agent  default user agent (LifterLMS {$version})
	 * @param    string     $resource    requested resource
	 * @param    string     $method      request method
	 * @return   string
	 * @since    3.22.0
	 * @version  3.22.0
	 */
	protected function set_user_agent( $user_agent, $resource, $method ) {
		return $user_agent;
	}

}
