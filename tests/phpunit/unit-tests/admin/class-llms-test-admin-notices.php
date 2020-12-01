<?php
/**
 * Test Admin Notices Class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_notices
 *
 * @since [version]
 */
class LLMS_Test_Admin_Notices extends LLMS_Unit_Test_Case {

	/**
	 * Setup before class
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function setupBeforeClass() {
		parent::setupBeforeClass();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';
	}

	/**
	 * Test init() properly initializes the `$notices` class variable
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_init_notices_var() {

		$expect = array( 'fake' );
		update_option( 'llms_admin_notices', $expect );

		LLMS_Admin_Notices::init();

		$this->assertEquals( $expect, LLMS_Unit_Test_Util::get_private_property_value( 'LLMS_Admin_Notices', 'notices' ) );

	}

	/**
	 * Test init() properly adds action hooks
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_init_add_actions() {

		remove_action( 'wp_loaded', array( 'LLMS_Admin_Notices', 'hide_notices' ) );
		remove_action( 'current_screen', array( 'LLMS_Admin_Notices', 'add_output_actions' ) );
		remove_action( 'shutdown', array( 'LLMS_Admin_Notices', 'save_notices' ) );

		LLMS_Admin_Notices::init();

		$this->assertEquals( 10, has_action( 'wp_loaded', array( 'LLMS_Admin_Notices', 'hide_notices' ) ) );
		$this->assertEquals( 10, has_action( 'current_screen', array( 'LLMS_Admin_Notices', 'add_output_actions' ) ) );
		$this->assertEquals( 10, has_action( 'shutdown', array( 'LLMS_Admin_Notices', 'save_notices' ) ) );

	}

}
