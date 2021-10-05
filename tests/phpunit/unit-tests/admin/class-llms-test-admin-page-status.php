<?php
/**
 * Test Admin Status page
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group status
 *
 * @since 3.37.14
 * @since 4.0.0 Removed clear sessions tests in favor of tests in the `LLMS_Test_Admin_Tool_Clear_Sessions` test class.
 */
class LLMS_Test_Admin_Page_Status extends LLMS_Unit_Test_Case {

	/**
	 * Set up before class
	 *
	 * @since Unknown
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.page.status.php';

	}

	/**
	 * Setup the test case
	 *
	 * @since 3.37.14
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = 'LLMS_Admin_Page_Status';

	}

	/**
	 * Test do_tool() when no nonce is submitted.
	 *
	 * @since 3.37.14
	 * @since 5.3.3 Use `expectException()` in favor of deprecated `@expectedException` annotation.
	 *
	 * @return void
	 */
	public function test_do_tool_no_nonce() {

		$this->expectException( 'WPDieException' );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

	}

	/**
	 * Test do_tool() when invalid nonce is submitted.
	 *
	 * @since 3.37.14
	 * @since 5.3.3 Use `expectException()` in favor of deprecated `@expectedException` annotation.
	 *
	 * @return void
	 */
	public function test_do_tool_invalid_nonce() {

		$this->expectException( 'WPDieException' );

		$this->mockPostRequest( array(
			'_wpnonce' => 'fake',
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

	}

	/**
	 * Test do_tool() when no user permissions
	 *
	 * @since 3.37.14
	 * @since 5.3.3 Use `expectException()` in favor of deprecated `@expectedException` annotation.
	 *
	 * @return void
	 */
	public function test_do_tool_no_user_caps() {

		$this->expectException( 'WPDieException' );

		$this->mockPostRequest( array(
			'_wpnonce' => wp_create_nonce( 'llms_tool' ),
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

	}

	/**
	 * Test do_tool() valid.
	 *
	 * @since 3.37.14
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
	 * Test the overall progress cache clear tool.
	 *
	 * @since 3.37.14
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
	 * Test the tracking reset tool.
	 *
	 * @since 3.37.14
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
	 * @since 3.37.14
	 * @since 4.13.0 Fix expected redirect URL.
	 *
	 * @return void
	 */
	public function test_do_tool_setup_wizard() {

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( sprintf( '%s [302] YES', admin_url( 'admin.php?page=llms-setup') ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->mockPostRequest( array(
			'_wpnonce'  => wp_create_nonce( 'llms_tool' ),
			'llms_tool' => 'setup-wizard',
		) );
		LLMS_Unit_Test_Util::call_method( $this->main, 'do_tool' );

	}

}
