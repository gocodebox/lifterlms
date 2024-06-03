<?php
/**
 * Test view manager
 *
 * @package LifterLMS/Tests
 *
 * @group view_manager
 *
 * @since 4.5.1
 */
class LLMS_Test_View_Manager extends LLMS_UnitTestCase {

	/**
	 * Setup test case
	 *
	 * @since 4.5.1
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = new LLMS_View_Manager();

	}

	/**
	 * Initiate (and retrieve) an instance of WP_Admin_Bar
	 *
	 * @since 4.16.0
	 *
	 * @return WP_Admin_Bar
	 */
	private function get_admin_bar() {

		add_filter( 'show_admin_bar', '__return_true' );
		_wp_admin_bar_init();

		global $wp_admin_bar;

		remove_filter( 'show_admin_bar', '__return_true' );

		return $wp_admin_bar;

	}

	/**
	 * Mock `$_GET` data to control the return of `get_view()`.
	 *
	 * @since 4.16.0
	 *
	 * @param string $role Requested view role.
	 * @return void
	 */
	public function mock_view_data( $role ) {

		$this->mockGetRequest( array(
			'view_nonce'   => wp_create_nonce( 'llms-view-as' ),
			'llms-view-as' => $role,
		) );

	}

	/**
	 * Test constructor
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test__construct() {

		// Remove existing action.
		remove_action( 'init', array( $this->main, 'add_actions' ) );
		$this->assertFalse( has_action( 'init', array( $this->main, 'add_actions' ) ) );

		// Reinit.
		$this->main = new LLMS_View_Manager();
		$this->assertEquals( 10, has_action( 'init', array( $this->main, 'add_actions' ) ) );

	}

	/**
	 * Test constructor when a pending order is being created.
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test__construct_pending_order() {

		// Remove existing action.
		remove_action( 'init', array( $this->main, 'add_actions' ) );
		$this->assertFalse( has_action( 'init', array( $this->main, 'add_actions' ) ) );

		// Reinit.
		$this->mockPostRequest( array(
			'action' => 'create_pending_order',
		) );
		$this->main = new LLMS_View_Manager();
		$this->assertFalse( has_action( 'init', array( $this->main, 'add_actions' ) ) );
	}

	/**
	 * Test add_menu_items() when the display manager shouldn't be displayed.
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_add_menu_items_no_display() {

		$bar = $this->get_admin_bar();

		$this->main->add_menu_items( $bar );

		$this->assertNull( $bar->get_nodes() );

	}

	/**
	 * Test add_menu_items()
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_add_menu_items() {

		$bar = $this->get_admin_bar();

		add_filter( 'llms_view_manager_should_display', '__return_true' );

		$this->main->add_menu_items( $bar );

		$this->assertEquals( array( 'llms-view-as-menu', 'llms-view-as--visitor', 'llms-view-as--student' ), array_keys( $bar->get_nodes() ) );

		remove_filter( 'llms_view_manager_should_display', '__return_true' );

	}

	/**
	 * Test get_url() with a supplied URL and additional QS args.
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_get_url_with_url() {

		$url = parse_url( LLMS_View_Manager::get_url(  'visitor', 'https://mock.tld/test?whatever=0', array( 'more' => 'yes' ) ) );

		// Make sure URL is preserved properly.
		$this->assertEquals( 'https', $url['scheme'] );
		$this->assertEquals( 'mock.tld', $url['host'] );
		$this->assertEquals( '/test', $url['path'] );

		// Check query vars.
		parse_str( $url['query'], $qs );
		$this->assertEquals( '0', $qs['whatever'] );
		$this->assertEquals( 'yes', $qs['more'] );
		$this->assertEquals( 'visitor', $qs['llms-view-as'] );

		// Ensure generated nonce is valid.
		$this->assertEquals( 1, wp_verify_nonce( $qs['view_nonce'], 'llms-view-as' ) );

	}

	/**
	 * Test get_url() with a supplied URL and additional QS args.
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_get_url_without_url() {

		$_SERVER['REQUEST_URI'] = 'https://fake.tld';

		$url = parse_url( LLMS_View_Manager::get_url(  'student' ) );

		// Make sure URL is preserved properly.
		$this->assertEquals( 'https', $url['scheme'] );
		$this->assertEquals( 'fake.tld', $url['host'] );

		// Check query vars.
		parse_str( $url['query'], $qs );
		$this->assertEquals( 'student', $qs['llms-view-as'] );

		// Ensure generated nonce is valid.
		$this->assertEquals( 1, wp_verify_nonce( $qs['view_nonce'], 'llms-view-as' ) );

		$_SERVER['REQUEST_URI'] = '';

	}

	/**
	 * Test get_view() when there's nonce errors.
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_get_view_nonce_error() {

		// Nothing set.
		$this->assertEquals( 'self', LLMS_Unit_Test_Util::call_method( $this->main, 'get_view' ) );

		// Invalid nonce.
		$this->mockGetRequest( array(
			'view_nonce'   => 'fake',
			'llms-view-as' => 'student',
		) );
		$this->assertEquals( 'self', LLMS_Unit_Test_Util::call_method( $this->main, 'get_view' ) );

	}

	/**
	 * Test get_view() with an invalid view.
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_get_view_invalid_view() {

		$this->mock_view_data( 'fake' );
		$this->assertEquals( 'self', LLMS_Unit_Test_Util::call_method( $this->main, 'get_view' ) );

	}

	/**
	 * Test get_view() with valid data.
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_get_view() {

		foreach ( array_keys( LLMS_Unit_Test_Util::call_method( $this->main, 'get_views' ) ) as $view ) {

			$this->mock_view_data( $view );
			$this->assertEquals( $view, LLMS_Unit_Test_Util::call_method( $this->main, 'get_view' ) );

		}

	}

	/**
	 * Test modify_dashboard()
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_modify_dashboard() {

		// Unchanged when viewing as self.
		$this->assertNull( $this->main->modify_dashboard( null ) );

		// Visitors can't load the dashboard (they see forms).
		$this->mock_view_data( 'visitor' );
		$this->assertFalse( $this->main->modify_dashboard( null ) );

		// Students see the dashboard.
		$this->mock_view_data( 'student' );
		$this->assertTrue( $this->main->modify_dashboard( null ) );

	}

	/**
	 * Test modify_course_open().
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_modify_course_open() {

		$course = llms_get_post( $this->factory->post->create( array( 'post_type' => 'course' ) ) );

		$this->mock_view_data( 'visitor' );
		$this->assertFalse( $this->main->modify_course_open( false, $course ) );

		// Logged out user.
		$this->mock_view_data( 'self' );
		$this->assertFalse( $this->main->modify_course_open( false, $course ) );

		// Admin can do it.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertTrue( $this->main->modify_course_open( false, $course ) );

		// Instructor can't.
		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );
		wp_set_current_user( $instructor );
		$this->assertFalse( $this->main->modify_course_open( false, $course ) );

		$course->set_instructors( array(
			array(
				'id' => $instructor,
			)
		) );
		$this->assertTrue( $this->main->modify_course_open( false, $course ) );

	}

	/**
	 * Test modify_restrictions().
	 *
	 * @since 5.9.0
	 *
	 * @return void
	 */
	public function test_modify_restrictions() {

		$course = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$mock_restriction = array(
			'content_id'     => $course,
			'is_restricted'  => true,
			'reason'         => 'enrollment_course',
			'restriction_id' => $course,
		);

		$expected_success = wp_parse_args( array(
			'is_restricted' => false,
			'reason'        => 'role-access',
		), $mock_restriction );

		$this->mock_view_data( 'visitor' );
		$this->assertEquals( $mock_restriction, $this->main->modify_restrictions( $mock_restriction ) );

		// No user.
		$this->mock_view_data( 'self' );
		$this->assertEquals( $mock_restriction, $this->main->modify_restrictions( $mock_restriction ) );

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertEquals( $expected_success, $this->main->modify_restrictions( $mock_restriction ) );

		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );
		wp_set_current_user( $instructor );
		$this->assertEquals( $mock_restriction, $this->main->modify_restrictions( $mock_restriction ) );

