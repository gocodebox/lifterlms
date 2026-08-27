<?php
/**
 * Tests for LLMS_Admin_Plugins class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_plugins
 *
 * @since [version]
 */
class LLMS_Test_Admin_Plugins extends LLMS_Unit_Test_Case {

	/**
	 * Setup test class.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {

		parent::set_up_before_class();
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-plugins.php';

	}

	/**
	 * Setup test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Plugins();

	}

	/**
	 * Test plugin_action_links() prepends the setup wizard link before Dashboard.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_plugin_action_links() {

		$links  = array(
			'deactivate' => '<a href="#">Deactivate</a>',
		);
		$result = $this->main->plugin_action_links( $links );
		$keys   = array_keys( $result );

		$this->assertSame( array( 'setup-wizard', 'dashboard', 'settings', 'deactivate' ), $keys );
		$this->assertStringContains( 'page=llms-setup', $result['setup-wizard'] );
		$this->assertStringContains( 'Launch Setup Wizard', $result['setup-wizard'] );

	}

}
