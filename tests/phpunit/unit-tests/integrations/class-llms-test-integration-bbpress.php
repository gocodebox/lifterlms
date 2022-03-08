<?php
/**
 * Tests for LLMS_Admin_Review class
 *
 * @package LifterLMS/Tests/Integrations
 *
 * @group integrations
 * @group integration_bbpress
 *
 * @since 3.37.11
 * @since 3.38.1 Added test on forum values saved as array of strings.
 */
class LLMS_Test_Integration_BBPress extends LLMS_Unit_Test_Case {

	/**
	 * Instance of a mock bbPress class.
	 *
	 * @var bbPress
	 */
	protected $mock_bbPress = null;

	/**
	 * Instance of the bbPress integration class.
	 *
	 * @var LLMS_Integration_BBPress
	 */
	protected $main = null;

	/**
	 * Array of hooks added by the integration.
	 *
	 * @var array
	 */
	protected $hooks = array();

	/**
	 * Add or remove hooks based on hooks defined in the $this->hooks array.
	 *
	 * @since 3.37.11
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
	 * @since  3.37.11
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
	 * @since  3.37.11
	 *
	 * @return void
	 */
	private function setup_hooks() {

		$this->hooks = array(
			array(
				'type'     => 'filter',
				'hook'     => 'lifterlms_engagement_triggers',
				'method'   => array( $this->main, 'register_engagement_triggers' ),
				'priority' => 10,
			),
			array(
				'type'     => 'filter',
				'hook'     => 'lifterlms_external_engagement_query_arguments',
				'method'   => array( $this->main, 'engagement_query_args' ),
				'priority' => 10,
			),
			array(
				'type'     => 'filter',
				'hook'     => 'llms_load_shortcodes',
				'method'   => array( $this->main, 'register_shortcodes' ),
				'priority' => 10,
			),
			array(
				'type'     => 'filter',
				'hook'     => 'llms_membership_restricted_post_types',
				'method'   => array( $this->main, 'add_membership_restrictions' ),
				'priority' => 10,
			),
			array(
				'type'     => 'filter',
				'hook'     => 'llms_page_restricted_before_check_access',
				'method'   => array( $this->main, 'restriction_checks_memberships' ),
				'priority' => 40,
			),
			array(
				'type'     => 'filter',
				'hook'     => 'llms_page_restricted_before_check_access',
				'method'   => array( $this->main, 'restriction_checks_courses' ),
				'priority' => 50,
			),
			array(
				'type'     => 'filter',
				'hook'     => 'llms_metabox_fields_lifterlms_course_options',
				'method'   => array( $this->main, 'course_settings_fields' ),
				'priority' => 10,
			),
			array(
				'type'     => 'filter',
				'hook'     => 'llms_get_course_properties',
				'method'   => array( $this->main, 'add_course_props' ),
				'priority' => 10,
			),

			array(
				'type'     => 'action',
				'hook'     => 'bbp_new_topic',
				'method'   => array( llms()->engagements(), 'maybe_trigger_engagement' ),
				'priority' => 10,
			),
			array(
				'type'     => 'action',
				'hook'     => 'bbp_new_reply',
				'method'   => array( llms()->engagements(), 'maybe_trigger_engagement' ),
				'priority' => 10,
			),
			array(
				'type'     => 'action',
				'hook'     => 'llms_metabox_after_save_lifterlms-course-options',
				'method'   => array( $this->main, 'save_course_settings' ),
				'priority' => 10,
			),
			array(
				'type'     => 'action',
				'hook'     => 'llms_content_restricted_by_bbp_course_forum',
				'method'   => array( $this->main, 'handle_course_forum_restriction' ),
				'priority' => 10,
			),
		);

	}

