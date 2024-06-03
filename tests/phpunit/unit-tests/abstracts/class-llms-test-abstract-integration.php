<?php
/**
 * Tests for the LLMS_Abstract_Integration class
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group integrations
 *
 * @since 3.19.0
 */
class LLMS_Test_Abstract_Integration extends LLMS_UnitTestCase {

	/**
	 * Retrieve the abstract class mock stub
	 *
	 * @since 3.19.0
	 * @since 4.21.0 Use an anonymous class in favor of a mock abstract.
	 *
	 * @return LLMS_Abstract_Integration
	 */
	private function get_stub() {

		return new class() extends LLMS_Abstract_Integration {

			protected function configure() {
				$this->id          = 'mocker';
				$this->title       = 'Mock Integration';
				$this->description = 'this is a mock description of the integration';
				do_action( 'llms_tests_mock_integration_configured' );
			}

			public $__is_installed = true;
			public function is_installed() {
				return $this->__is_installed;
			}

		};

	}

	/**
	 * Test the constructor.
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_constructor() {

		$stub = $this->get_stub();

		$configure_action = did_action( 'llms_tests_mock_integration_configured' );
		$init_action      = did_action( 'llms_integration_mocker_init' );

		remove_filter( 'lifterlms_integrations_settings_mocker', array( $stub, 'add_settings' ), 20 );

		LLMS_Unit_Test_Util::set_private_property( $stub, 'plugin_basename', 'mockerpluginbasename' );

		$stub->__construct();

		// Actions ran.
		$this->assertEquals( ++$configure_action, did_action( 'llms_tests_mock_integration_configured' ) );
		$this->assertEquals( ++$init_action, did_action( 'llms_integration_mocker_init' ) );

		// Filter added.
		$this->assertEquals( 20, has_filter( 'lifterlms_integrations_settings_mocker', array( $stub, 'add_settings' ) ) );

		// Plugin actions link added.
		$this->assertEquals( 100, has_action( 'plugin_action_links_mockerpluginbasename', array( $stub, 'plugin_action_links' ) ) );


	}

	/**
	 * Test add_settings() method
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_add_settings() {

		$stub = $this->get_stub();

		// Must be an array.
		$this->assertTrue( is_array( $stub->add_settings( array() ) ) );

		// Only the default integration settings.
		$this->assertEquals( 4, count( $stub->add_settings( array() ) ) );

		// Mimic other settings from other integrations.
		$this->assertEquals( 10, count( $stub->add_settings( array( 1, 2, 3, 4, 5, 6 ) ) ) );

	}

	/**
	 * Test the get_option() method v1 behavior
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_get_option_v1() {

		$stub = $this->get_stub();
		$this->assertEquals( '', $stub->get_option( 'enabled' ) );
		$this->assertEquals( 'yes', $stub->get_option( 'enabled', 'yes' ) );
		$this->assertEquals( 'no', $stub->get_option( 'enabled', 'no' ) );
		$this->assertEquals( 'fake', $stub->get_option( 'enabled', 'fake' ) );

		$stub->set_option( 'enabled', 'yes' );
		$this->assertEquals( 'yes', $stub->get_option( 'enabled' ) );
		$this->assertEquals( 'yes', $stub->get_option( 'enabled', 'yes' ) );
		$this->assertEquals( 'yes', $stub->get_option( 'enabled', 'no' ) );
		$this->assertEquals( 'yes', $stub->get_option( 'enabled', 'fake' ) );

	}

	/**
	 * Test the get_option() method v2 behavior
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_get_option_v2() {

		$stub = $this->get_stub();
		LLMS_Unit_Test_Util::set_private_property( $stub, 'version', 2 );

		$this->assertEquals( 'no', $stub->get_option( 'enabled' ) );

		// Don't autoload the default value when a default value is passed.
		$this->assertEquals( 'yes', $stub->get_option( 'enabled', 'yes' ) );
		$this->assertEquals( 'no', $stub->get_option( 'enabled', 'no' ) );
		$this->assertEquals( 'fake', $stub->get_option( 'enabled', 'fake' ) );

		$stub->set_option( 'enabled', 'yes' );
		$this->assertEquals( 'yes', $stub->get_option( 'enabled' ) );
		$this->assertEquals( 'yes', $stub->get_option( 'enabled', 'yes' ) );
		$this->assertEquals( 'yes', $stub->get_option( 'enabled', 'no' ) );
		$this->assertEquals( 'yes', $stub->get_option( 'enabled', 'fake' ) );

	}

	/**
	 * Directly test the get_option_default_value() method.
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_get_option_default_value() {

		$stub = $this->get_stub();

		$this->assertEquals( 'no', $stub->get_option_default_value( '', $stub->get_option_name( 'enabled' ), false ) );
		$this->assertEquals( 'no', $stub->get_option_default_value( 'yes', $stub->get_option_name( 'enabled' ), false ) );

		// Default value explicitly passed.
		$this->assertEquals( 'yes', $stub->get_option_default_value( 'yes', $stub->get_option_name( 'enabled' ), true ) );

	}


	/**
	 * Test the get_priority() method
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_get_priority() {

		$stub = $this->get_stub();

		// Default.
		$this->assertEquals( 20, $stub->get_priority() );

		// Redefined.
		LLMS_Unit_Test_Util::set_private_property( $stub, 'priority', 50 );
		$this->assertEquals( 50, $stub->get_priority() );

	}

	/**
	 * Test get_settings()
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_get_settings() {

		$stub = $this->get_stub();

		$settings = LLMS_Unit_Test_Util::call_method( $stub, 'get_settings' );

		$expected = array(
			'llms_integration_mocker_start',
			'llms_integration_mocker_title',
			'llms_integration_mocker_enabled',
			'llms_integration_mocker_end',
		);
		$this->assertEquals( $expected, wp_list_pluck( $settings, 'id' ) );

	}

	/**
	 * Test get_settings() when missing requirements.
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_get_settings_not_installed() {

		$stub = $this->get_stub();
		$stub->__is_installed = false;

		LLMS_Unit_Test_Util::set_private_property( $stub, 'description_missing', 'Missing requirements' );

		$settings = LLMS_Unit_Test_Util::call_method( $stub, 'get_settings' );

		$expected = array(
			'llms_integration_mocker_start',
			'llms_integration_mocker_title',
			'llms_integration_mocker_enabled',
			'llms_integration_mocker_missing_requirements_desc',
			'llms_integration_mocker_end',
		);
		$this->assertEquals( $expected, wp_list_pluck( $settings, 'id' ) );

	}

	/**
	 * Test is_available() method
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_is_available() {

		$stub = $this->get_stub();

		// By default it is not available.
		$this->assertFalse( $stub->is_available() );

		// Enable it.
		$stub->set_option( 'enabled', 'yes' );
		$this->assertTrue( $stub->is_available() );

		// Explicitly disable it.
		$stub->set_option( 'enabled', 'no' );
		$this->assertFalse( $stub->is_available() );

	}

	/**
	 * Test is_enabled() method
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_is_enabled() {

		$stub = $this->get_stub();

		// Disabled by default (no option found).
		$this->assertFalse( $stub->is_enabled() );

		// Enable it.
		$stub->set_option( 'enabled', 'yes' );
		$this->assertTrue( $stub->is_enabled() );

		// Explicitly disable it.
		$stub->set_option( 'enabled', 'no' );
		$this->assertFalse( $stub->is_enabled() );

	}

	/**
	 * Test is_installed() method
	 *
	 * By default this just returns true, extending classes override it
	 *
	 * @since 3.19.0
	 *
	 * @return void
	 */
	public function test_is_installed() {

		$this->assertTrue( $this->get_stub()->is_installed() );

	}

	/**
	 * Test plugin_action_links()
	 *
	 * @since 4.21.0
	 *
	 * @return void
	 */
	public function test_plugin_action_links() {

		$mock_links    = array( '<a href="#">FAKE</a>' );
		$expected_link = array( '<a href="http://example.org/wp-admin/admin.php?page=llms-settings&#038;tab=integrations&#038;section=mocker">Settings</a>' );
		$stub          = $this->get_stub();

		$this->assertEquals( array_merge( $mock_links, $expected_link ), $stub->plugin_action_links( $mock_links, 'mock', array(), 'all' ) );

	}

}
