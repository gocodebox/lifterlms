<?php
/**
 * Tests for the LLMS_Admin_Tool_Clear_Sessions class
 *
 * @package LifterLMS/Tests/Admins/Tools
 *
 * @group admin
 * @group admin_tools
 * @group clear_sessions
 *
 * @since 4.0.0
 * @since 5.3.0 Use `LLMS_Admin_Tool_Test_Case` and remove redundant methods/tests.
 */
class LLMS_Test_Admin_Tool_Clear_Sessions extends LLMS_Admin_Tool_Test_Case {

	/**
	 * Name of the class being tested.
	 *
	 * @var sting
	 */
	const CLASS_NAME = 'LLMS_Admin_Tool_Clear_Sessions';

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
