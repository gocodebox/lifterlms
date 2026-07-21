<?php
/**
 * Test authentication methods.
 *
 * @package LifterLMS_REST/Tests
 *
 * @group auth
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Test_Authentication extends LLMS_REST_Unit_Test_Case_Base {

	public function set_up() {

		parent::set_up();

		$this->auth = new LLMS_REST_Authentication();

	}

	/**
	 * Tear down the test case.
	 *
	 * Resets superglobal state modified by these tests so it doesn't leak into other tests in the suite.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		unset( $_SERVER['HTTPS'], $_SERVER['REQUEST_URI'], $_SERVER['HTTP_X_LLMS_CONSUMER_KEY'], $_SERVER['HTTP_X_LLMS_CONSUMER_SECRET'] );

		parent::tear_down();

	}

	/**
	 * Test the authenticate method.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.12 Unset the global `current_user` where needed to catch possibile infinite loops on authentication error.
	 *
	 * @return void
	 */
	public function test_authenticate() {

		$expected_actions = did_action( 'llms_rest_basic_auth_success' );

		// An already authenticated user.
		$this->assertEquals( 123, $this->auth->authenticate( 123 ) );
		$this->assertEquals( $expected_actions, did_action( 'llms_rest_basic_auth_success' ) );

		// No SSL.
		$this->assertEquals( false, $this->auth->authenticate( false ) );
		$this->assertEquals( $expected_actions, did_action( 'llms_rest_basic_auth_success' ) );

		$_SERVER['HTTPS'] = 'ON';

		$this->assertEquals( false, $this->auth->authenticate( false ) );
		$this->assertEquals( $expected_actions, did_action( 'llms_rest_basic_auth_success' ) );

		// Not an LLMS Request.
		$_SERVER['REQUEST_URI'] = 'https://example.org/wp-json/wp/v1/mock';

		$this->assertEquals( false, $this->auth->authenticate( false ) );
		$this->assertEquals( $expected_actions, did_action( 'llms_rest_basic_auth_success' ) );

		$_SERVER['REQUEST_URI'] = 'https://example.org/wp-json/llms/v1/mock';

		// No credentials.
		$this->assertEquals( false, $this->auth->authenticate( false ) );
		$this->assertEquals( $expected_actions, did_action( 'llms_rest_basic_auth_success' ) );

		// Success.
		$key = $this->get_mock_api_key();
		$expected_actions++;
		$this->assertEquals( $key->get( 'user_id' ), $this->auth->authenticate( false ) );
		$this->assertEquals( $expected_actions, did_action( 'llms_rest_basic_auth_success' ) );

		// Correct key, incorrect secret.
		unset($GLOBALS['current_user']); // Make sure the current user is not set: e.g. to catch possibile infinite loops on authentication error.
		$_SERVER['HTTP_X_LLMS_CONSUMER_SECRET'] = 'fakesecret';
		$this->assertEquals( false, $this->auth->authenticate( false ) );
		$this->assertEquals( $expected_actions, did_action( 'llms_rest_basic_auth_success' ) );
		$this->assertTrue( is_wp_error( LLMS_Unit_Test_Util::call_method( $this->auth, 'get_error' ) ) );

		// Incorrect key.
		$_SERVER['HTTP_X_LLMS_CONSUMER_KEY'] = 'fakekey';
		$this->assertEquals( false, $this->auth->authenticate( false ) );
		$this->assertEquals( $expected_actions, did_action( 'llms_rest_basic_auth_success' ) );

	}

	/**
	 * Test key lookup.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return [type]
	 */
	public function test_find_key() {

		// No keys to find.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->auth, 'find_key', array( 'ck_fake' ) ) );

		$keys = array();
		$i = 0;
		while ( $i < 3 ) {

			$keys[] = LLMS_REST_API()->keys()->create( array(
				'description' => 'Test Key ' . $i,
				'user_id' => $this->factory->user->create(),
			) );

			// Fake key.
			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->auth, 'find_key', array( 'ck_fake' ) ) );

			// Find the key
			$found = LLMS_Unit_Test_Util::call_method( $this->auth, 'find_key', array( $keys[ $i ]->get( 'consumer_key_one_time' ) ) );
			$this->assertEquals( $keys[ $i ]->get( 'id' ), $found->get( 'id' ) );

			$i++;

		}

	}

	/**
	 * Test the get_credentials() method using headers.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_credentials_from_headers() {

		$expect = array(
			'key'    => 'mock_key',
			'secret' => 'mock_secret',
		);

		// No key or secret.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->auth, 'locate_credentials', array( 'HTTP_X_LLMS_CONSUMER_KEY', 'HTTP_X_LLMS_CONSUMER_SECRET' ) ) );

		// Key, no secret.
		$_SERVER['HTTP_X_LLMS_CONSUMER_KEY'] = $expect['key'];
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->auth, 'locate_credentials', array( 'HTTP_X_LLMS_CONSUMER_KEY', 'HTTP_X_LLMS_CONSUMER_SECRET' ) ) );

		// Secret, no key.
		unset( $_SERVER['HTTP_X_LLMS_CONSUMER_KEY'] );
		$_SERVER['HTTP_X_LLMS_CONSUMER_SECRET'] = $expect['secret'];
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->auth, 'locate_credentials', array( 'HTTP_X_LLMS_CONSUMER_KEY', 'HTTP_X_LLMS_CONSUMER_SECRET' ) ) );

		// Key & Secret
		$_SERVER['HTTP_X_LLMS_CONSUMER_KEY'] = $expect['key'];
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->auth, 'locate_credentials', array( 'HTTP_X_LLMS_CONSUMER_KEY', 'HTTP_X_LLMS_CONSUMER_SECRET' ) ) );

		unset( $_SERVER['HTTP_X_LLMS_CONSUMER_KEY'], $_SERVER['HTTP_X_LLMS_CONSUMER_SECRET'] );

	}

	/**
	 * Test the get_credentials() method using basic auth.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_credentials_from_auth() {

		$expect = array(
			'key'    => 'mock_key',
			'secret' => 'mock_secret',
		);

		// No key or secret.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->auth, 'locate_credentials', array( 'PHP_AUTH_USER', 'PHP_AUTH_PW' ) ) );

		// Key, no secret.
		$_SERVER['PHP_AUTH_USER'] = $expect['key'];
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->auth, 'locate_credentials', array( 'PHP_AUTH_USER', 'PHP_AUTH_PW' ) ) );

		// Secret, no key.
		unset( $_SERVER['PHP_AUTH_USER'] );
		$_SERVER['PHP_AUTH_PW'] = $expect['secret'];
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->auth, 'locate_credentials', array( 'PHP_AUTH_USER', 'PHP_AUTH_PW' ) ) );

		// Key & Secret
		$_SERVER['PHP_AUTH_USER'] = $expect['key'];
		$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->auth, 'locate_credentials', array( 'PHP_AUTH_USER', 'PHP_AUTH_PW' ) ) );

		unset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] );

	}

	/**
	 * Test the is_rest_request() method.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.8 Added query-string and namespace boundary bypass cases.
	 *
	 * @return void
	 */
	public function test_is_rest_request() {

		$tests = array(
			'' => false,
			'http://example.com/wp-json/wp/v1/mock' => false,
			'https://example.com/wp-json/mock/v1/mock' => false,

			// An `llms` marker in the query string or a later path segment must not authenticate a non-LifterLMS route.
			'https://example.com/wp-json/wp/v2/users?x=/wp-json/llms/courses' => false,
			'https://example.com/wp-json/wp/v2/users?redirect=/wp-json/llms-foo' => false,
			'https://example.com/wp-json/wp/v2/wp-json/llms/v1/mock' => false,
			'https://example.com/?rest_route=/wp/v2/users&x=/wp-json/llms/' => false,

			'https://example.org/wp-json/llms/v1/mock' => true,
			'https://example.com/wp-json/llms/v1/mock' => true,
			'https://example.com/wp-json/llms/v2/mock' => true,
			'http://example.com/wp-json/llms/v1/mock' => true,
			'http://example.com/wp-json/llms-external/v1/mock' => true,

			// Subdirectory install and plain-permalink routes still match.
			'https://example.com/blog/wp-json/llms/v1/mock' => true,
			'https://example.com/?rest_route=/llms/v1/courses' => true,
		);

		foreach ( $tests as $uri => $expect ) {

			$_SERVER['REQUEST_URI'] = $uri;
			$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $this->auth, 'is_rest_request' ) );

		}


	}

}