		llms_get_post( $course )->set_instructors( array(
			array(
				'id' => $instructor,
			)
		) );

		$this->assertEquals( $expected_success, $this->main->modify_restrictions( $mock_restriction ) );

	}

	/**
	 * Test should_display() when viewing valid post types with a valid user
	 *
	 * @since 4.5.1
	 * @since 5.9.0 Add tests for instructors.
	 *
	 * @return void
	 */
	public function test_should_display_on_valid_post_types() {

		global $post;

		$admin     = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );

		foreach ( array( 'course', 'lesson', 'llms_membership', 'llms_quiz' ) as $post_type ) {

			wp_set_current_user( $admin );

			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

			wp_set_current_user( $instructor );
			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

			if ( in_array( $post_type, array( 'course', 'llms_membership' ), true ) ) {
				llms_get_post( $post )->set_instructors( array(
					array(
						'id' => $instructor,
					)
				) );
				$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
			}

		}

	}

	/**
	 * Test should_display() when viewing checkout valid with a valid user
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_on_checkout() {
		LLMS_Install::create_pages();
		$this->go_to( llms_get_page_url( 'checkout' ) );
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

	}

	/**
	 * Test should_display() when viewing the student dashboard with a valid user
	 *
	 * @since 4.16.0
	 *
	 * @return void
	 */
	public function test_should_display_on_dashboard() {
		LLMS_Install::create_pages();
		$this->go_to( llms_get_page_url( 'myaccount' ) );
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
	}

	/**
	 * Test should_display() when no user is present
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_no_user() {
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
	}

	/**
	 * Test should_display() when an invalid user is logged in
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_invalid_user() {
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'student' ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
	}

	/**
	 * Test should_display() on the admin panel
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_in_admin() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		set_current_screen( 'users.php' );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
		set_current_screen( 'front' ); // Reset for later tests.

	}

	/**
	 * Test should_display() on a post type archive
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_post_type_archive() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->go_to( get_post_type_archive_link( 'course' ) );
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );

	}

	/**
	 * Test should_display() when a valid using is viewing an invalid post type
	 *
	 * @since 4.5.1
	 *
	 * @return void
	 */
	public function test_should_display_on_invalid_post_types() {

		global $post;
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		foreach ( array( 'post', 'page' ) as $post_type ) {
			$post = $this->factory->post->create_and_get( compact( 'post_type' ) );
			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->main, 'should_display' ) );
		}

	}

}
