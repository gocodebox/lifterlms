<?php
/**
 * Tests for the LLMS_Integrations class
 * @group    integrations
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Integrations extends LLMS_UnitTestCase {

	/**
	 * test instance() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_instance() {

		$this->assertTrue( is_a( LLMS()->integrations(), 'LLMS_Integrations' ) );

	}

	public function test_get_integration() {}

	/**
	 * test init() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_init() {

		$instance = LLMS()->integrations();
		$this->assertEquals( 2, count( $instance->integrations() ) );

	}

	/**
	 * Test get available integrations
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_available_integrations() {

		$instance = LLMS()->integrations();
		$this->assertEquals( array(), $instance->get_available_integrations() );

		// enable an integration
		update_option( 'llms_integration_bbpress_enabled', 'yes' );

		// option is enabled but it's not available b/c deps. don't exist
		$this->assertEquals( 0, count( $instance->get_available_integrations() ) );

		// deps exist now
		$stub = $this->getMockBuilder( 'bbPress' )->getMock();
		$this->assertEquals( 1, count( $instance->get_available_integrations() ) );

	}

	/**
	 * Test integrations() method
	 * @return   [type]
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_integrations() {

		$instance = LLMS()->integrations();
		$this->assertEquals( 2, count( $instance->integrations() ) );

	}


}
