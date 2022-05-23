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
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->main = $this->getMockForAbstractClass( 'LLMS_Payment_Gateway' );
		$this->main->id = 'cash-now';

		// Clean logs.
		foreach ( glob( LLMS_LOG_DIR . 'cash-now-*.log*' ) as $file ) {
			unlink( $file );
		}

	}

	/**
	 * Mock callback method used to add a secure option to the mock gateway's settings.
	 *
	 * @since 6.4.0
	 *
	 * @param array[] $settings Existing settings array
	 * @return array[]
	 */
	public function add_admin_settings( $settings ) {

		$settings[] = array(
			'id'            => $this->main->get_option_name( 'secure_key' ),
			'secure_option' => 'LLMS_CASH_NOW_SECURE_KEY',
		);

		return $settings;
	}

	/**
	 * Test get_secure_strings().
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function test_get_secure_strings() {

		$strings = array( 'abcdefg' );

		// No secure options defined.
		$this->assertEquals( $strings, $this->main->get_secure_strings( $strings, 'cash-now' ) );

		add_filter( 'llms_get_gateway_settings_fields', array( $this, 'add_admin_settings' ), 10 );
		
		// Has an option but it isn't defined.
		$this->assertEquals( $strings, $this->main->get_secure_strings( $strings, 'cash-now' ) );

		// Has a defined option.
		$this->main->set_option( 'secure_key', 'MY-KEY' );
		$this->assertEquals( 
			array( 'abcdefg', 'MY-KEY' ),
			$this->main->get_secure_strings( $strings, 'cash-now' )
		);

		// Another log file.
		$this->assertEquals( $strings, $this->main->get_secure_strings( $strings, 'llms' ) );

		remove_filter( 'llms_get_gateway_settings_fields', array( $this, 'add_admin_settings' ), 10 );

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

	/**
	 * Test log().
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function test_log() {

		$log_path = llms_get_log_path( 'cash-now' );

		// Disabled.
		$this->main->set_option( 'logging_enabled', 'no' );
		$this->main->log( 'Test log' );

		// Nothing logged because it's disabled.
		$this->assertFalse( file_exists( $log_path ) );

		// Logging enabled.
		$this->main->set_option( 'logging_enabled', 'yes' );
		$this->main->log( 'Test log' );

		$logs = explode( ' - ', file_get_contents( llms_get_log_path( 'cash-now' ) ) );
		$this->assertTrue( date_create( $logs[0] ) instanceof DateTime );
		$this->assertEquals( 'Test log', trim( $logs[1] ) );

	}

	/**
	 * Test log() masks secure strings.
	 *
	 * @since 6.4.0
	 *
	 * @return void
	 */
	public function test_log_secure_strings() {

		$this->main->set_option( 'logging_enabled', 'yes' );
		
		add_filter( 'llms_get_gateway_settings_fields', array( $this, 'add_admin_settings' ), 10 );

		$key = 'F@K3-$3CUR3-K3Y!';
		$this->main->set_option( 'secure_key', $key );

		$this->main->log( array(
			'headers' => array(
				'Authorization' => "Basic {$key}:password",
			),
		) );

		$logs = explode( ' - ', file_get_contents( llms_get_log_path( 'cash-now' ) ) );

		$this->assertTrue( date_create( $logs[0] ) instanceof DateTime );
		$this->assertEquals( 'Array
(
    [headers] => Array
        (
            [Authorization] => Basic F@************Y!:password
        )

)', trim( $logs[1] ) );

		remove_filter( 'llms_get_gateway_settings_fields', array( $this, 'add_admin_settings' ), 10 );

	}

}
