<?php
/**
 * Assertions related to checking for WP_REST_Response objects
 *
 * @since Unknown
 */
trait LLMS_Unit_Test_Assertions_REST_Responses {

	/**
	 * Assert a WP_REST_Response code equals an expected code.
	 *
	 * @since Unknown
	 *
	 * @param string           $expected Expected response code.
	 * @param WP_REST_Response $response Response object.
	 * @return void
	 */
	protected function assertResponseCodeEquals( $expected, WP_REST_Response $response ) {

		$data = $response->get_data();
		$this->assertEquals( $expected, $data['code'] );

	}

	/**
	 * Assert a WP_REST_Response message equals an expected message.
	 *
	 * @since Unknown
	 *
	 * @param int              $expected Expected response message.
	 * @param WP_REST_Response $response Response object.
	 * @return void
	 */
	protected function assertResponseMessageEquals( $expected, WP_REST_Response $response ) {

		$data = $response->get_data();
		$this->assertEquals( $expected, $data['message'] );

	}

	/**
	 * Assert a WP_REST_Response status code equals an expected status code.
	 *
	 * @since Unknown
	 *
	 * @param int              $expected Expected response http status code.
	 * @param WP_REST_Response $response Response object.
	 * @return void
	 */
	protected function assertResponseStatusEquals( $expected, WP_REST_Response $response ) {

		$this->assertEquals( $expected, $response->get_status() );

	}

}
