<?php
/**
 * Test SendWP Connector
 *
 * @package LifterLMS/Tests
 *
 * @group sendwp
 *
 * @since 3.36.1
 * @since 3.37.0 Add testing for nonce verifications.
 * @since 3.40.0 Added additional coverage.
 */
class LLMS_Test_SendWP extends LLMS_Unit_Test_Case {

	/**
	 * @var LLMS_SendWP
	 */
	protected $sendwp;

	/**
	 * Setup before class
	 *
	 * @since 3.40.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();

		include_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-email-provider.php';
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-sendwp.php';

	}

	/**
	 * Setup the test case.
	 *
	 * @since 3.36.1
	 * @since 3.40.0 Include class file via `set_up_before_class()`.
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->sendwp = new LLMS_SendWP();

	}

	/**
	 * Tear down the testcase.
	 *
	 * @since 3.36.1
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		delete_plugins( array( 'sendwp/sendwp.php' ) );

	}

	/**
	 * Test the add_settings() method.
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 */
	public function test_add_settings() {

		// No settings for anyone without the `install_plugins` cap.
		$this->assertEquals( array(), $this->sendwp->add_settings( array() ) );

		// Admin can see the settings.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$res = $this->sendwp->add_settings( array() );
		$this->assertEquals( array( 'sendwp_title', 'sendwp_connect' ), wp_list_pluck( $res, 'id' ) );

	}

	/**
	 * Test do_remote_install() error with no nonce submitted.
	 *
	 * @since 3.37.0
	 * @since [version] Changed {@see LLMS_SendWP::do_remote_install()} access from public to protected.
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_do_remote_install_no_nonce() {

		$res = LLMS_Unit_Test_Util::call_method( $this->sendwp, 'do_remote_install' );

		$this->assertArrayHasKey( 'message', $res );
		$this->assertEquals( 'llms_sendwp_install_nonce_failure', $res['code'] );
		$this->assertEquals( 401, $res['status'] );

	}

	/**
	 * Test do_remote_install() error for no user.
	 *
	 * @since 3.36.1
	 * @since 3.37.0 Add mock nonce to test.
	 * @since [version] Changed {@see LLMS_SendWP::do_remote_install()} access from public to protected.
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_do_remote_install_no_user() {

		$this->mockPostRequest( array(
			'_llms_sendwp_nonce' => wp_create_nonce( 'llms-sendwp-install' ),
		) );

		$res = LLMS_Unit_Test_Util::call_method( $this->sendwp, 'do_remote_install' );

		$this->assertArrayHasKey( 'message', $res );
		$this->assertEquals( 'llms_sendwp_install_unauthorized', $res['code'] );
		$this->assertEquals( 403, $res['status'] );

	}

	/**
	 * Test do_remote_install() error with plugins api.
	 *
	 * @since 3.36.1
	 * @since 3.37.0 Add mock nonce to test.
	 * @since [version] Changed {@see LLMS_SendWP::do_remote_install()} access from public to protected.
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_do_remote_install_plugins_api_error() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'_llms_sendwp_nonce' => wp_create_nonce( 'llms-sendwp-install' ),
		) );

		$handler = function( $ret, $action, $args ) {
			return new WP_Error( 'plugins_api_failed', 'Error' );
		};
		add_filter( 'plugins_api', $handler, 10, 3 );
		$res = LLMS_Unit_Test_Util::call_method( $this->sendwp, 'do_remote_install' );
		remove_filter( 'plugins_api', $handler, 10 );

		$this->assertArrayHasKey( 'message', $res );
		$this->assertEquals( 'plugins_api_failed', $res['code'] );
		$this->assertEquals( 400, $res['status'] );

	}

	/**
	 * Test do remote install success.
	 *
	 * @since 3.36.1
	 * @since 3.37.0 Add mock nonce to test.
	 * @since [version] Changed {@see LLMS_SendWP::do_remote_install()} access from public to protected.
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_do_remote_install_success() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'_llms_sendwp_nonce' => wp_create_nonce( 'llms-sendwp-install' ),
		) );

		// Install.
		$res = LLMS_Unit_Test_Util::call_method( $this->sendwp, 'do_remote_install' );
		$this->assertEquals( array( 'partner_id', 'register_url', 'client_name', 'client_secret', 'client_redirect', ), array_keys( $res ) );
		$this->assertEquals( 2007, $res['partner_id'] );

		// Already installed, activate.
		$res = LLMS_Unit_Test_Util::call_method( $this->sendwp, 'do_remote_install' );
		$this->assertEquals( array( 'partner_id', 'register_url', 'client_name', 'client_secret', 'client_redirect', ), array_keys( $res ) );
		$this->assertEquals( 2007, $res['partner_id'] );

	}

	/**
	 * Test get_connect_setting()
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_get_connect_setting() {

		// Not connected.
		$this->assertStringContains( 'id="llms-sendwp-connect"', LLMS_Unit_Test_Util::call_method( $this->sendwp, 'get_connect_setting' ) );

		// Connected and forwarding.
		update_option( 'sendwp_client_connected', '1' );
		$this->assertStringContains( 'Manage your account', LLMS_Unit_Test_Util::call_method( $this->sendwp, 'get_connect_setting' ) );

		// Connected and not forwarding.
		update_option( 'sendwp_forwarding_enabled', '0' );
		$this->assertStringContains( 'Email sending is currently disabled', LLMS_Unit_Test_Util::call_method( $this->sendwp, 'get_connect_setting' ) );

	}

	/**
	 * Test should_output_inline() method.
	 *
	 * @since 3.40.0
	 *
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_should_output_inline() {

		// No user.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->sendwp, 'should_output_inline' ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Wrong screen.
		set_current_screen( 'admin' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->sendwp, 'should_output_inline' ) );

		// Mock screen.
		set_current_screen( 'lifterlms_page_llms-settings' );

		// Right screen, wrong tab.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->sendwp, 'should_output_inline' ) );

		// Right screen, right tab, is connected.
		update_option( 'sendwp_client_connected', '1' );
		$this->mockGetRequest( array( 'tab' => 'engagements' ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->sendwp, 'should_output_inline' ) );

		// Right screen, right tab, not connected.
		update_option( 'sendwp_client_connected', '0' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->sendwp, 'should_output_inline' ) );

		set_current_screen( 'front' );
	}

}
