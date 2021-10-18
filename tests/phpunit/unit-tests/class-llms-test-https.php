<?php
/**
 * Test LLMS_HTTPS class
 *
 * @package LifterLMS/Tests
 *
 * @group https
 *
 * @since 3.35.1
 * @version 3.35.1
 */
class LLMS_Test_HTTPS extends LLMS_UnitTestCase {

	/**
	 * Setup testcase.
	 *
	 * @since 3.35.1
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->https = new LLMS_HTTPS();
		$this->original_server = $_SERVER;

	}

	/**
	 * Setup testcase.
	 *
	 * @since 3.35.1
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		$_SERVER = $this->original_server;

	}

	/**
	 * Test force url getter
	 *
	 * @since 3.35.1
	 *
	 * @return void
	 */
	public function test_get_force_redirect_url() {

		// No REQUEST_URI or HTTP_X_FORWARDED_HOST.
		$this->assertEquals( 'https://example.org', LLMS_Unit_Test_Util::call_method( $this->https, 'get_force_redirect_url', array( true ) ) );
		$this->assertEquals( 'http://example.org', LLMS_Unit_Test_Util::call_method( $this->https, 'get_force_redirect_url', array( false ) ) );

		// No REQUEST_URI.
		$_SERVER['HTTP_X_FORWARDED_HOST'] = 'example.org';
		$this->assertEquals( 'https://example.org', LLMS_Unit_Test_Util::call_method( $this->https, 'get_force_redirect_url', array( true ) ) );
		$this->assertEquals( 'http://example.org', LLMS_Unit_Test_Util::call_method( $this->https, 'get_force_redirect_url', array( false ) ) );

		$_SERVER['REQUEST_URI'] = 'http://example.org';
		$this->assertEquals( 'https://example.org', LLMS_Unit_Test_Util::call_method( $this->https, 'get_force_redirect_url', array( true ) ) );

		$_SERVER['REQUEST_URI'] = 'https://example.org';
		$this->assertEquals( 'https://example.org', LLMS_Unit_Test_Util::call_method( $this->https, 'get_force_redirect_url', array( true ) ) );

		$_SERVER['REQUEST_URI'] = 'https://example.org';
		$this->assertEquals( 'http://example.org', LLMS_Unit_Test_Util::call_method( $this->https, 'get_force_redirect_url', array( false ) ) );

		$_SERVER['REQUEST_URI'] = 'http://example.org';
		$this->assertEquals( 'http://example.org', LLMS_Unit_Test_Util::call_method( $this->https, 'get_force_redirect_url', array( false ) ) );

	}

	/**
	 * Test force redirect
	 *
	 * @since 3.35.1
	 *
	 * @return void
	 */
	public function test_force_https_redirect() {

		LLMS_Install::create_pages();

		$_SERVER['HTTPS'] = 1;
		$this->assertNull( $this->https->force_https_redirect() );

		unset( $_SERVER['HTTPS'] );
		$url = llms_get_page_url( 'checkout' );
		$this->go_to( $url );

		$this->expectException( LLMS_Unit_Test_Exception_Exit::class );
		$this->expectExceptionMessage( sprintf( '%s [301] YES', preg_replace( '|^http://|', 'https://', $url ) ) );

		$this->https->force_https_redirect();

	}

	/**
	 * Test unforce redirect
	 *
	 * @since 3.35.1
	 *
	 * @return void
	 */
	public function test_unforce_https_redirect() {

		LLMS_Install::create_pages();
		$this->go_to( llms_get_page_url( 'checkout' ) );

		$home = get_option( 'home' );
		update_option( 'home', preg_replace( '|^https://|', 'http://', $home ) );
		$this->assertNull( $this->https->unforce_https_redirect() );
		update_option( 'home', $home );

		$_SERVER['HTTPS'] = 1;
		$this->assertNull( $this->https->unforce_https_redirect() );

		$this->go_to( '/' );
		unset( $_SERVER['HTTPS'] );
		$this->assertNull( $this->https->unforce_https_redirect() );

		$_SERVER['HTTPS'] = 1;
		$this->expectException( LLMS_Unit_Test_Exception_Exit::class );
		$this->expectExceptionMessage(  'http://example.org/ [301] YES' );

		$this->assertNull( $this->https->unforce_https_redirect() );

	}

}

