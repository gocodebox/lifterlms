<?php
/**
 * Test Admin Status page
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group status
 *
 * @since [version]
 */
class LLMS_Test_Admin_Page_Status extends LLMS_Unit_Test_Case {

	public static function setUpBeforeClass() {

		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.page.status.php';

	}

	/**
	 * Setup the test case
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->main = 'LLMS_Admin_Page_Status';

	}

	/**
	 * Test do_tool() when no nonce is submitted.
	 *
	 * @since [version]
	 *
	 * @expectedException WPDieException
	 *
	 * @return void
	 */
	public function test_do_tool_no_nonce() {

		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );


	}

	/**
	 * Test do_tool() when invalid nonce is submitted.
	 *
	 * @since [version]
	 *
	 * @expectedException WPDieException
	 *
	 * @return void
	 */
	public function test_do_tool_invalid_nonce() {

		$this->mockPostRequest( array(
			'_wpnonce' => 'fake',
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

	}

	/**
	 * Test do_tool() when no user permissions
	 *
	 * @since [version]
	 *
	 * @expectedException WPDieException
	 *
	 * @return void
	 */
	public function test_do_tool_no_user_caps() {

		$this->mockPostRequest( array(
			'_wpnonce' => wp_create_nonce( 'llms_tool' ),
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

	}

	/**
	 * Test do_tool() valid.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_tool_valid_user() {

		$actions = did_action( 'llms_status_tool' );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'_wpnonce'  => wp_create_nonce( 'llms_tool' ),
			'llms_tool' => 'custom',
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

		$this->assertEquals( ++$actions, did_action( 'llms_status_tool' ) );

	}

	/**
	 * Test the automatic payments reset tool.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_tool_reset_auto_payments() {

		// Get the original values of options to be cleared.
		$orig_url    = get_option( 'llms_site_url' );
		$orig_ignore = get_option( 'llms_site_url_ignore' );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'_wpnonce'  => wp_create_nonce( 'llms_tool' ),
			'llms_tool' => 'automatic-payments',
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

		$this->assertEquals( '', get_option( 'llms_site_url' ) );
		$this->assertEquals( 'no', get_option( 'llms_site_url_ignore' ) );

		// Reset to the orig values.
		update_option( 'llms_site_url', $orig_url );
		update_option( 'llms_site_url_ignore', $orig_ignore );

	}

	/**
	 * Test the overall progress cache clear tool.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_tool_clear_cache() {

		// Add mock data.
		foreach ( $this->factory->student->create_many( 3 ) as $uid ) {
			update_user_meta( $uid, 'llms_overall_progress', 'mock' );
			update_user_meta( $uid, 'llms_overall_grade', 'mock' );
		}

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'_wpnonce'  => wp_create_nonce( 'llms_tool' ),
			'llms_tool' => 'clear-cache',
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

		global $wpdb;
		$res = $wpdb->get_results( "SELECT * FROM {$wpdb->usermeta} WHERE meta_key = 'llms_overall_progress' OR meta_key = 'llms_overall_grade';" );

		$this->assertEquals( array(), $res );

	}

	/**
	 * Test the session data clear tool.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_tool_clear_sessions() {

		// Create mock data.
		WP_Session_Utils::create_dummy_session();

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'_wpnonce'  => wp_create_nonce( 'llms_tool' ),
			'llms_tool' => 'clear-sessions',
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

		global $wpdb;
		$res = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '_wp_session_%';" );

		$this->assertEquals( array(), $res );

	}

	/**
	 * Test the tracking reset tool.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_tool_reset_tracking() {

		update_option( 'llms_allow_tracking', 'yes' );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'_wpnonce'  => wp_create_nonce( 'llms_tool' ),
			'llms_tool' => 'reset-tracking',
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

		$this->assertEquals( 'no', get_option( 'llms_allow_tracking' ) );

	}

	/**
	 * Test the setup wizard redirect tool.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_do_tool_setup_wizard() {

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( sprintf( '%s?page=llms-setup [302] YES', admin_url() ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'_wpnonce'  => wp_create_nonce( 'llms_tool' ),
			'llms_tool' => 'setup-wizard',
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

	}

}
