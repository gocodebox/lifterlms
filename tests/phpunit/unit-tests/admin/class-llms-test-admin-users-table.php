<?php
/**
 * Test LLMS_Admin_Users_Table class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group users_table
 *
 * @since [version]
 */
class LLMS_Test_Admin_Users_table extends LLMS_Unit_Test_Case {

	/**
	 * Setup before class
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function setupBeforeClass() {
		parent::setupBeforeClass();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class-llms-admin-users-table.php';
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
		set_current_screen( 'users.php' );
		$this->main = new LLMS_Admin_Users_Table();

	}

	/**
	 * Test add_actions() method
	 *
	 * @since [version]
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
	 * @since [version]
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
