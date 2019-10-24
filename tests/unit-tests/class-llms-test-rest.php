<?php
/**
 * Test inclusion and initialization of the rest api bundle
 *
 * @package LifterLMS/Tests
 *
 * @group rest
 * @group packages
 *
 * @since [version]
 * @version [version]
 */
class LLMS_Test_REST extends LLMS_Unit_Test_Case {

	/**
	 * Test rest package exists and is loaded.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_rest_package_exists() {
		$this->assertTrue( function_exists( 'LLMS_REST_API' ) );
		$this->assertTrue( defined( 'LLMS_REST_API_VERSION' ) );
		$this->assertNotNull( LLMS_REST_API_VERSION );
	}

	/**
	 * Ensure the REST API initializes.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_api_init() {

		$res = llms_rest_get_api_endpoint_data( '/llms/v1' );
		$this->assertEquals( 'llms/v1', $res['namespace'] );

	}

}
