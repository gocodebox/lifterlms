<?php
/**
 * Test Admin Menus Class
 *
 * @package LifterLMS/Tests/Admin
 *
 * @group admin
 * @group admin_menus
 *
 * @since 6.0.0
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
	 * @since 6.0.0 Removed loading the LLMS_Admin_Reporting class file that is now handled by the autoloader.
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
	 * Retrieves a mock admin menu array.
	 *
	 * @since [version]
	 *
	 * @return array[]
	 */
	private function get_mock_admin_menu() {

		$menu = array();
		$menu[2]  = array( __( 'Dashboard' ), 'read', 'index.php', '', 'menu-top menu-top-first menu-icon-dashboard', 'menu-dashboard', 'dashicons-dashboard' );
		$menu[4]  = array( '', 'read', 'separator1', '', 'wp-menu-separator' );
		$menu[5] = array( 'Posts', 'edit_posts', 'edit.php', '', 'menu-top menu-icon-post open-if-no-js', 'menu-posts', 'dashicons-admin-post' );
		$menu[7] = array( '', 'read', 'separator2', '', 'wp-menu-separator' );

		return $menu;

	}

	/**
	 * Tests {@see LLMS_Admin_Menus::instructor:menu_hack}.
	 *
	 * @since [version]
	 */
	public function test_instructor_menu_hack() {

		global $menu;

		$tests = array(
			'administrator'         => array( 2, 4, 5, 7 ),
			'lms_manager'           => array( 2, 4, 5, 7 ),
			'author'                => array( 2, 4, 5, 7 ),
			'instructor'            => array( 2, 4, 7 ),
			'instructors_assistant' => array( 2, 4, 7 ),
		);

		foreach ( $tests as $role => $expected ) {
			$menu = $this->get_mock_admin_menu();
			wp_set_current_user( $this->factory->user->create( compact( 'role' ) ) );
			$this->main->instructor_menu_hack();
			$this->assertEquals( $expected, array_keys( $menu ), $role );
		}

	}

	/**
	 * Tests {@see LLMS_Admin_Menus::instructor:menu_hack} when an instructor is
	 * explicitly allowed to edit posts.
	 *
	 * @since [version]
	 */
	public function test_instructor_menu_hack_removed() {

		global $menu;
		$menu = $this->get_mock_admin_menu();

		// Allow instructor to edit.
		$handler = function( $roles ) {
			return array( 'instructors_assistant' );
		};
		add_filter( 'llms_instructor_menu_hack_roles', $handler );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'instructor' ) ) );

		$this->main->instructor_menu_hack();
		$this->assertEquals( array( 2, 4, 5, 7 ), array_keys( $menu ) );

		remove_filter( 'llms_instructor_menu_hack_roles', $handler );
		unset( $menu );

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

	/**
	 * Test status_page_includes()
	 *
	 * @since 4.12.0
	 * @since 6.0.0 Updated for autoloader changes. Stopped autoloading classes when checking if they exist.
	 *              Stopped checking for the LLMS_Admin_Page_Status class because status_page_includes() no longer loads it.
	 *
	 * @return void
	 */
	public function test_status_page_includes() {

		$classes = array(
			'LLMS_Admin_Tool_Batch_Eraser',
			'LLMS_Admin_Tool_Clear_Sessions',
			'LLMS_Admin_Tool_Recurring_Payment_Rescheduler',
		);

		$actions = did_action( 'llms_load_admin_tools' );

		foreach ( $classes as $class ) {
			$this->assertFalse( class_exists( $class, false ), $class );
		}

		LLMS_Unit_Test_Util::call_method( $this->main, 'status_page_includes' );

		// Classes included.
		foreach ( $classes as $class ) {
			$this->assertTrue( class_exists( $class, false ), $class );
		}

		// Action ran.
		$this->assertSame( ++$actions, did_action( 'llms_load_admin_tools' ) );
	}
}
