<?php
/**
 * Test REST Server functions
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group rest_server
 * @group rest_functions
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_Server_Functions extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Test the llms_rest_authorization_required_error() function when logged out.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_llms_rest_authorization_required_error_logged_out() {

		// Default.
		$err = llms_rest_authorization_required_error();
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 401 ), $err );
		$this->assertWPErrorMessageEquals( 'The API credentials were invalid.', $err );

		//  Custom message.
		$err = llms_rest_authorization_required_error( 'My message.' );
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 401 ), $err );
		$this->assertWPErrorMessageEquals( 'My message.', $err );

	}

	/**
	 * Test the llms_rest_authorization_required_error() function when logged in.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_llms_rest_authorization_required_error_logged_in() {

		wp_set_current_user( $this->factory->user->create() );

		// Default.
		$err = llms_rest_authorization_required_error();
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_forbidden_request', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 403 ), $err );
		$this->assertWPErrorMessageEquals( 'You are not authorized to perform this request.', $err );

		//  Custom message.
		$err = llms_rest_authorization_required_error( 'My message.' );
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_forbidden_request', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 403 ), $err );
		$this->assertWPErrorMessageEquals( 'My message.', $err );

	}

	/**
	 * Test the llms_rest_bad_request_error() function.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_llms_rest_bad_request_error() {

		// Default.
		$err = llms_rest_bad_request_error();
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_bad_request', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 400 ), $err );
		$this->assertWPErrorMessageEquals( 'Invalid or malformed request syntax.', $err );

		// Custom message.
		$err = llms_rest_bad_request_error( 'My message.' );
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_bad_request', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 400 ), $err );
		$this->assertWPErrorMessageEquals( 'My message.', $err );

	}

	/**
	 * Test the llms_rest_not_found_error() function.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_llms_rest_not_found_error() {

		// Default.
		$err = llms_rest_not_found_error();
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_not_found', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 404 ), $err );
		$this->assertWPErrorMessageEquals( 'The requested resource could not be found.', $err );

		// Custom message.
		$err = llms_rest_not_found_error( 'My message.' );
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_not_found', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 404 ), $err );
		$this->assertWPErrorMessageEquals( 'My message.', $err );

	}

	/**
	 * Test the llms_rest_server_error() function.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_llms_rest_server_error() {

		// Default.
		$err = llms_rest_server_error();
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_server_error', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 500 ), $err );
		$this->assertWPErrorMessageEquals( 'Internal Server Error.', $err );

		// Custom message.
		$err = llms_rest_server_error( 'My message.' );
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_server_error', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 500 ), $err );
		$this->assertWPErrorMessageEquals( 'My message.', $err );

	}

}
