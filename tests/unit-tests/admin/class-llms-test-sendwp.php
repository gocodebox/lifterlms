<?php
/**
 * Test SendWP Connector
 *
 * @package LifterLMS/Tests
 *
 * @group sendwp
 *
 * @since 3.36.1
 * @version 3.36.1
 */
class LLMS_Test_SendWP extends LLMS_Unit_Test_Case {

	/**
	 * Setup the test case.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-sendwp.php';
		$this->sendwp = new LLMS_SendWP();

	}

	public function tearDown() {

		parent::tearDown();
		delete_plugins( array( 'sendwp/sendwp.php' ) );

	}

	/**
	 * Test do_remote_install() error for no user.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function test_do_remote_install_no_user() {

		$res = $this->sendwp->do_remote_install();

		$this->assertArrayHasKey( 'message', $res );
		$this->assertEquals( 'llms_sendwp_install_unauthorized', $res['code'] );
		$this->assertEquals( 403, $res['status'] );

	}

	/**
	 * Test do_remote_install() error with plugins api.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function test_do_remote_install_plugins_api_error() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$handler = function( $ret, $action, $args ) {
			return new WP_Error( 'plugins_api_failed', 'Error' );
		};
		add_filter( 'plugins_api', $handler, 10, 3 );
		$res = $this->sendwp->do_remote_install();
		remove_filter( 'plugins_api', $handler, 10 );

		$this->assertArrayHasKey( 'message', $res );
		$this->assertEquals( 'plugins_api_failed', $res['code'] );
		$this->assertEquals( 400, $res['status'] );

	}

	/**
	 * Test do remote install success.
	 *
	 * @since 3.36.1
	 *
	 * @return void
	 */
	public function test_do_remote_install_success() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		// Install.
		$res = $this->sendwp->do_remote_install();
		$this->assertEquals( array( 'partner_id', 'register_url', 'client_name', 'client_secret', 'client_redirect', ), array_keys( $res ) );
		$this->assertEquals( 2007, $res['partner_id'] );

		// Already installed, activate.
		$res = $this->sendwp->do_remote_install();
		$this->assertEquals( array( 'partner_id', 'register_url', 'client_name', 'client_secret', 'client_redirect', ), array_keys( $res ) );
		$this->assertEquals( 2007, $res['partner_id'] );

	}


}
