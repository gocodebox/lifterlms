<?php
/**
 * Test export api class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group export_api
 *
 * @since 4.8.0
 */
class LLMS_Test_Export_API extends LLMS_Unit_Test_Case {

	/**
	 * Setup before class.
	 *
	 * @since 4.8.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-export-api.php';
	}

	/**
	 * Test get() when a request error is encountered.
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_get_conn_error() {

		$handler = function( $res ) {
			return new WP_Error( 'mocked', 'Mocked error' );
		};

		add_filter( 'pre_http_request', $handler );

		$res = LLMS_Export_API::get( array( 1 ) );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'mocked', $res );

		remove_filter( 'pre_http_request', $handler );

	}

	/**
	 * Test get() when an API error is encountered (404)
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_get_api_error() {

		$res = LLMS_Export_API::get( array( 1 ) );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'not-found', $res );

	}

	/**
	 * Test get() for success response
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_get_success() {

		$res = LLMS_Export_API::get( array( 33579 ) ); // Free course lead magnet template.

		$this->assertEquals( 'LifterLMS/BulkCourseExporter', $res['_generator'] );
		$this->assertArrayHasKey( 33579, $res['courses'] );

	}

	/**
	 * Test list() when a request error is encountered.
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_list_conn_error() {

		$handler = function( $res ) {
			return new WP_Error( 'mocked', 'Mocked error' );
		};

		add_filter( 'pre_http_request', $handler );

		$res = LLMS_Export_API::list();
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'mocked', $res );

		remove_filter( 'pre_http_request', $handler );

	}

	/**
	 * Test list() for success response
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function test_list_success() {

		$list = LLMS_Export_API::list();

		$this->assertTrue( is_array( $list ) );

		foreach ( $list as $res ) {
			$this->assertEquals( array( 'id', 'description', 'image', 'title' ), array_keys( $res ) );
		}


	}


}
