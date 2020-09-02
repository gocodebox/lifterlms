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
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->llms = LLMS();
	}

	/**
	 * test the _instance variable
	 *
	 * @since 3.3.1
	 *
	 * @return void
	 */
	public function test_llms_instance() {

		$this->assertClassHasStaticAttribute( '_instance', 'LifterLMS' );

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
	 *
	 * @return void
	 */
	public function test_instances() {

		$this->assertInstanceOf( 'LLMS_Payment_Gateways', $this->llms->payment_gateways() );
		$this->assertInstanceOf( 'LLMS_Emails', $this->llms->mailer() );
		$this->assertInstanceOf( 'LLMS_Integrations', $this->llms->integrations() );
		$this->assertInstanceOf( 'LLMS_Engagements', $this->llms->engagements() );
		$this->assertInstanceOf( 'LLMS_Certificates', $this->llms->certificates() );
		$this->assertInstanceOf( 'LLMS_Achievements', $this->llms->achievements() );

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
		LLMS()->session = null;

		// Initializes a new session.
		$session = LLMS()->init_session();
		$this->assertTrue( is_a( $session, 'LLMS_Session' ) );
		$session->set( 'test', 'mock' );

		// Call it again, should respond with the same session as before.
		$this->assertEquals( $session->get_id(), LLMS()->init_session()->get_id() );
		$this->assertEquals( 'mock', LLMS()->init_session()->get( 'test' ) );

	}

	/**
	 * Test plugin localization
	 *
	 * @since 3.21.1
	 *
	 * @return void
	 */
	public function test_localize() {

		/**
		 * custom-lifterlms-en_US.po/mo
		 * Original  | Translation
		 * -----------------------
		 * LifterLMS | BetterLMS
		 * Course    | Module
		 */

		/**
		 * lifterlms-en_US.po/mo
		 * Original  | Translation
		 * -----------------------
		 * LifterLMS | MyLMS
		 * Settings  | Options
		 */


		/**
		 * Default order during initialization
		 * Custom safe location
		 * Default location (from community)
		 */

		// this is translated in both but should use the translation from the custom file
		$this->assertEquals( 'BetterLMS', __( 'LifterLMS', 'lifterlms' ) );

		// translated in only the custom file
		$this->assertEquals( 'Module', __( 'Course', 'lifterlms' ) );

		// translated only in the default file
		$this->assertEquals( 'Options', __( 'Settings', 'lifterlms' ) );

		// not translated in either
		$this->assertEquals( 'Lesson', __( 'Lesson', 'lifterlms' ) );

		// fake string
		$this->assertEquals( 'arstienarstyularst', __( 'arstienarstyularst', 'lifterlms' ) );

	}

}
