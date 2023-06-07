<?php
/**
 * Base test case for all tests
 *
 * @since Unknown
 */
class LLMS_REST_Unit_Test_Case extends WP_UnitTestCase {

	use LLMS_Unit_Test_Case_Base {
		set_up as base_set_up;
		tear_down as base_tear_Down;
	}
	use LLMS_Unit_Test_Assertions_REST_Responses;

	/**
	 * Route being tested by the class
	 *
	 * EG: /llms/v1/courses
	 *
	 * @var string
	 */
	protected $route = '';

	/**
	 * Setup the test case
	 *
	 * @since Unknown
	 * @since 3.0.0 Renamed from `setUp()` for WP core compat.
	 *
	 * @return void
	 */
	public function set_up() {

		$this::base_set_up();
		do_action( 'rest_api_init' );
		$this->server = rest_get_server();

	}

	/**
	 * Unset the server.
	 *
	 * @since Unknown
	 * @since 3.0.0 Renamed from `tearDown()` for WP core compat.
	 *
	 * @return void
	 */
	public function tear_down() {

		$this::base_tear_Down();

		global $wp_rest_server;
		unset( $this->server );

		$wp_rest_server = null;

	}

	/**
	 * Preform a mock WP_REST_Request
	 *
	 * @since Unknown
	 * @since 4.1.0 Added `$headers` parameter.
	 *
	 * @param string $method  Request method.
	 * @param string $route   Request route, eg: '/llms/v1/courses'.
	 * @param array  $body    Optional request body.
	 * @param array  $query   Optional query arguments.
	 * @param array  $headers Optional header arguments.
	 * @return WP_REST_Response.
	 */
	protected function perform_mock_request( $method, $route, $body = array(), $query = array(), $headers = array() ) {

		$request = new WP_REST_Request( $method, $route );
		if ( $body ) {
			$request->set_body_params( $body );
		}
		if ( $query ) {
			$request->set_query_params( $query );
		}
		if ( $headers ) {
			$request->set_headers( $headers );
		}
		return $this->server->dispatch( $request );

	}

}
