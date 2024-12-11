<?php
/**
 * Tests for LifterLMS Main Class
 *
 * @package LifterLMS/Tests
 *
 * @group main_class
 *
 * @since 3.3.1
 * @since 3.21.1 Add localization tests.
 * @since 4.0.0 Add tests for `init_session()` method.
 *               Remove tests against removed LLMS_SVG_DIR constant.
 * @since 4.4.0 Add tests for `init_assets()` method.
 */
class LLMS_Test_Main_Class extends LLMS_UnitTestCase {

	/**
	 * Setup function
	 *
	 * @since 3.3.1
	 * @since 5.3.3 Use `llms()` in favor of `LLMS()` and renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->llms = llms();
	}

	/**
	 * Test the `instance` property.
	 *
	 * @since 3.3.1
	 * @since 5.3.0 Rename `_instance` property to `instance`.
	 *
	 * @return void
	 */
	public function test_llms_instance() {

		$this->assertClassHasStaticAttribute( 'instance', 'LifterLMS' );

	}

	/**
	 * Test class constants
	 *
	 * @since 3.3.1
	 * @since 4.0.0 Remove tests against removed LLMS_SVG_DIR constant.
	 *
	 * @return void
	 */
	public function test_constants() {

		$this->assertEquals( $this->llms->version, LLMS_VERSION );
		$this->assertNotEquals( LLMS_LOG_DIR, '' );
		$this->assertNotEquals( LLMS_PLUGIN_DIR, '' );
		$this->assertNotEquals( LLMS_PLUGIN_FILE, '' );
		$this->assertNotEquals( LLMS_TEMPLATE_PATH, '' );

	}

	/**
	 * Test main instances
	 *
	 * @since 3.3.1
	 * @since 5.8.0 Added tests for additional instances.
	 *
	 * @return void
	 */
	public function test_instances() {

		$tests = array(
			array( 'LLMS_Achievements', 'achievements' ),
			array( 'LLMS_Block_Templates', 'block_templates' ),
			array( 'LLMS_Certificates', 'certificates' ),
			array( 'LLMS_Engagements', 'engagements' ),
			array( 'LLMS_Events', 'events' ),
			array( 'LLMS_Grades', 'grades' ),
			array( 'LLMS_Integrations', 'integrations' ),
			array( 'LLMS_Emails', 'mailer' ),
			array( 'LLMS_Notifications', 'notifications' ),
			array( 'LLMS_Payment_Gateways', 'payment_gateways' ),
			array( 'LLMS_Processors', 'processors' ),
		);

		foreach ( $tests as $test ) {

			list( $expected_class, $func ) = $test;
			$this->assertInstanceOf( $expected_class, $this->llms->$func() );

		}

	}

	/**
	 * Test the init_assets() method.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function test_init_assets() {

		$assets = LLMS_Unit_Test_Util::call_method( llms(), 'init_assets' );

		$this->assertEquals( $assets, llms()->assets );

		$this->assertEquals( require LLMS_PLUGIN_DIR . 'includes/assets/llms-assets-scripts.php', LLMS_Unit_Test_Util::get_private_property_value( $assets, 'scripts' ) );
		$this->assertEquals( require LLMS_PLUGIN_DIR . 'includes/assets/llms-assets-styles.php', LLMS_Unit_Test_Util::get_private_property_value( $assets, 'styles' ) );

	}

	/**
	 * Test the init_session() method
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public function test_init_session() {

		// Clear the session.
		llms()->session = null;

		// Initializes a new session.
		$session = llms()->init_session();
		$this->assertTrue( is_a( $session, 'LLMS_Session' ) );
		$session->set( 'test', 'mock' );

		// Call it again, should respond with the same session as before.
		$this->assertEquals( $session->get_id(), llms()->init_session()->get_id() );
		$this->assertEquals( 'mock', llms()->init_session()->get( 'test' ) );

	}

}
