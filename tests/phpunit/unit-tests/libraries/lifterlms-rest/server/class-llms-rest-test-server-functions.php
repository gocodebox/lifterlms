<?php
/**
 * Test REST Server functions
 *
 * @package LifterLMS_REST/Tests
 *
 * @group rest_server
 * @group rest_functions
 *
 * @since 1.0.0-beta.1
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
	 * Test the llms_rest_authorization_required_error() function `$check_authenticated` parameter.
	 *
	 * @since 1.0.0-beta.12
	 *
	 * @return void
	 */
	public function test_llms_rest_authorization_required_error_with_check_authenticated_false() {

		// Default.
		$err = llms_rest_authorization_required_error('', false);
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 401 ), $err );
		$this->assertWPErrorMessageEquals( 'The API credentials were invalid.', $err );

		//  Custom message.
		$err = llms_rest_authorization_required_error( 'My message.', false );
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 401 ), $err );
		$this->assertWPErrorMessageEquals( 'My message.', $err );

		// Log in.
		wp_set_current_user( $this->factory->user->create() );

		// Default.
		$err = llms_rest_authorization_required_error('', false);
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 401 ), $err );
		$this->assertWPErrorMessageEquals( 'The API credentials were invalid.', $err );

		//  Custom message.
		$err = llms_rest_authorization_required_error( 'My message.', false );
		$this->assertIsWPError( $err );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $err );
		$this->assertWPErrorDataEquals( array( 'status' => 401 ), $err );
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

	/**
	 * Test the llms_rest_is_authorization_required_error() function
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_llms_rest_is_authorization_required_error() {
		// Log out to check 401.
		wp_set_current_user( 0 );

		// True.
		$err = llms_rest_authorization_required_error();
		$this->assertTrue( llms_rest_is_authorization_required_error( $err ) );
		$this->assertWPErrorDataEquals( array( 'status' => 401 ), $err );

		// False.
		$err = llms_rest_server_error();
		$this->assertFalse( llms_rest_is_authorization_required_error( $err ) );

		// Passing something different than a WP_Error: False.
		$err = 3;
		$this->assertFalse( llms_rest_is_authorization_required_error( $err ) );

		// Passing a WP_Error with no errors.
		$err = new WP_Error();
		$this->assertFalse( llms_rest_is_authorization_required_error( $err ) );

		// Log in to check 403.
		wp_set_current_user( $this->factory->user->create() );

		// True.
		$err = llms_rest_authorization_required_error();
		$this->assertTrue( llms_rest_is_authorization_required_error( $err ) );
		$this->assertWPErrorDataEquals( array( 'status' => 403 ), $err );

		// False.
		$err = llms_rest_server_error();
		$this->assertFalse( llms_rest_is_authorization_required_error( $err ) );

		// Passing something different than a WP_Error: False.
		$err = 3;
		$this->assertFalse( llms_rest_is_authorization_required_error( $err ) );

		// Passing a WP_Error with no errors.
		$err = new WP_Error();
		$this->assertFalse( llms_rest_is_authorization_required_error( $err ) );

	}

	/**
	 * Test the llms_rest_is_bad_request_error() function
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_llms_rest_is_bad_request_error() {

		// True.
		$err = llms_rest_bad_request_error();
		$this->assertTrue( llms_rest_is_bad_request_error( $err ) );

		// False.
		$err = llms_rest_server_error();
		$this->assertFalse( llms_rest_is_bad_request_error( $err ) );

		// Passing something different than a WP_Error: False.
		$err = 3;
		$this->assertFalse( llms_rest_is_bad_request_error( $err ) );

		// Passing a WP_Error with no errors.
		$err = new WP_Error();
		$this->assertFalse( llms_rest_is_bad_request_error( $err ) );
	}

	/**
	 * Test the llms_rest_is_not_found_error() function
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_llms_rest_is_not_found_error() {

		// True.
		$err = llms_rest_not_found_error();
		$this->assertTrue( llms_rest_is_not_found_error( $err ) );

		// False.
		$err = llms_rest_server_error();
		$this->assertFalse( llms_rest_is_not_found_error( $err ) );

		// Passing something different than a WP_Error: False.
		$err = 3;
		$this->assertFalse( llms_rest_is_not_found_error( $err ) );

		// Passing a WP_Error with no errors.
		$err = new WP_Error();
		$this->assertFalse( llms_rest_is_not_found_error( $err ) );
	}

	/**
	 * Test the llms_is_rest_server_error() function
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_llms_rest_is_server_error() {

		// True.
		$err = llms_rest_server_error();
		$this->assertTrue( llms_rest_is_server_error( $err ) );

		// False.
		$err = llms_rest_not_found_error();
		$this->assertFalse( llms_rest_is_server_error( $err ) );

		// Passing something different than a WP_Error: False.
		$err = 3;
		$this->assertFalse( llms_rest_is_server_error( $err ) );

		// Passing a WP_Error with no errors.
		$err = new WP_Error();
		$this->assertFalse( llms_rest_is_server_error( $err ) );

	}

	/**
	 * Test llms_rest_get_all_error_statuses() function
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_get_all_error_status() {
		$err = llms_rest_server_error();
		// Additional data for the same error code (only makes sense with wp 5.6+).
		$err->add_data(
			array(
				'status' => 2000
			)
		);

		$err->add(
			'code',
			'meassage',
			array(
				'status' => 800
			)
		);
		$err->add(
			'code_2',
			'message',
			array(
				200
			)
		);
		$err->add_data(
			array(
				'status' => 500 // Check duplicates.
			)
		);

		$expected = array(
			500,
			800,
		);

		/**
		 * since WordPress 5.6.0 Errors can now contain more than one item of error data. {@see WP_Error::$additional_data}.
		 */
		global $wp_version;
		if ( version_compare( $wp_version, 5.6, '>=' ) ) {
			$expected[] = 2000;
		}

		$this->assertEqualSets( $expected, llms_rest_get_all_error_statuses( $err ) );

		// Check empty error results in empty array returned by the function.
		$err = new WP_Error();
		$this->assertEquals( array(), llms_rest_get_all_error_statuses( $err ) );

		// Check non WP_Error results in empty array returned by the function.
		$err = new stdClass();
		$this->assertEquals( array(), llms_rest_get_all_error_statuses( $err ) );

	}

	/**
	 * Test llms_rest_validate_memberships()
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_llms_rest_validate_memberships() {
		$this->validate_post_types_tests( 'llms_rest_validate_memberships', 'llms_membership' );
	}

	/**
	 * Test llms_rest_validate_courses()
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_llms_rest_validate_courses() {
		$this->validate_post_types_tests( 'llms_rest_validate_courses', 'course' );
	}

	/**
	 * Test llms_rest_validate_products()
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_llms_rest_validate_products() {
		$course     = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$membership = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );

		$this->validate_post_types_tests( 'llms_rest_validate_products', '', array( $course, $membership ) );

		// Test mixed with a standard post.
		$this->assertFalse( llms_rest_validate_products( array( $course, $membership, $this->factory->post->create() ) ) );
	}

	/**
	 * Test validate llms_rest_validate_post_types
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	private function validate_post_types_tests( $func, $post_type = '', $posts = array() ) {

		// Test an empty array.
		$this->assertTrue( $func( array(), true ), $func ); // Allowed.

		$this->assertFalse( $func( array() ), $func );

		// Create 2 pt.
		$pts = empty( $posts ) ? $this->factory->post->create_many( 2, array( 'post_type' => $post_type ) ) : $posts;

		// Test a not existing pt.
		$this->assertFalse( $func( end( $pts ) + 1 ), $func );

		// Test an array of non existing pt.
		$this->assertFalse( $func( array( end( $pts ) + 1, end( $pts ) + 2 ) ), $func );

		// Test an array of an existing and non existing pt.
		$this->assertFalse( $func( array( end( $pts ), end( $pts ) + 2 ) ), $func );

		// Test an array of existting post types.
		$this->assertTrue( $func( $pts ) );

		// Test an existing post type.
		$this->assertTrue( $func( end( $pts ) ) );

	}
}