	/**
	 * Setup the mock bbPress class and functions.
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	protected function setup_mock_bbPress() {

		// Mock functions.
		if ( ! function_exists( 'bbp_get_forum_post_type' ) ) {
			function bbp_get_forum_post_type() {
				return 'mock_forum_post_type';
			}
		}

		// Create the mock bbPress class.
		$this->mock_bbPress = $this->getMockBuilder( 'bbPress' )
			->getMock();

		// Enable the integration.
		update_option( 'llms_integration_bbpress_enabled', 'yes' );

		// Refresh cached available integrations list.
		llms()->integrations()->get_available_integrations();

	}

	/**
	 * Setup the test case.
	 *
	 * @since 3.37.11
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		// Load mock.
		if ( ! $this->mock_bbPress ) {
			$this->setup_mock_bbPress();
		}

		$this->main = llms()->integrations()->get_integration( 'bbpress' );

		if ( ! $this->hooks ) {
			$this->setup_hooks();
		}

	}

	/**
	 * Test that attributes are setup properly.
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_attributes() {

		$this->assertEquals( 'bbPress', $this->main->title );
		$this->assertTrue( ! empty( $this->main->description ) );

	}

	/**
	 * Test configure()
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_configure() {

		// Disable the integration.
		update_option( 'llms_integration_bbpress_enabled', 'no' );

		// Remove all the set hooks.
		$this->update_hooks( 'remove' );

		LLMS_Unit_Test_Util::call_method( $this->main, 'configure' );

		// All hooks should be false when calling has_action()/has_filter().
		$this->assertHooks( false );


		// Re-enable the integration.
		$this->setup_mock_bbPress();
		LLMS_Unit_Test_Util::call_method( $this->main, 'configure' );

		// All hooks should be configured.
		$this->assertHooks();

	}

	/**
	 * Test add_course_props()
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_add_course_props() {

		$this->assertEquals( array( 'bbp_forum_ids' => 'array' ), $this->main->add_course_props( array(), 'mock' ) );

	}

	/**
	 * Test add_membership_restrictions()
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_add_membership_restrictions() {

		$this->assertEquals( array( 'mock_forum_post_type' ), $this->main->add_membership_restrictions( array() ) );

	}

	/**
	 * Test course_settings_field()
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_course_settings_fields() {

		$res = $this->main->course_settings_fields( array() )[0];

		$this->assertEquals( 'bbPress', $res['title'] );
		$this->assertEquals( 1, count( $res['fields'] ) );

		$this->assertequals( '_llms_bbp_forum_ids', $res['fields'][0]['id'] );

	}

	/**
	 * Test engagement_query_args() for non bbPress hooks.
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_engagement_query_args_not_supported() {

		$expect = array(
			'trigger_type'    => 'mock',
			'related_post_id' => 123,
			'user_id'         => 456,
		);
		$this->assertEquals( $expect, $this->main->engagement_query_args( $expect, 'mock_action', array() ) );

	}

	/**
	 * Test engagement_query_args() for a new reply hook.
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_engagement_query_args_new_reply() {

		$args = array(
			'trigger_type'    => 'mock',
			'related_post_id' => 123,
			'user_id'         => 456,
		);

		$expect = array(
			'trigger_type'    => 'bbp_new_reply',
			'related_post_id' => '',
			'user_id'         => 4,
		);

		$this->assertEquals( $expect, $this->main->engagement_query_args( $args, 'bbp_new_reply', array( 0, 1, 2, 3, 4, ) ) );

	}

	/**
	 * Test engagement_query_args() for a new topic
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_engagement_query_args_new_topic() {

		$args = array(
			'trigger_type'    => 'mock',
			'related_post_id' => 123,
			'user_id'         => 456,
		);

		$expect = array(
			'trigger_type'    => 'bbp_new_topic',
			'related_post_id' => '',
			'user_id'         => 3,
		);

		$this->assertEquals( $expect, $this->main->engagement_query_args( $args, 'bbp_new_topic', array( 0, 1, 2, 3 ) ) );

	}

	/**
	 * Test handle_course_forum_restriction()
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_handle_course_forum_restriction() {

		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( sprintf( '%s [302] YES', get_permalink( $id ) ) );

		try {

			$this->main->handle_course_forum_restriction( array( 'restriction_id' => $id ) );

		} catch( LLMS_Unit_Test_Exception_Redirect $exception ) {

			$notices = llms_get_notices();

			$this->assertStringContains( 'llms-error', $notices );
			$this->assertStringContains( 'You must be enrolled in this course to access the course forum.', $notices );

			throw $exception;
		}

	}

	/**
	 * Test get_course_forum_ids()
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_get_course_forum_ids() {

		// Ensure property filter is applied.
		LLMS_Unit_Test_Util::call_method( $this->main, 'configure' );

		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		// Nothing set.
		$this->assertEquals( array(), $this->main->get_course_forum_ids( $id ) );

		// Empty string.
		update_post_meta( $id, '_llms_bbp_forum_ids', '' );
		$this->assertEquals( array(), $this->main->get_course_forum_ids( $id ) );

		// Empty array.
		update_post_meta( $id, '_llms_bbp_forum_ids', array() );
		$this->assertEquals( array(), $this->main->get_course_forum_ids( $id ) );

		// Has forums.
		update_post_meta( $id, '_llms_bbp_forum_ids', array( 1, 2, 3 ) );
		$this->assertEquals( array( 1, 2, 3 ), $this->main->get_course_forum_ids( $id ) );

	}

	/**
	 * Test get_forum_course_restrictions()
	 *
	 * @since 3.37.11
	 * @since 3.38.1 Made sure it's able to match forum ids either saved as strings or integers.
	 *
	 * @return void
	 */
	public function test_get_forum_course_restrictions() {

		// No restrictions for a fake forum.
		$this->assertEquals( array(), $this->main->get_forum_course_restrictions( 3452 ) );

		// Restricted to one course.
		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		update_post_meta( $id, '_llms_bbp_forum_ids', array( 9239 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 9239 ) );

		// Restricted to two courses.
		$id2 = $this->factory->post->create( array( 'post_type' => 'course' ) );
		update_post_meta( $id2, '_llms_bbp_forum_ids', array( 9239 ) );
		$this->assertEquals( array( $id, $id2 ), $this->main->get_forum_course_restrictions( 9239 ) );

		// Restricted to two courses, second course forum ids saved as strings.
		update_post_meta( $id, '_llms_bbp_forum_ids', array( 9239, 1008 ) );
		update_post_meta( $id2, '_llms_bbp_forum_ids', array( '9239', '1008', '1007' ) );

		// Make sure we don't match a forum id which is part of one of the saved values.
		$this->assertNotEquals( array( $id, $id2 ), $this->main->get_forum_course_restrictions( 923 ) );

		$this->assertEquals( array( $id, $id2 ), $this->main->get_forum_course_restrictions( 9239 ) );
		$this->assertEquals( array( $id, $id2 ), $this->main->get_forum_course_restrictions( 1008 ) );
		$this->assertEquals( array( $id2 ), $this->main->get_forum_course_restrictions( 1007 ) );

		update_post_meta( $id2, '_llms_bbp_forum_ids', array( '1' ) );
		$this->assertEquals( array( $id2 ), $this->main->get_forum_course_restrictions( 1 ) );

		/**
		 * Edge case check:
		 * We save the values as a serialized array, and before 3.37.11 we used to save them as integers.
		 * Our SQL query to retrieve the courses linked to a certain forum uses a REGEXP to match the forum id in it.
		 * This REGEXP is able to match either ids saved as strings or integers.
		 * We want also to be sure that if we have a value of the type
		 * a:3:{i:0;i:2299;i:1;i:3333;i:2:i:7777;}
		 * and a forum id to check against equal to 1, that value above doesn't match.
		 * This would mean that our query is able differentiate between serialized array item values and indexes.
		 */
		// Case saved as integers.
		update_post_meta( $id, '_llms_bbp_forum_ids', array( 2299, 3333, 7777, 9999, 29999, 109999 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 2299 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 3333 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 7777 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 9999 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 29999 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 109999 ) );


		// Make sure we don't match the array indexes.
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 0 ) );
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 1 ) );
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 2 ) );
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 3 ) );
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 4 ) );
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 5 ) );

		// Case saved as strings.
		update_post_meta( $id, '_llms_bbp_forum_ids', array( '12299', '13333', '17777', '19999', '129999', '1109999' ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 12299 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 13333 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 17777 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 19999 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 129999 ) );
		$this->assertEquals( array( $id ), $this->main->get_forum_course_restrictions( 1109999 ) );

		// Make sure we don't match the array indexes
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 0 ) );
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 1 ) );
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 2 ) );
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 3 ) );
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 4 ) );
		$this->assertNotEquals( array( $id ), $this->main->get_forum_course_restrictions( 5 ) );
	}

	/**
	 * Test is_installed()
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_is_installed() {

		$this->assertTrue( $this->main->is_installed() );

	}

	/**
	 * Test register_shortcodes()
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_register_shortcodes() {

		$this->assertEquals( array( 'LLMS_BBP_Shortcode_Course_Forums_List' ), $this->main->register_shortcodes( array() ) );

	}

	// @todo these tests should be written but I'm tired.
	// public function test_restriction_checks_courses() {}
	// public function test_restriction_checks_memberships() {}

	/**
	 * Test register_engagement_triggers()
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_register_engagement_triggers() {

		$this->assertEquals( array( 'bbp_new_topic', 'bbp_new_reply' ), array_keys( $this->main->register_engagement_triggers( array() ) ) );

	}

	/**
	 * Test save_course_settings() when a quick edit is performed on a course.
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_save_course_settings_quick_edit() {

		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$expect = array( 1, 2, 3 );
		update_post_meta( $id, '_llms_bbp_forum_ids', $expect );

		$this->mockPostRequest( array( 'action' => 'inline-save' ) );
		$this->assertNull( $this->main->save_course_settings( $id ) );

		$this->assertEquals( $expect, get_post_meta( $id, '_llms_bbp_forum_ids', true ) );

	}

	/**
	 * Test save_course_settings() correctly saving strings
	 *
	 * @since 3.38.1
	 *
	 * @return void
	 */
	public function test_save_course_settings_save_strings() {

		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$expect = array( 1, 2, 3 );

		$this->mockPostRequest( array( 'action' => '', '_llms_bbp_forum_ids' => $expect ) );
		$this->main->save_course_settings( $id );

		$this->assertEquals( $expect, array_filter( get_post_meta( $id, '_llms_bbp_forum_ids', true ), 'is_string' ) );

	}

	/**
	 * Test save_course_settings() when there's no new ids passed to the form.
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_save_course_settings_not_set() {

		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		update_post_meta( $id, '_llms_bbp_forum_ids', array( 1 ) );

		$this->assertEquals( array(), $this->main->save_course_settings( $id ) );
		$this->assertEquals( array(), get_post_meta( $id, '_llms_bbp_forum_ids', true ) );

	}

	/**
	 * Test save_course_settiongs()
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_save_course_settings() {

		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$expect = array( 1, 2, 3 );
		$this->mockPostRequest( array( '_llms_bbp_forum_ids' => $expect ) );

		$this->assertEquals( $expect, $this->main->save_course_settings( $id ) );
		$this->assertEquals( $expect, get_post_meta( $id, '_llms_bbp_forum_ids', true ) );

	}

	/**
	 * Test save_course_settings() to delete existing courses.
	 *
	 * @since 3.37.11
	 *
	 * @return void
	 */
	public function test_save_course_settings_delete() {

		$id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		update_post_meta( $id, '_llms_bbp_forum_ids', array( 1 ) );
		$this->mockPostRequest( array( '_llms_bbp_forum_ids' => array() ) );

		$this->assertEquals( array(), $this->main->save_course_settings( $id ) );
		$this->assertEquals( array(), get_post_meta( $id, '_llms_bbp_forum_ids', true ) );

	}

}
