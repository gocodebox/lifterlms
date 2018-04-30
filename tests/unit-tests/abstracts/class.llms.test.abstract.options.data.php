<?php
/**
 * Tests for the LLMS_Abstract_Integration class
 * @group    abstracts
 * @group    options
 * @group    settings
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Abstract_Options_Data extends LLMS_UnitTestCase {

	/**
	 * Retrieve the abstract class mock stub
	 * @return   obj
	 * @since    [version]
	 * @version  [version]
	 */
	private function get_stub() {

		return $this->getMockForAbstractClass( 'LLMS_Abstract_Options_Data' );

	}

	/**
	 * test get_option() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_option() {

		$stub = $this->get_stub();

		// default value
		$this->assertEquals( '', $stub->get_option( 'mock_option' ) );
		$this->assertEquals( 'mockvalue', $stub->get_option( 'mock_option', 'mockvalue' ) );

		update_option( 'llms_mock_option', 'mockvalue' );

		$this->assertEquals( 'mockvalue', $stub->get_option( 'mock_option' ) );
		$this->assertEquals( 'mockvalue', $stub->get_option( 'mock_option', 'anothermockvalue' ) );

	}

	/**
	 * test get_option_name() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_option_name() {

		$stub = $this->get_stub();

		$this->assertEquals( 'llms_mock_option', $stub->get_option_name( 'mock_option' ) );

		// change the option prefix as an extending class might via overriding the `get_option_prefix()` method
		$reflection = new ReflectionClass( $this->get_stub() );
		$prop = $reflection->getProperty( 'option_prefix' );
		$prop->setAccessible( true );
		$prop->setValue( $stub, 'llms_extended_' );

		$this->assertEquals( 'llms_extended_mock_option', $stub->get_option_name( 'mock_option' ) );

	}

	/**
	 * test set_option() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_set_option() {

		delete_option( 'llms_mock_option' );
		$this->assertEquals( true, $this->get_stub()->set_option( 'mock_option', 'mockvalue' ) );
		$this->assertEquals( 'mockvalue', get_option( 'llms_mock_option', 'mockvalue' ) );

	}

}
