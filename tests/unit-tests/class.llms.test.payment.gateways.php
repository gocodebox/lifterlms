<?php
/**
 * Tests for the LLMS_Payment_Gateways class
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Payment_Gateways extends LLMS_UnitTestCase {

	/**
	 * Enable or disable a payment gateway by ID
	 * @param    string     $id       gateway id
	 * @param    string     $enabled  on|off
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	private function toggle_gateway( $id, $enabled = 'on' ) {

		$enabled = 'on' === $enabled ? 'yes' : 'no';

		$manual = LLMS()->payment_gateways()->get_gateway_by_id( 'manual' );
		update_option( $manual->get_option_name( 'enabled' ), $enabled );

	}

	/**
	 * Test get_enabled_payment_gateways function
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_enabled_payment_gateways() {

		$gways = LLMS()->payment_gateways();

		$this->assertEquals( array(), $gways->get_enabled_payment_gateways() );

		// enable the manual gateway
		$this->toggle_gateway( 'manual', 'on' );

		// gateway should exist in the array
		$this->assertTrue( is_array( $gways->get_enabled_payment_gateways() ) );
		$this->assertTrue( array_key_exists( 'manual', $gways->get_enabled_payment_gateways() ) );
		$this->assertEquals( 1, count( $gways->get_enabled_payment_gateways() ) );

	}

	/**
	 * Test get_default_gateway() function
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_default_gateway() {

		// enable the manual gateway
		$this->toggle_gateway( 'manual', 'on' );
		$this->assertEquals( 'manual', LLMS()->payment_gateways()->get_default_gateway() );

	}

	/**
	 * Test get_payment_gateways() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_payment_gateways() {

		$gways = LLMS()->payment_gateways();

		$this->assertTrue( is_array( $gways->get_payment_gateways() ) );
		$this->assertTrue( array_key_exists( 'manual', $gways->get_payment_gateways() ) );
		$this->assertEquals( 1, count( $gways->get_payment_gateways() ) );

	}

	/**
	 * Test has_gateways() method
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_has_gateways() {

		$gways = LLMS()->payment_gateways();

		// check all gateways (default)
		$this->assertTrue( $gways->has_gateways() );
		// check all gateways passing false
		$this->assertTrue( $gways->has_gateways( false ) );

		// check enabled
		$this->assertFalse( $gways->has_gateways( true ) );

		$this->toggle_gateway( 'manual', 'on' );
		$this->assertTrue( $gways->has_gateways( true ) );

	}

	/**
	 * Test get_gateway_by_id()
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function test_get_gateway_by_id() {

		$gways = LLMS()->payment_gateways();
		$manual = $gways->get_gateway_by_id( 'manual' );
		$this->assertTrue( is_a( $manual, 'LLMS_Payment_Gateway' ) );
		$this->assertEquals( 'manual', $manual->get_id() );

		$this->assertFalse( $gways->get_gateway_by_id( 'fake_gway' ) );

	}

}
