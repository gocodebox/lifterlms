<?php
/**
 * Tests for LLMS_Integration_Byddypress class
 *
 * @package LifterLMS/Tests/Integrations
 *
 * @group integrations
 * @group integration_buddypress
 *
 * @since [version]
 */
class LLMS_Test_Integration_Buddypress extends LLMS_Unit_Test_Case {

	/**
	 * Instance of a mock BuddyPress class.
	 *
	 * @var BuddyPress
	 */
	private $mock_buddypress = null;

	/**
	 * Instance of the BuddyPress integration class.
	 *
	 * @var LLMS_Integration_Buddypress
	 */
	private $main = null;

	/**
	 * Array of hooks added by the integration.
	 *
	 * @var array
	 */
	private $hooks = array();

	/**
	 * Setup the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		// Load mock.
		if ( ! $this->mock_buddypress ) {
			$this->setup_mock_buddypress();
		}

		$this->main = llms()->integrations()->get_integration( 'buddypress' );

		if ( ! $this->hooks ) {
			$this->setup_hooks();
		}

	}

	/**
	 * Teardown the test case.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		unset( $GLOBALS['bp_displayed_user'], $GLOBALS['bp_current_user'], $GLOBALS['bp_nav'] );

	}

	/**
	 * Test that attributes are setup properly.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_attributes() {

		$this->assertEquals( 'BuddyPress', $this->main->title );
		$this->assertTrue( ! empty( $this->main->description ) );

	}

	/**
	 * Test configure().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_configure() {

		// Disable the integration.
		update_option( 'llms_integration_buddypress_enabled', 'no' );

		// Remove all the set hooks.
		$this->update_hooks( 'remove' );

		LLMS_Unit_Test_Util::call_method( $this->main, 'configure' );

		// All hooks should be false when calling has_action()/has_filter().
		$this->assertHooks( false );

		// Re-enable the integration.
		$this->setup_mock_buddypress();
		LLMS_Unit_Test_Util::call_method( $this->main, 'configure' );

		// All hooks should be configured.
		$this->assertHooks();

	}

	/**
	 * Test is_installed().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_installed() {

		$this->assertTrue( $this->main->is_installed() );

	}

	/**
	 * Test populate_profile_endpoints().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_populate_profile_endpoints() {

		// Populate all possible endpoints.
		LLMS_Unit_Test_Util::call_method( $this->main, 'populate_profile_endpoints' );

		// By default they're all the default LLMS_Student_Dashboard endpoints except 'dashboard' and 'signout'.
		$dashboard_endpoints = LLMS_Student_Dashboard::get_tabs();
		$endpoints           = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'endpoints' );

		$this->assertEquals(
			array(
				'dashboard',
				'signout',
			),
			array_values(
				array_diff(
					array_keys( $dashboard_endpoints ),
					array_keys( $endpoints )
				)
			)
		);

		// Check the endpoints do not have the fields 'nav_item', 'url', 'paginate'.
		foreach ( array( 'nav_item', 'url', 'paginate' ) as $field ) {
			$this->assertEmpty( array_column( $endpoints, $field ), $field );
		}

		// Reset private property endpoints.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'endpoints', null );

	}

	/**
	 * Test get_profile_endpoints().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_profile_endpoints() {

		// Get all possible endpoints ($active_only=false).
		$profile_endpoints = LLMS_Unit_Test_Util::call_method( $this->main, 'get_profile_endpoints', array( false ) );
		$endpoints         = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'endpoints' );

		// They're equal to the populated endpoints.
		$this->assertEquals(
			$endpoints,
			$profile_endpoints
		);

		// Set the profile endpoints option as only the first of all the available endpoints.
		$this->main->set_option( 'profile_endpoints', array( key( $endpoints ) ) );
		// Get active endpoints ($active_only=false).
		$profile_endpoints = LLMS_Unit_Test_Util::call_method( $this->main, 'get_profile_endpoints', array( false ) );
		// They're equal to the populated endpoints.
		$this->assertEquals(
			$endpoints,
			$profile_endpoints
		);

		// Get active only.
		$profile_endpoints = LLMS_Unit_Test_Util::call_method( $this->main, 'get_profile_endpoints' );
		// They only contains the first possible endpoint.
		$this->assertEquals(
			array( key( $endpoints ) => reset( $endpoints ) ),
			$profile_endpoints
		);

		// Set the profile endpoints option as empty string.
		$this->main->set_option( 'profile_endpoints', array( '' ) );
		// Get active only.
		$profile_endpoints = LLMS_Unit_Test_Util::call_method( $this->main, 'get_profile_endpoints' );
		// Empty array.
		$this->assertEmpty( $profile_endpoints );

		// Reset private property endpoints.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'endpoints', null );

	}

	/**
	 * Test get_profile_endpoints_options().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_profile_endpoints_options() {

		$endpoints_options = LLMS_Unit_Test_Util::call_method( $this->main, 'get_profile_endpoints_options' );
		// Must be an array having as keys all the possible endpoints, and values the endpoints titles.
		$endpoints = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'endpoints' );

		$this->assertEquals(
			array_combine(
				array_keys( $endpoints ),
				array_column( $endpoints, 'title' )
			),
			$endpoints_options
		);

		// Reset private property endpoints.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'endpoints', null );

	}

	/**
	 * Test add_profile_nav_items()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_add_profile_nav_items() {

		global $bp_nav, $bp_displayed_user, $bp_current_user;

		// User not logged in.
		$this->main->add_profile_nav_items();

		$this->assertEmpty( $bp_nav );

		// Log in as admin.
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		// Visit someone else profile.
		$bp_displayed_user = 'test_user';
		$this->assertEmpty( $bp_nav );

		// Visit admin profile.
		$bp_displayed_user = 'admin';
		$this->assertEmpty( $bp_nav );

	}

	/**
	 * Add or remove hooks based on hooks defined in the $this->hooks array.
	 *
	 * @since [version]
	 *
	 * @param string $action Either "add" or "remove".
	 * @return void
	 */
	private function update_hooks( $action = 'add' ) {

		foreach ( $this->hooks as $hook ) {

			$function = sprintf( '%1$s_%2$s', $action, $hook['type'] );
			$function( $hook['hook'], $hook['method'], $hook['priority'] );

		}

	}

