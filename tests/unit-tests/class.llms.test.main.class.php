<?php
/**
 * Tests for LifterLMS Main Class
 * @since   3.3.1
 * @version [version]
 */
class LLMS_Test_Main_Class extends LLMS_UnitTestCase {

	/**
	 * Setup function
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function setUp() {
		parent::setUp();
		$this->llms = LLMS();
	}

	/**
	 * test the _instance variable
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_llms_instance() {

		$this->assertClassHasStaticAttribute( '_instance', 'LifterLMS' );

	}

	/**
	 * Test class constants
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
	 */
	public function test_constants() {

		$this->assertEquals( $this->llms->version, LLMS_VERSION );
		$this->assertNotEquals( LLMS_LOG_DIR, '' );
		$this->assertNotEquals( LLMS_SVG_DIR, '' );
		$this->assertNotEquals( LLMS_PLUGIN_DIR, '' );
		$this->assertNotEquals( LLMS_PLUGIN_FILE, '' );
		$this->assertNotEquals( LLMS_TEMPLATE_PATH, '' );

	}

	/**
	 * Test main instants
	 * @return   void
	 * @since    3.3.1
	 * @version  3.3.1
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
	 * Test plugin localization
	 * @return   void
	 * @since    [version]
	 * @version  [version]
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
