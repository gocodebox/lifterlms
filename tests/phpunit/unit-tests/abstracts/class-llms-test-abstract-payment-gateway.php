<?php
/**
 * Tests for the LLMS_Payment_Gateway abstract
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group payment_gateway
 *
 * @since 5.3.0
 */
class LLMS_Test_Payment_Gateway extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->main = $this->getMockForAbstractClass( 'LLMS_Payment_Gateway' );
		$this->main->id = 'cash-now';

	}

	/**
	 * Test get_option_name()
	 *
	 * Tests options-related methods:
	 *   + get_option()
	 *   + get_option_default_value()
	 *   + get_option_prefix()
	 *   + get_option_name()
	 *   + and set_option()
	 *
	 * @since 5.3.0
	 *
	 * @return void
	 */
	public function test_option_methods() {

		$expected_name = 'llms_gateway_cash-now_title';
		$secure_key    = 'LLMS_GATEWAY_CASH_NOW_TITLE';
		$expected_val  = 'Cash Now';
		$this->assertEquals( $expected_name, $this->main->get_option_name( 'title' ) );

		// Empty.
		$this->assertEquals( '', $this->main->get_option( 'title') );

		// Default value.
		$this->main->title = 'Currency Immediately';
		$this->assertEquals( 'Currency Immediately', $this->main->get_option( 'title') );

		// Set the title via WP core methods.
		update_option( $expected_name, $expected_val );

		$this->assertEquals( $expected_val, $this->main->get_option( 'title' ) );

		// Secure not defined, fallsback with the default value.
		$this->assertEquals( $expected_val, $this->main->get_option( 'title', $secure_key ) );

		// Change the value via setter.
		$this->main->set_option( 'title', 'Money Later' );
		$this->assertEquals( 'Money Later', $this->main->get_option( 'title' ) );

		// Secure value defined.
		define( $secure_key, 'Bucks Yesterday' );
		$this->assertEquals( 'Bucks Yesterday', $this->main->get_option( 'title', $secure_key ) );

	}

}
