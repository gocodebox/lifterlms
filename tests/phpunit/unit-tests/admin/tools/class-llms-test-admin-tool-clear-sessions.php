<?php
/**
 * Tests for the LLMS_Admin_Tool_Clear_Sessions class
 *
 * @package LifterLMS/Tests/Admins/Tools
 *
 * @group admin
 * @group admin_tools
 *
 * @since 4.0.0
 */
class LLMS_Test_Admin_Tool_Clear_Sessions extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Include abstract class.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();

		require_once LLMS_PLUGIN_DIR . 'includes/abstracts/llms-abstract-admin-tool.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/tools/class-llms-admin-tool-clear-sessions.php';

	}

	/**
	 * Setup the test case
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Tool_Clear_Sessions();

	}

	/**
	 * Test get_description()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_description() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_description' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_label()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_label() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_label' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test get_text()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_get_text() {

		$res = LLMS_Unit_Test_Util::call_method( $this->main, 'get_text' );
		$this->assertTrue( ! empty( $res ) );
		$this->assertTrue( is_string( $res ) );

	}

	/**
	 * Test handle()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_handle() {

		$this->create_mock_session_data();

		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'handle' ) );

		global $wpdb;
		$this->assertEquals( 0, $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}lifterlms_sessions" ) );

	}

}
