<?php
/**
 * Test Option functions
 *
 * @package  LifterLMS/Tests/Functions
 * @since    3.29.0
 * @version  3.29.0
 */
class LLMS_Test_Functions_Options extends LLMS_UnitTestCase {

	/**
	 * test the get_secure_var method
	 *
	 * @return  void
	 * @since   3.29.0
	 * @version 3.29.0
	 */
	public function test_llms_get_secure_option() {

		$val = 'F4K3_ApI-K3Y$!';

		// nothing set.
		$this->assertFalse( llms_get_secure_option( 'LLMS_MOCK_SECURE_VAR' ) );
		// fallback to something else.
		$this->assertEquals( '', llms_get_secure_option( 'LLMS_MOCK_SECURE_VAR', '' ) );
		// fallback to actual val.
		$this->assertEquals( $val, llms_get_secure_option( 'LLMS_MOCK_SECURE_VAR', $val ) );
		// fallback with db call.
		$this->assertEquals( $val, llms_get_secure_option( 'LLMS_MOCK_SECURE_VAR', $val, 'llms_mock_secure_option' ) );
		// no fallback with db call.
		$this->assertFalse( llms_get_secure_option( 'LLMS_MOCK_SECURE_VAR', false, 'llms_mock_secure_option' ) );

		// add the option.
		update_option( 'llms_mock_secure_option', $val );
		$this->assertEquals( $val, llms_get_secure_option( 'LLMS_MOCK_SECURE_VAR', false, 'llms_mock_secure_option' ) );

		// use constant variable.
		define( 'LLMS_MOCK_SECURE_VAR', 'arstarstarst' );
		$this->assertEquals( 'arstarstarst', llms_get_secure_option( 'LLMS_MOCK_SECURE_VAR', false, 'llms_mock_secure_option' ) );

		// use environment var.
		putenv( 'LLMS_MOCK_SECURE_VAR=a90rst0-98arst' );
		$this->assertEquals( 'a90rst0-98arst', llms_get_secure_option( 'LLMS_MOCK_SECURE_VAR', false, 'llms_mock_secure_option' ) );

	}

	/**
	 * Tests the llms_is_option_secure() function.
	 *
	 * @since 7.0.0
	 *
	 * @return void
	 */
	public function test_llms_is_option_secure() {

		// Empty name.
		$this->assertFalse( llms_is_option_secure( '' ) );

		// Environment variable.
		putenv( 'FOO=BAR' );
		$this->assertTrue( llms_is_option_secure( 'FOO' ) );

		// Unset environment variable.
		putenv( 'FOO' );
		$this->assertFalse( llms_is_option_secure( 'FOO' ) );

		// Undefined constant.
		$this->assertFalse( llms_is_option_secure( 'F00D' ) );

		// Defined constant.
		define( 'F00D', 'BAD' );
		$this->assertTrue( llms_is_option_secure( 'F00D' ) );
	}
}
