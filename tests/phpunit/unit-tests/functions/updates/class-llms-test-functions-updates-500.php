<?php
/**
* Test updates functions when updating to 5.0.0
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_500
 *
 * @since 5.0.0
 * @since 5.2.0 Removed tearDown override, we don't need to remove any transient related to this update as we don't create it.
 */
class LLMS_Test_Functions_Updates_500 extends LLMS_UnitTestCase {

	/**
	 * Setup before class
	 *
	 * Include update functions file.
	 *
	 * @since 5.0.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-500.php';
	}

	/**
	 * Test llms_update_500_update_db_version()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		// Remove existing db version.
		delete_option( 'lifterlms_db_version' );

		llms_update_500_update_db_version();

		$this->assertEquals( '5.0.0', get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );

	}

	/**
	 * Test llms_update_500_add_admin_notice()
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function test_update_500_add_admin_notice() {

		$notice = 'v500-welcome-msg';

		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

		$this->assertFalse( LLMS_Admin_Notices::has_notice( $notice ) );

		llms_update_500_add_admin_notice();

		$this->assertTrue( true, LLMS_Admin_Notices::has_notice( $notice ) );

		// Cleanup.
		LLMS_Admin_Notices::delete_notice( $notice );

	}
}
