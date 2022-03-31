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
	protected $mock_buddypress = null;

	/**
	 * Instance of the bbPress integration class.
	 *
	 * @var LLMS_Integration_Buddypress
	 */
	protected $main = null;

	/**
	 * Array of hooks added by the integration.
	 *
	 * @var array
	 */
	protected $hooks = array();


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
	 * @param  mixed $equals If `null`, asserts that the priority matches the configured priority. Otherwise all hooks equal this value.
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
	protected function setup_mock_buddypress() {

		// Create the mock buddypress class.
		$this->mock_buddypress = $this->getMockBuilder( 'BuddyPress' )
			->getMock();

		// Enable the integration.
		update_option( 'llms_integration_buddypress_enabled', 'yes' );

		// Refresh cached available integrations list.
		llms()->integrations()->get_available_integrations();

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
	 * Test is_installed()
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_installed() {

		$this->assertTrue( $this->main->is_installed() );

	}

	/**
	 * Test get_profile_endpoints().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_populate_profile_ndpoints() {

		// Populate all possible endpoints.
		$this->main->populate_profile_endpoints();

		// By default they're all the default LLMS_Student_Dashboard endpoints except 'dashboard' and 'signout'.
		$dashboard_endpoints = LLMS_Student_Dashboard::get_tabs();

		$this->assertEquals(
			array(
				'dashboard',
				'signout',
			),
			array_values(
				array_diff(
					array_keys( $dashboard_endpoints ),
					array_keys( LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'endpoints' ) )
				)
			)
		);

		// Reset private property endpoints.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'endpoints', null );

	}

}
