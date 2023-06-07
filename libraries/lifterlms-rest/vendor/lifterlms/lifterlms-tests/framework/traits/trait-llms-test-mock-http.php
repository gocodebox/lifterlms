<?php
/**
 * Mock HTTP requests made via `wp_remote_request()`.
 *
 * @since 1.5.0
 * @version 4.0.0
 */
trait LLMS_Unit_Test_Mock_Http {

	/**
	 * Array of requests to mock.
	 *
	 * @var array
	 */
	protected $_mock_http_requests = array();

	/**
	 * Setup mock http request data.
	 *
	 * @since 1.5.0
	 * @since 4.0.0 Updated to allow mocking multiple consecutive requests.
	 *
	 * @param string $url_to_mock The URL to mock.
	 * @param array|obj|WP_Error $mock_return The mock data to respond with.
	 * @return void
	 */
	protected function mock_http_request( $url_to_mock, $mock_return = array(), $fuzzy = false ) {

		$this->_mock_http_requests[] = array(
			'url'    => $url_to_mock,
			'return' => $mock_return,
			'fuzzy'  => $fuzzy,
		);

		if ( ! has_filter( 'pre_http_request', array( $this, '_handle_mock_http_request' ) ) ) {
			add_filter( 'pre_http_request', array( $this, '_handle_mock_http_request' ), 10, 3 );
		}

	}

	/**
	 * Mock `wp_remote_request` via the `pre_http_request`
	 *
	 * @since 1.5.0
	 * @since 4.0.0 Updated to allow mocking multiple consecutive requests.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/pre_http_request/
	 *
	 * @param false|array|WP_Error $ret  Whether to preempt the response.
	 * @param array                $args HTTP Request args.
	 * @param string               $url  Request url.
	 * @return false|array|WP_Error
	 */
	public function _handle_mock_http_request( $ret, $args, $url ) {

		// Loop through all mocked requests.
		foreach ( $this->_mock_http_requests as $i => $mock_data ) {

			// This is a valid URL to mock.
			if ( ( $mock_data['fuzzy'] && false !== strpos( $url, $mock_data['url'] ) ) || $url === $mock_data['url'] ) {

				// Return the mock return data.
				$ret = $mock_data['return'];

				// Remove the request.
				unset( $this->_mock_http_requests[ $i ] );
				break;

			}

		}

		// No more requests to mock.
		if ( empty( $this->_mock_http_requests ) ) {
			remove_filter( 'pre_http_request', array( $this, '_handle_mock_http_request' ), 10 );
		}

		return $ret;

	}

}
