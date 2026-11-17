<?php
/**
 * LifterLMS Helper Unit Test Case Bootstrap
 *
 * @package LifterLMS_Helper/Tests
 *
 * @since 3.2.0
 */
class LLMS_Helper_Unit_Test_Case extends LLMS_Unit_Test_Case {

	/**
	 * Set up the test.
	 *
	 * @since 3.4.2
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->skip_api_integration_test();

	}

	/**
	 * Teardown the test case
	 *
	 * @since 3.2.1
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		delete_option( 'llms_helper_options' );

	}

	/**
	 * Retrieve license keys to use for testing from environment vars.
	 *
	 * @since 3.2.0
	 * @since 3.2.1 Only run api integration tests when explicitly specified through environment vars.
	 * @since 3.4.2 Removed test skip logic in favor of using `@apiIntegration` annotations to skip tests.
	 *
	 * @return void
	 */
	public function get_test_keys() {

		$keys = array();

		foreach ( array( 'INFINITY', 'UNIVERSE', 'STRIPE', 'INACTIVE' ) as $val ) {

			$key = getenv( "LLMS_HELPER_TEST_KEY_{$val}" );

			if ( $key ) {
				$keys[ $val ] = $key;
			}

		}

		// No keys found, skip the test.
		if ( ! $keys ) {
			$this->markTestSkipped( 'No license keys available.' );
		}

		return $keys;

	}

	/**
	 * Retrieve a single test key by its test ID
	 *
	 * @since 3.2.1
	 *
	 * @param string $key Test key ID. See `get_test_keys()` for available key ids.
	 * @return strinng|boolaen The license key or `false`.
	 */
	public function get_test_key( $key ) {
		$keys = $this->get_test_keys();
		return isset( $keys[ $key ] ) ? $keys[ $key ] : false;
	}

	/**
	 * Activate a test key using the test key id
	 *
	 * @since 3.2.1
	 *
	 * @param string $key Test key ID. See `get_test_keys()` for available key ids.
	 * @return array
	 */
	public function activate_key( $key ) {
		$key = $this->get_test_key( $key );
		$api = LLMS_Helper_Keys::activate_keys( $key );
		LLMS_Helper_Keys::add_license_key( $api['data']['activations'][0] );
		return llms_helper_options()->get_license_keys()[ $key ];
	}

}
