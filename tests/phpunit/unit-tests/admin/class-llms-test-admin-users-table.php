<?php
/**
 * Test LLMS_Admin_Users_Table class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group users_table
 *
 * @since 4.0.0
 */
class LLMS_Test_Admin_Users_table extends LLMS_Unit_Test_Case {

	/**
	 * Setup before class
	 *
	 * @since 4.0.0
	 * @since 4.7.0 Add `LLMS_Admin_Reporting` class.
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/class.llms.admin.reporting.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-users-table.php';
	}

	/**
	 * Setup the test case
	 *
	 * @since 4.0.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		set_current_screen( 'users.php' );
		$this->main = new LLMS_Admin_Users_Table();

	}


	/**
	 * Teardown the test case
	 *
	 * @since 4.0.0
	 * @since 5.3.3 Renamed from `tearDown()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		/**
		 * Reset current screen
		 *
		 * I can't find anything officially documenting the proper way to do this but this line seems to indicate
		 * you can reset it by using `front` as the current screen:
		 *
		 * https://core.trac.wordpress.org/browser/tags/5.4/src/wp-admin/includes/class-wp-screen.php#L277
		 *
		 * Without this, tests following theses tests these tests which use function that have `is_admin()` calls in them
		 * may fail because `is_admin()` would otherwise return `true` on PHP 7.3 and lower and WP 5.2 or lower.
		 */
		set_current_screen( 'front' );

	}

	/**
	 * Test add_actions() method
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_add_actions() {

		$user = $this->factory->user->create_and_get();
		$res  = $this->main->add_actions( array(), $user );

		$this->assertArrayHasKey( 'llms-reporting', $res );

		$this->assertStringContains( 'page=llms-reporting', $res['llms-reporting'] );
		$this->assertStringContains( 'tab=students', $res['llms-reporting'] );
		$this->assertStringContains( 'student_id=' . $user->ID, $res['llms-reporting'] );

	}

	/**
	 * Test add_cols()
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_add_cols() {

		$this->assertEquals( array(
			'llms-last-login'  => 'Last Login',
			'llms-enrollments' => 'Enrollments',
		), $this->main->add_cols( array() ) );
	}

}
