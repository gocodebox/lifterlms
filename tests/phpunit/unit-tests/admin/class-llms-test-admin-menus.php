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
	 * @var LLMS_Admin_Menus
	 */
	private $main;

	/**
	 * Setup before class
	 *
	 * @since 4.7.0
	 * @since 5.3.3 Renamed from `setUpBeforeClass()` for compat with WP core changes.
	 * @since [version] Removed loading the LLMS_Admin_Reporting class file that is now handled by the autoloader.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.menus.php';
	}

	/**
	 * Setup the test case.
	 *
	 * @since 4.7.0
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_Admin_Menus();

	}

	/**
	 * Test load_admin_tools()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_load_admin_tools() {

		$actions = did_action( 'llms_load_admin_tools' );

		LLMS_Unit_Test_Util::call_method( $this->main, 'load_admin_tools' );

		// Action ran.
		$this->assertSame( ++$actions, did_action( 'llms_load_admin_tools' ) );
	}

	/**
	 * Test reporting_page_init() when there's permission issues.
	 *
	 * @since 4.7.0
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
	 * @since 4.7.0
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
	 * @since 4.7.0
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
