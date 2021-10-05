<?php
/**
 * Tests for the LLMS_Payment_Gateways class
 *
 * @group payment_gateways
 *
 * @since 3.10.0
 */
class LLMS_Test_Payment_Gateways extends LLMS_UnitTestCase {

	/**
	 * Enable or disable a payment gateway by ID
	 *
	 * @since 3.10.0
	 * @since 5.3.3 Use `llms()` in favor of deprecated `LLMS()`.
	 *
	 * @param string $id      Gateway id.
	 * @param string $enabled Whether the gateway should be enabled or disabled. Accepts on or off.
	 * @return void
	 */
	private function toggle_gateway( $id, $enabled = 'on' ) {

		$enabled = 'on' === $enabled ? 'yes' : 'no';

		$manual = llms()->payment_gateways()->get_gateway_by_id( 'manual' );
		update_option( $manual->get_option_name( 'enabled' ), $enabled );

	}

	/**
	 * Test get_enabled_payment_gateways function
	 *
	 * @since 3.10.0
	 * @since 5.3.3 Use `llms()` in favor of deprecated `LLMS()`.
	 *
	 * @return void
	 */
	public function test_get_enabled_payment_gateways() {

		$gways = llms()->payment_gateways();

		$this->toggle_gateway( 'manual', 'off' );

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
	 *
	 * @since 3.10.0
	 * @since 5.3.3 Use `llms()` in favor of deprecated `LLMS()`.
	 *
	 * @return void
	 */
	public function test_get_default_gateway() {

		// enable the manual gateway
		$this->toggle_gateway( 'manual', 'on' );
		$this->assertEquals( 'manual', llms()->payment_gateways()->get_default_gateway() );

	}

	/**
	 * Test get_payment_gateways() method
	 *
	 * @since 3.10.0
	 * @since 5.3.3 Use `llms()` in favor of deprecated `LLMS()`.
	 *
	 * @return void
	 */
	public function test_get_payment_gateways() {

		$gways = llms()->payment_gateways();

		$this->assertTrue( is_array( $gways->get_payment_gateways() ) );
		$this->assertTrue( array_key_exists( 'manual', $gways->get_payment_gateways() ) );
		$this->assertEquals( 1, count( $gways->get_payment_gateways() ) );

	}

	/**
	 * Test has_gateways() method
	 *
	 * @since 3.10.0
	 * @since 5.3.3 Use `llms()` in favor of deprecated `LLMS()`.
	 *
	 * @return void
	 */
	public function test_has_gateways() {

		$gways = llms()->payment_gateways();

		// check all gateways (default)
		$this->assertTrue( $gways->has_gateways() );
		// check all gateways passing false
		$this->assertTrue( $gways->has_gateways( false ) );

		// check enabled
		$this->toggle_gateway( 'manual', 'off' );
		$this->assertFalse( $gways->has_gateways( true ) );

		$this->toggle_gateway( 'manual', 'on' );
		$this->assertTrue( $gways->has_gateways( true ) );

	}

	/**
	 * Test get_gateway_by_id()
	 *
	 * @since 3.10.0
	 * @since 5.3.3 Use `llms()` in favor of deprecated `LLMS()`.
	 *
	 * @return void
	 */
	public function test_get_gateway_by_id() {

		$gways = llms()->payment_gateways();
		$manual = $gways->get_gateway_by_id( 'manual' );
		$this->assertTrue( is_a( $manual, 'LLMS_Payment_Gateway' ) );
		$this->assertEquals( 'manual', $manual->get_id() );

		$this->assertFalse( $gways->get_gateway_by_id( 'fake_gway' ) );

	}

}
