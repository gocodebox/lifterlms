<?php
/**
 * Tests for the LLMS_Abstract_Integration class
 * @group    abstracts
 * @group    integrations
 * @since    3.19.0
 * @version  3.19.0
 */
class LLMS_Test_Abstract_Integration extends LLMS_UnitTestCase {

	/**
	 * Retrieve the abstract class mock stub
	 * @return   obj
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	private function get_stub() {

		$stub = $this->getMockForAbstractClass( 'LLMS_Abstract_Integration' );

		// setup variables that would be configured by abstract configure method
		$stub->title = 'Mock Integration';
		$stub->id = 'mocker';
		$stub->description = 'this is a mock description of the integration';

		return $stub;

	}

	/**
	 * test add_settings() method
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function test_add_settings() {

		$stub = $this->get_stub();

		// must be an array
		$this->assertTrue( is_array( $stub->add_settings( array() ) ) );

		// only the default integration settings
		$this->assertEquals( 4, count( $stub->add_settings( array() ) ) );

		// mimic other settings from other integrations
		$this->assertEquals( 10, count( $stub->add_settings( array( 1, 2, 3, 4, 5, 6 ) ) ) );

	}

	/**
	 * Test is_available() method
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function test_is_available() {

		$stub = $this->get_stub();

		// by default it is not available
		$this->assertFalse( $stub->is_available() );

		// enable it
		$stub->set_option( 'enabled', 'yes' );
		$this->assertTrue( $stub->is_available() );

		// explicitly disable it
		$stub->set_option( 'enabled', 'no' );
		$this->assertFalse( $stub->is_available() );

	}

	/**
	 * Test is_enabled() method
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function test_is_enabled() {

		$stub = $this->get_stub();

		// disabled by default (no option found)
		$this->assertFalse( $stub->is_enabled() );

		// enable it
		$stub->set_option( 'enabled', 'yes' );
		$this->assertTrue( $stub->is_enabled() );

		// explicitly disable it
		$stub->set_option( 'enabled', 'no' );
		$this->assertFalse( $stub->is_enabled() );

	}

	/**
	 * test is_installed() method
	 * by default this just returns true, extending classes override it
	 * @return   void
	 * @since    3.19.0
	 * @version  3.19.0
	 */
	public function test_is_installed() {

		$this->assertTrue( $this->get_stub()->is_installed() );

	}

}