	/**
	 * Run assertions for all hooks in the $this->hooks array.
	 *
	 * @since [version]
	 *
	 * @param mixed $equals If `null`, asserts that the priority matches the configured priority. Otherwise all hooks equal this value.
	 * @return void
	 */
	private function assertHooks( $equals = null ) {

		foreach( $this->hooks as $hook ) {

			$function = sprintf( 'has_%s', $hook['type'] );
			$this->assertEquals( is_null( $equals ) ? $hook['priority'] : $equals, $function( $hook['hook'], $hook['method'] ), $hook['hook'] );

		}

	}

	/**
	 * Setup all the hooks defined in the configuration method.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function setup_hooks() {

		$this->hooks = array(
			array(
				'type'     => 'action',
				'hook'     => 'bp_setup_nav',
				'method'   => array( $this->main, 'add_profile_nav_items' ),
				'priority' => 10,
			),
			array(
				'type'     => 'filter',
				'hook'     => 'llms_page_restricted_before_check_access',
				'method'   => array( $this->main, 'restriction_checks' ),
				'priority' => 40,
			),
			array(
				'type'     => 'filter',
				'hook'     => 'lifterlms_update_account_redirect',
				'method'   => array( $this->main, 'maybe_alter_update_account_redirect' ),
				'priority' => 10,
			),
		);

	}

	/**
	 * Setup the mock BuddyPress class and functions.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function setup_mock_buddypress() {

		// Mock functions.
		if ( ! function_exists( 'bp_loggedin_user_domain' ) ) {
			function bp_loggedin_user_domain() {
				global $bp_current_user;
				return is_user_logged_in() ? ( $bp_current_user ?? 'admin' ) : '';
			}
		}

		if ( ! function_exists( 'bp_displayed_user_domain' ) ) {
			function bp_displayed_user_domain() {
				global $bp_displayed_user;
				return $bp_displayed_user ?? '';
			}
		}
		if ( ! function_exists( 'bp_core_new_nav_item' ) ) {
			function bp_core_new_nav_item( $args ) {
				global $bp_nav;
				if ( empty( $args['slug'] ) ) {
					return;
				}
				$bp_nav[$args['slug']] = $args;
			}
		}
		if ( ! function_exists( 'bp_core_new_subnav_item' ) ) {
			function bp_core_new_subnav_item( $args ) {
				global $bp_nav;
				if ( empty( $args['slug'] ) || empty( $args['parent_slug'] || empty( $bp_nav[ $args['parent_slug'] ] ) ) ) {
					return;
				}
				$bp_nav[ $args['parent_slug'] ] = is_array( $bp_nav[ $args['parent_slug'] ] ) ? $bp_nav[ $args['parent_slug'] ] : array();
				$bp_nav[ $args['parent_slug'] ][ $args['slug'] ] = $args;
			}
		}

		// Create the mock buddypress class.
		$this->mock_buddypress = $this->getMockBuilder( 'BuddyPress' )
			->getMock();

		// Enable the integration.
		update_option( 'llms_integration_buddypress_enabled', 'yes' );

		// Refresh cached available integrations list.
		llms()->integrations()->get_available_integrations();

	}

}
