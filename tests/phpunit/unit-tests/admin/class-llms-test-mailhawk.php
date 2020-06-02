<?php
/**
 * Test MailHawk Connector
 *
 * @package LifterLMS/Tests
 *
 * @group mailhawk
 *
 * @since [version]
 */
class LLMS_Test_MailHawk extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-mailhawk.php';
		$this->mailhawk = new LLMS_MailHawk();

	}

	/**
	 * Tear down the testcase.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();
		wp_delete_file( WP_PLUGIN_DIR . '/mailhawk/uninstall.php' );
		delete_plugins( array( 'mailhawk/mailhawk.php' ) );

	}

	/**
	 * Test the add_settings() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_settings() {

		// No settings for anyone without the `install_plugins` cap.
		$this->assertEquals( array(), $this->mailhawk->add_settings( array() ) );

		// Admin can see the settings.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$res = $this->mailhawk->add_settings( array() );
		$this->assertEquals( array( 'mailhawk_title', 'mailhawk_connect' ), wp_list_pluck( $res, 'id' ) );

	}

	/**
	 * Test do_remote_install() error with no nonce submitted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_remote_install_no_nonce() {

		$res = LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'do_remote_install' );

		$this->assertArrayHasKey( 'message', $res );
		$this->assertEquals( 'llms_mailhawk_install_nonce_failure', $res['code'] );
		$this->assertEquals( 401, $res['status'] );

	}

	/**
	 * Test do_remote_install() error for no user.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_remote_install_no_user() {

		$this->mockPostRequest( array(
			'_llms_mailhawk_nonce' => wp_create_nonce( 'llms-mailhawk-install' ),
		) );

		$res = LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'do_remote_install' );

		$this->assertArrayHasKey( 'message', $res );
		$this->assertEquals( 'llms_mailhawk_install_unauthorized', $res['code'] );
		$this->assertEquals( 403, $res['status'] );

	}

	/**
	 * Test do_remote_install() error with plugins api.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_remote_install_plugins_api_error() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'_llms_mailhawk_nonce' => wp_create_nonce( 'llms-mailhawk-install' ),
		) );

		$handler = function( $ret, $action, $args ) {
			return new WP_Error( 'plugins_api_failed', 'Error' );
		};
		add_filter( 'plugins_api', $handler, 10, 3 );
		$res = LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'do_remote_install' );
		remove_filter( 'plugins_api', $handler, 10 );

		$this->assertArrayHasKey( 'message', $res );
		$this->assertEquals( 'plugins_api_failed', $res['code'] );
		$this->assertEquals( 400, $res['status'] );

	}

	/**
	 * Test do remote install success.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_remote_install_success() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->mockPostRequest( array(
			'_llms_mailhawk_nonce' => wp_create_nonce( 'llms-mailhawk-install' ),
		) );

		// Install.
		$res = LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'do_remote_install' );
		$this->assertEquals( array( 'partner_id', 'register_url', 'client_state', 'redirect_uri', ), array_keys( $res ) );
		$this->assertEquals( 1, $res['partner_id'] );

		// Already installed, activate.
		$res = LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'do_remote_install' );
		$this->assertEquals( array( 'partner_id', 'register_url', 'client_state', 'redirect_uri', ), array_keys( $res ) );
		$this->assertEquals( 1, $res['partner_id'] );

	}

	/**
	 * Test get_connect_setting()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_connect_setting() {

		// Not connected.
		$this->assertStringContains( 'id="llms-mailhawk-connect"', LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'get_connect_setting' ) );

		// Connected and not suspended.
		update_option( 'mailhawk_is_connected', 'yes' );
		set_transient( 'mailhawk_is_suspended', 'no', 10 );
		$this->assertStringContains( 'View settings', LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'get_connect_setting' ) );
		$this->assertStringContains( 'manage your account', LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'get_connect_setting' ) );

		// Connected and suspended.
		set_transient( 'mailhawk_is_suspended', 'yes', 10 );
		$this->assertStringContains( 'Email sending is currently disabled', LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'get_connect_setting' ) );

	}

	/**
	 * Test should_output_inline() method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_should_output_inline() {

		// No user.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'should_output_inline' ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Wrong screen.
		set_current_screen( 'admin' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'should_output_inline' ) );

		// Mock screen.
		set_current_screen( 'lifterlms_page_llms-settings' );

		// Right screen, wrong tab.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'should_output_inline' ) );

		// Right screen, right tab, is connected.
		update_option( 'mailhawk_is_connected', 'yes' );
		$this->mockGetRequest( array( 'tab' => 'engagements' ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'should_output_inline' ) );

		// Right screen, right tab, not connected.
		update_option( 'mailhawk_is_connected', 'no' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->mailhawk, 'should_output_inline' ) );

		set_current_screen( 'front' );
	}

}
