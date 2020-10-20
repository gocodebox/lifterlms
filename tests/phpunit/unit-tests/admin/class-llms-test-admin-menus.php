<?php
/**
 * Test Admin Menus Class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_menus
 *
 * @since [version]
 */
class LLMS_Test_Admin_Menus extends LLMS_Unit_Test_Case {

	/**
	 * Setup before class
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function setupBeforeClass() {
		parent::setupBeforeClass();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/reporting/class.llms.admin.reporting.php';
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.menus.php';
	}

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->main = new LLMS_Admin_Menus();

	}

	/**
	 * Test reporting_page_init() when there's permission issues.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reporting_page_init_permissions_error() {

		$this->mockGetRequest( array( 'student_id' => $this->factory->student->create() ) );

		$this->setExpectedException( 'WPDieException', 'You do not have permission to access this content.' );

		$this->main->reporting_page_init();

	}

	/**
	 * Test reporting_page_init() when there's no permission issues
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reporting_page_init_permission_success() {

		set_current_screen( 'admin' );
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->mockGetRequest( array( 'student_id' => $this->factory->student->create() ) );

		$this->assertOutputContains( '<div class="wrap lifterlms llms-reporting tab--students">', array( $this->main, 'reporting_page_init' ) );

		set_current_screen( 'front' );
	}

	/**
	 * Test reporting_page_init() when there's no permission issues
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_reporting_page_init_no_permissions() {

		set_current_screen( 'admin' );
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->assertOutputContains( '<div class="wrap lifterlms llms-reporting tab--students">', array( $this->main, 'reporting_page_init' ) );

		set_current_screen( 'front' );
	}

}
