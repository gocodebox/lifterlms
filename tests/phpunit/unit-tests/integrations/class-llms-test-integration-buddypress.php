<?php
/**
 * Tests for LLMS_Integration_Byddypress class
 *
 * @package LifterLMS/Tests/Integrations
 *
 * @group integrations
 * @group integration_buddypress
 *
 * @since 6.3.0
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
	 * Logged in user slug to be used to build the profile domain.
	 *
	 * @var string
	 */
	private $loggedin_user_slug;

	/**
	 * Displayed user slug to be used to build the profile domain.
	 *
	 * @var string
	 */
	private $displayed_user_slug;


	/**
	 * Displayed user id to be used to build the profile nav.
	 *
	 * @var string
	 */
	private $displayed_user_id;

	/**
	 * Setup the test case.
	 *
	 * @since 6.3.0
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
	 * @since 6.3.0
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		// Reset private property endpoints.
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'endpoints', null );
		unset( $GLOBALS['bp'] );

	}

	/**
	 * Test that attributes are setup properly.
	 *
	 * @since 6.3.0
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
	 * @since 6.3.0
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
	 * @since 6.3.0
	 *
	 * @return void
	 */
	public function test_is_installed() {

		$this->assertTrue( $this->main->is_installed() );

	}

	/**
	 * Test populate_profile_endpoints().
	 *
	 * @since 6.3.0
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

	}

	/**
	 * Test get_profile_endpoints().
	 *
	 * @since 6.3.0
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

	}

	/**
	 * Test get_profile_endpoints_options().
	 *
	 * @since 6.3.0
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

	}

	/**
	 * Test add_profile_nav_items().
	 *
	 * @since 6.3.0
	 * @since [version] Main nav item always shown.
	 *
	 * @return void
	 */
	public function test_add_profile_nav_items() {

		$bp = buddypress();

		// User not logged in.
		$this->_setup_members_nav();
		$this->_set_bp_is_my_profile_false();

		$this->main->add_profile_nav_items();
		$endpoints = LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'endpoints' );

		$this->assertNotEmpty(
			$bp->members->nav->nav[0][ 'courses' ]
		);

		foreach ( $endpoints as $key => $endpoint ) {
			$this->assertNotEmpty(
				$bp->members->nav->nav[0][ 'courses/' . $endpoint['endpoint'] ]
			);
			$this->assertEquals(
				$endpoint['title'],
				$bp->members->nav->nav[0][ 'courses/' . $endpoint['endpoint'] ]->name
			);
			// No access to this.
			$this->assertFalse(
				$bp->members->nav->nav[0][ 'courses/' . $endpoint['endpoint'] ]->user_has_access
			);
		}

		// Log in as admin.
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$this->loggedin_user_slug = 'admin';
		$this->_set_loggedin_user_domain();

		// Visit someone else profile: cannot see other's courses related endpoints.
		$this->_set_bp_is_my_profile_false();
		$this->displayed_user_id = $admin_id + 1;
		$this->_set_displayed_user_id();
		$this->_setup_members_nav();
		$this->main->add_profile_nav_items();
		foreach ( $endpoints as $key => $endpoint ) {
			$this->assertNotEmpty(
				$bp->members->nav->nav[ $this->displayed_user_id ][ 'courses/' . $endpoint['endpoint'] ]
			);
			$this->assertEquals(
				$endpoint['title'],
				$bp->members->nav->nav[ $this->displayed_user_id ][ 'courses/' . $endpoint['endpoint'] ]->name
			);
			// No access to the endpoint's content.
			$this->assertFalse(
				$bp->members->nav->nav[ $this->displayed_user_id ][ 'courses/' . $endpoint['endpoint'] ]->user_has_access
			);
		}

		// 'admin' visiting 'admin' profile.
		$this->_set_bp_is_my_profile_true();
		$this->displayed_user_id = $admin_id;
		$this->_set_displayed_user_id();
		$this->_setup_members_nav();
		$this->main->add_profile_nav_items();
		$this->assertNotEmpty( $bp->members->nav->nav[ $this->displayed_user_id ] );

		$this->assertNotEmpty(
			$bp->members->nav->nav[ $this->displayed_user_id ][ 'courses' ]
		);

		// Check all the endpoints are registered as subnav items of 'course'.
		foreach ( $endpoints as $key => $endpoint ) {
			$this->assertNotEmpty(
				$bp->members->nav->nav[ $this->displayed_user_id ][ 'courses/' . $endpoint['endpoint'] ]
			);
			$this->assertEquals(
				$endpoint['title'],
				$bp->members->nav->nav[ $this->displayed_user_id ][ 'courses/' . $endpoint['endpoint'] ]->name
			);
			// Can see the endpoint content.
			$this->assertTrue(
				$bp->members->nav->nav[ $this->displayed_user_id ][ 'courses/' . $endpoint['endpoint'] ]->user_has_access
			);
		}

		// Reset.
		$this->_clear_bp_is_my_profile();
		$this->_clear_displayed_user_domain();
		$this->_clear_loggedin_user_domain();
		$this->_clear_displayed_user_id();

	}

	/**
	 * Test endpoint_content() method.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	public function test_endpoint_content() {

		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$profile_endpoints = LLMS_Unit_Test_Util::call_method( $this->main, 'get_profile_endpoints' );
		foreach ( $profile_endpoints as $key => $endpoint ) {

			$content = $this->get_output( array( $this->main, 'endpoint_content' ), array( $key, $endpoint['content'] ) );
			// Current endpoint set.
			$this->assertEquals(
				$key,
				LLMS_Unit_Test_Util::get_private_property_value( $this->main, 'current_endpoint_key' ),
				$key
			);

			// BuddyPress tab content is the $endpoint['content'].
			$this->assertEquals(
				$this->get_output( $endpoint['content'] ),
				$content,
				$key
			);

			// Remove the current endpoint callback, for the next loop.
			remove_action( 'bp_template_content', $endpoint['content'] );

		}
	}

	/**
	 * Test modify_paginate_links() method.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	public function test_modify_paginate_links() {

		global $wp_rewrite;

		$original_permalink_structure = get_option( 'permalink_structure' );
		// Ugly permalinks, bail.
		if ( empty( $origianl_permalink_structure ) ) {
			$this->assertEquals(
				'whatever',
				$this->main->modify_paginate_links(
					'whatever'
				)
			);
		}

		// Pretty permalinks.
		update_option( 'permalink_structure', '/%postname%/' );
		$wp_rewrite->init();

		// Enroll to many courses.
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		$this->loggedin_user_slug = 'admin';
		$this->_set_loggedin_user_domain();
		//$this->factory->student->create_and_enroll_many( 30, $this->factory->post->create( array( 'post_type' => 'course' ) ) );

		// 'admin' visiting 'admin' profile.
		$this->_set_bp_is_my_profile_true();
		$this->displayed_user_id = $admin_id;
		$this->_set_displayed_user_id();
		$this->_setup_members_nav();
		$this->main->add_profile_nav_items();

		// Go to my-grades tab, not the first subnav.
		$my_grades = home_url() . '/members/' . $this->loggedin_user_slug . '/courses/my-grades/';
		$this->go_to( $my_grades );
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'current_endpoint_key', 'my-grades' );

		// Link to page 1: page/1/ stripped.
		$this->assertEquals(
			$my_grades,
			$this->main->modify_paginate_links(
				$my_grades . 'page/1/'
			)
		);

		// Link to page 2: nothing to do.
		$this->assertEquals(
			$my_grades . 'page/2/',
			$this->main->modify_paginate_links(
				$my_grades . 'page/2/'
			)
		);

		// Link to page 1 but with query args.
		$this->assertEquals(
			$my_grades . '?query=arg_1',
			$this->main->modify_paginate_links(
				$my_grades . 'page/1/?query=arg_1'
			)
		);

		// Test first subnav.
		$fist_subnav_first_page = home_url() . '/members/' . $this->loggedin_user_slug . '/courses/';
		$profile_endpoints      = LLMS_Unit_Test_Util::call_method( $this->main, 'get_profile_endpoints' );
		$first_endpoint_slug    = reset( $profile_endpoints )['endpoint'];
		$first_endpoint_key     = key( $profile_endpoints );
		// Go to first subnav.
		$this->go_to( $fist_subnav_first_page );
		set_query_var( 'page', 1 );
		LLMS_Unit_Test_Util::set_private_property( $this->main, 'current_endpoint_key', $first_endpoint_key );

		// Link to page 2.
		$this->assertEquals(
			$fist_subnav_first_page . $first_endpoint_slug  . '/page/2/',
			$this->main->modify_paginate_links(
				$fist_subnav_first_page . 'page/2/'
			)
		);

		// Go to page 2.
		$this->go_to( $fist_subnav_first_page . $first_endpoint_slug  . '/page/2/' );
		set_query_var( 'page', 2 );

		// Link to page 1: expect link to the first subnav first page === defaults to the parent nav URL.
		$this->assertEquals(
			$fist_subnav_first_page,
			$this->main->modify_paginate_links(
				$fist_subnav_first_page . $first_endpoint_slug  . '/page/1/'
			)
		);

		// Reset.
		$this->_clear_bp_is_my_profile();
		$this->_clear_displayed_user_domain();
		$this->_clear_loggedin_user_domain();
		$this->_clear_displayed_user_id();
		update_option( 'permalink_structure', $original_permalink_structure );
		$wp_rewrite->init();
		set_query_var( 'page', 1 );

	}

	/**
	 * Add or remove hooks based on hooks defined in the $this->hooks array.
	 *
	 * @since 6.3.0
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
	 * @since 6.3.0
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
	 * @since 6.3.0
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
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function setup_mock_buddypress() {

		// Mock functions.
		if ( ! function_exists( 'bp_loggedin_user_domain' ) ) {
			function bp_loggedin_user_domain() {
				/**
				 * BuddyPress filter.
				 *
				 * @since BuddyPress 1.0.0
				 */
				return apply_filters( 'bp_loggedin_user_domain', '' );
			}
		}
		// Mock functions.
		if ( ! function_exists( 'bp_displayed_user_id' ) ) {
			function bp_displayed_user_id() {
				/**
				 * BuddyPress filter.
				 *
				 * @since BuddyPress 1.0.0
				 */
				return apply_filters( 'bp_displayed_user_id', 0 );
			}
		}
		if ( ! function_exists( 'bp_is_my_profile' ) ) {
			function bp_is_my_profile() {
				/**
				 * BuddyPress filter.
				 *
				 * @since BuddyPress 1.2.4
				 */
				return apply_filters( 'bp_is_my_profile', false );
			}
		}
		if ( ! function_exists( 'bp_core_new_nav_item' ) ) {
			function bp_core_new_nav_item( $args ) {

				if ( empty( $args['slug'] ) ) {
					return false;
				}
				$bp                           = buddypress();
				$args['primary']              = true;
				$args['link']                 = trailingslashit( bp_loggedin_user_domain() . $args['slug'] );
				$args['default_subnav_slug '] = $args['default_subnav_slug'];

				$bp->members->nav->nav[ $bp->members->nav->object_id ][$args['slug']] = new ArrayObject(
					$args, ArrayObject::ARRAY_AS_PROPS
				);

			}
		}
		if ( ! function_exists( 'bp_core_new_subnav_item' ) ) {
			function bp_core_new_subnav_item( $args ) {

				if ( empty( $args['slug'] ) || empty( $args['parent_slug'] ) ) {
					return;
				}

				$bp = buddypress();
				$args['secondary'] = true;
				$args['link']      = trailingslashit( $args['parent_url'] . $args['slug'] );

				$parent_nav = $bp->members->nav->get_primary(
					array(
						'slug' => $args['parent_slug'],
					),
					false
				);

				// If this sub item is the default for its parent, skip the slug.
				if ( $parent_nav ) {
					$parent_nav_item = reset( $parent_nav );
					if ( ! empty( $parent_nav_item->default_subnav_slug ) && $args['slug'] === $parent_nav_item->default_subnav_slug ) {
						$args['link'] = trailingslashit( $args['parent_url'] );
					}
				}

				$bp->members->nav->nav[ $bp->members->nav->object_id ][$args['parent_slug'] . '/' . $args['slug']] = new ArrayObject(
					$args, ArrayObject::ARRAY_AS_PROPS
				);

			}
		}
		if ( ! function_exists( 'bp_core_load_template' ) ) {
			function bp_core_load_template( $default_template = '' ) {
				do_action( 'bp_template_content' );
			}
		}
		if ( ! function_exists( 'buddypress' ) ) {
			function buddypress() {
				global $bp;
				return $bp;
			}
		}

		// Create the mock BuddyPress class.
		$this->mock_buddypress = $this->getMockBuilder( 'BuddyPress' )
			->disableOriginalConstructor()
			->getMock();

		$GLOBALS['bp'] = $this->mock_buddypress;

		// Mock user properties.
		$this->mock_buddypress->displayed_user = new stdClass();
		$this->mock_buddypress->loggedin_user  = new stdClass();
		// Mock members and members->nav properties, so to be able to mock members->nav->get_secondary method.
		$this->mock_buddypress->members      = new stdClass();
		$this->mock_buddypress->members->nav = $this->getMockBuilder( 'BP_Core_Nav' )
			->disableOriginalConstructor()
			->setMethods( array( 'get_secondary', 'get_primary' ) )
			->getMock();

		// Mock get_primary and get_secondary method for the members->nav.
		$this->mock_buddypress->members->nav->
			method( 'get_primary' )->will(
				$this->returnCallback(
					function ( $args ) {

						$params = wp_parse_args( $args, array( 'primary' => true ) );

						// This parameter is not overridable.
						if ( empty( $params['primary'] ) ) {
							return false;
						}

						$bp = buddypress();
						$primary_nav =wp_list_filter( $bp->members->nav->nav[ $bp->members->nav->object_id ], $params );

						if ( ! $primary_nav ) {
							return false;
						}

						return $primary_nav;

					}
				)
			);
		$this->mock_buddypress->members->nav->
			method( 'get_secondary' )->will(
				$this->returnCallback(
					function ( $args ) {

						$params = wp_parse_args( $args, array( 'parent_slug' => '' ) );

						// No need to search children if the parent is not set.
						if ( empty( $params['parent_slug'] ) && empty( $params['secondary'] ) ) {
							return false;
						}

						$bp = buddypress();
						$secondary_nav = wp_list_filter( $bp->members->nav->nav[ $bp->members->nav->object_id ], $params );

						if ( ! $secondary_nav ) {
							return false;
						}

						return $secondary_nav;

					}
				)
			);

		$this->mock_buddypress->members->nav->nav = array();
		$this->mock_buddypress->members->nav->object_id = 0;

		// Enable the integration.
		update_option( 'llms_integration_buddypress_enabled', 'yes' );

		// Refresh cached available integrations list.
		llms()->integrations()->get_available_integrations();

	}


	/**
	 * Utility to set the logged in user domain.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function _set_loggedin_user_domain() {
		add_filter( 'bp_loggedin_user_domain', array( $this, 'loggedin_user_domain_filter_cb' ) );
	}

	/**
	 * Utility to clear the logged in user domain.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function _clear_loggedin_user_domain() {
		remove_filter( 'bp_loggedin_user_domain', array( $this, 'loggedin_user_domain_filter_cb' ) );
	}

	/**
	 * Call back that sets the logged in user domain.
	 *
	 * @since 6.3.0
	 *
	 * @return string
	 */
	public function loggedin_user_domain_filter_cb() {
		return home_url() . '/members/' . $this->loggedin_user_slug . '/';
	}

	/**
	 * Utility to set the displayed user id.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function _set_displayed_user_id() {
		add_filter( 'bp_displayed_user_id', array( $this, 'displayed_user_id_filter_cb' ) );
	}

	/**
	 * Utility to clear the displayed user id.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function _clear_displayed_user_id() {
		remove_filter( 'bp_displayed_user_id', array( $this, 'displayed_user_id_filter_cb' ) );
	}

	/**
	 * Call back that sets the displayed user id.
	 *
	 * @since 6.3.0
	 *
	 * @return string
	 */
	public function displayed_user_id_filter_cb() {
		return $this->displayed_user_id;
	}


	/**
	 * Utility to set the displayed user domain.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function _set_displayed_user_domain() {
		add_filter( 'bp_displayed_user_domain', array( $this, 'displayed_user_domain_filter_cb' ) );
	}

	/**
	 * Utility to clear the displayed user domain.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function _clear_displayed_user_domain() {
		remove_filter( 'bp_displayed_user_domain', array( $this, 'displayed_user_domain_filter_cb' ) );
	}

	/**
	 * Call back that sets the displayed user domain.
	 *
	 * @since 6.3.0
	 *
	 * @return string
	 */
	private function displayed_user_domain_filter_cb() {
		return home_url() . '/members/' . $this->displayed_user_slug . '/';
	}

	/**
	 * Utility to simulate we're on the BuddyPress my profile.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function _set_bp_is_my_profile_true() {
		add_filter( 'bp_is_my_profile', '__return_true' );
	}

	/**
	 * Utility to simulate we're NOT on the BuddyPress my profile.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function _set_bp_is_my_profile_false() {
		remove_filter( 'bp_is_my_profile', '__return_false' );
	}

	/**
	 * Utility to clear simulations.
	 *
	 * @since 6.3.0
	 *
	 * @return void
	 */
	private function _clear_bp_is_my_profile() {
		remove_filter( 'bp_is_my_profile', '__return_true' );
		remove_filter( 'bp_is_my_profile', '__return_false' );
	}

	/**
	 * Utility to setup members nav.
	 *
	 * This is done in the BP_Core_Nav constructor.
	 *
	 * @since 6.3.0
	 * @since [version] Setup nav also for not logged in users.
	 *
	 * @return void
	 */
	private function _setup_members_nav() {

		$displayed_user_id = (int) bp_displayed_user_id();

		$bp = buddypress();

		$bp->members->nav->object_id = $displayed_user_id;
		$bp->members->nav->nav[ $displayed_user_id ] = array();

	}

}
