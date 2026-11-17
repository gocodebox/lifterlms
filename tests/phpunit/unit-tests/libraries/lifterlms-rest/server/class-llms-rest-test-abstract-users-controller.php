<?php
/**
 * Test concrete methods from the LLMS_REST_Users_Controller abstract class.
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group rest_users_abstract
 * @group rest_users
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Test_Abstract_Users_Controller extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Setup the test case.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		// Stub of the abstract.
		$this->stub = new class extends LLMS_REST_Users_Controller {

			// stub required abstract method.
			protected function get_object( $id ) { return $id; }

		};

		$this->request = new WP_REST_Request( 'POST', 'mock' );

	}

	/**
	 * Filter for testing banned usernames added via the `illegal_user_logins` filter.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string[] $illegal List of illegal usernames.
	 * @return string[]
	 */
	public function get_illegal_user_logins( $illegal ) {
		$illegal[] = 'illegal';
		return $illegal;
	}


	/**
	 * Test the sanitize_password method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_sanitize_password() {

		// Good.
		$this->assertEquals( 'password', $this->stub->sanitize_username( 'password', $this->request, 'username' ) );

		// Illegal chars.
		$ret = $this->stub->sanitize_username( 'mock\\password', $this->request, 'username' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_bad_request', $ret );

	}


	/**
	 * Test the sanitize_username method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_sanitize_username() {

		// Good.
		$this->assertEquals( 'okay', $this->stub->sanitize_username( 'okay', $this->request, 'username' ) );

		// Illegal chars.
		$ret = $this->stub->sanitize_username( '¯\_(ツ)_/¯', $this->request, 'username' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_bad_request', $ret );

		// Banned username.
		add_filter( 'illegal_user_logins', array( $this, 'get_illegal_user_logins' ) );

		$ret = $this->stub->sanitize_username( 'illegal', $this->request, 'username' );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_bad_request', $ret );

		$this->assertEquals( 'something-else', $this->stub->sanitize_username( 'something-else', $this->request, 'username' ) );

		remove_filter( 'illegal_user_logins', array( $this, 'get_illegal_user_logins' ) );

	}

}
