<?php
/**
 * Test LLMS_Cache_Helper
 *
 * @package LifterLMS/Tests
 *
 * @group cache
 * @group cache_helper
 *
 * @since 4.0.0
 */
class LLMS_Test_Cache_Helper extends LLMS_Unit_Test_Case {

	/**
	 * Sets the WP Engine "live" status and defines the is_wpe() function, if not already defined.
	 *
	 * @see https://wpengine.com/support/determining-wp-engine-environment/
	 *
	 * @since [version]
	 *
	 * @param bool $is_live "Live" means a production, development or staging environment, but not a legacy staging site.
	 * @return void
	 */
	private function set_wpe_status( bool $is_live ) {

		putenv( 'IS_WPE=' . (int) $is_live );

		if ( function_exists( 'is_wpe' ) ) {
			return;
		}

		function is_wpe() {
			return getenv('IS_WPE');
		}
	}

	/**
	 * Tests the exclude_page_from_wpe_server_cache() method.
	 *
	 * @see LLMS_Cache_Helper::exclude_page_from_wpe_server_cache()
	 *
	 * @since [version]
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_exclude_page_from_wpe_server_cache() {

		// Setup.
		$helper  = new LLMS_Cache_Helper();
		$cookies = LLMS_Tests_Cookies::instance();
		$cookies->unset( 'wordpress_wpe_no_cache' );
		LLMS_Install::create_pages();
		$this->set_permalink_structure( '/%postname%/' );
		$dashboard_id    = llms_get_page_id( 'myaccount' );
		$dashboard_path  = parse_url( get_permalink( $dashboard_id ), PHP_URL_PATH );
		$expected_cookie = array(
			'value'    => '1',
			'expires'  => 0,
			'path'     => '',
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => true,
		);

		// is_wpe() function is not defined.
		LLMS_Unit_Test_Util::call_method( $helper, 'exclude_page_from_wpe_server_cache' );
		$this->assertNull( $cookies->get( 'wordpress_wpe_no_cache' ) );

		// is_wpe() returns 0.
		$this->set_wpe_status( false );
		LLMS_Unit_Test_Util::call_method( $helper, 'exclude_page_from_wpe_server_cache' );
		$this->assertNull( $cookies->get( 'wordpress_wpe_no_cache' ) );

		// is_wpe() returns 1, $post not given and global $post is not set.
		$this->set_wpe_status( true );
		unset( $GLOBALS['post'] );
		LLMS_Unit_Test_Util::call_method( $helper, 'exclude_page_from_wpe_server_cache' );
		$this->assertEquals( $expected_cookie, $cookies->get( 'wordpress_wpe_no_cache' ) );

		// is_wpe() returns 1, $post parameter is not given, global $post is for a LifterLMS dashboard page.
		$GLOBALS['post']         = get_post( $dashboard_id );
		$expected_cookie['path'] = $dashboard_path;
		LLMS_Unit_Test_Util::call_method( $helper, 'exclude_page_from_wpe_server_cache' );
		$this->assertEquals( $expected_cookie, $cookies->get( 'wordpress_wpe_no_cache' ) );

		// is_wpe() returns 1, $post parameter is for a LifterLMS dashboard page.
		LLMS_Unit_Test_Util::call_method( $helper, 'exclude_page_from_wpe_server_cache', array( $dashboard_id ) );
		$this->assertEquals( $expected_cookie, $cookies->get( 'wordpress_wpe_no_cache' ) );

		// URI contains a LifterLMS dashboard endpoint.
		$_SERVER['REQUEST_URI'] = '/dashboard/my-courses/';
		LLMS_Unit_Test_Util::call_method( $helper, 'exclude_page_from_wpe_server_cache' );
		$this->assertEquals( $expected_cookie, $cookies->get( 'wordpress_wpe_no_cache' ) );

		// Set permalink structure to plain.
		$this->set_permalink_structure( '' );
		$cookies->unset( 'wordpress_wpe_no_cache' );
		LLMS_Unit_Test_Util::call_method( $helper, 'exclude_page_from_wpe_server_cache' );
		$this->assertNull( $cookies->get( 'wordpress_wpe_no_cache' ) );
	}

	/**
	 * Test get_prefix() method.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_prefix() {

		$group = 'mock_prefix';

		// Cache miss.
		wp_cache_delete( 'llms_mock_cache_prefix', $group );

		$prefix = LLMS_Cache_Helper::get_prefix( $group );

		// Looks right.
		$this->assertEquals( 1, preg_match( '/llms_cache_0.[0-9]{8} [0-9]{10}_/', $prefix ) );

		// Cache hit.
		$this->assertEquals( $prefix, LLMS_Cache_Helper::get_prefix( $group ) );

	}

	/**
	 * Test invalidate_group() method.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_invalidate_group() {

		$group = 'mock_invalidate';

		$prefix = LLMS_Cache_Helper::get_prefix( $group );

		// Cache an item with the prefix.
		wp_cache_set( sprintf( 'fake_%s', $prefix ), 'mock_val', $group );

		$prefix = LLMS_Cache_Helper::invalidate_group( $group );

		// New prefix should not match the original prefix.
		$this->assertNotEquals( $prefix, LLMS_Cache_Helper::get_prefix( $group ) );

		// Cached item is gone.
		$this->assertFalse( wp_cache_get( sprintf( 'fake_%s', $prefix ), $group ) );

	}

	/**
	 * Test additional_nocache_headers() method.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function test_additional_nocache_headers() {

		$headers = array();

		$this->assertEquals(
			array(
				'Cache-Control' => 'no-cache, must-revalidate, max-age=0, no-store',
			),
			LLMS_Cache_Helper::additional_nocache_headers( $headers )
		);

		$headers = array(
			'Cache-Control' => '',
		);

		$this->assertEquals(
			array(
				'Cache-Control' => 'no-cache, must-revalidate, max-age=0, no-store',
			),
			LLMS_Cache_Helper::additional_nocache_headers( $headers )
		);

		$headers = array(
			'Cache-Control' => 'no-cache, something, no-store, something-else',
		);

		$this->assertEquals(
			array(
				'Cache-Control' => 'no-cache, must-revalidate, max-age=0, no-store, something, something-else',
			),
			LLMS_Cache_Helper::additional_nocache_headers( $headers )
		);

	}

}
