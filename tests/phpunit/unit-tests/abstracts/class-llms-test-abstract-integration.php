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
	 *
	 * @return LLMS_Abstract_Integration
	 */
	private function get_stub() {

		$stub = $this->getMockForAbstractClass( 'LLMS_Abstract_Integration' );

		// Setup variables that would be configured by abstract configure method.
		$stub->title = 'Mock Integration';
		$stub->id = 'mocker';
		$stub->description = 'this is a mock description of the integration';

		return $stub;

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

}
