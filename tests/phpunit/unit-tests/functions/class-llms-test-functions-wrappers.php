<?php
/**
 * Tests for the constant wrappers in llms-functions-wrappers.php and their test mocks
 *
 * @package LifterLMS/Tests/Functions
 *
 * @group functions
 * @group functions_wrappers
 *
 * @since [version]
 */
class LLMS_Test_Functions_Wrappers extends LLMS_UnitTestCase {

	/**
	 * Test llms_defined() and llms_constant() fall back to real constants.
	 *
	 * @return void
	 */
	public function test_real_constant() {
		$this->assertTrue( llms_defined( 'ABSPATH' ) );
		$this->assertSame( ABSPATH, llms_constant( 'ABSPATH' ) );
		$this->assertFalse( llms_defined( 'LLMS_TEST_UNDEFINED_CONSTANT' ) );
	}

	/**
	 * Test mocking a constant, including falsy values, and resetting it.
	 *
	 * @return void
	 */
	public function test_mocked_constant() {

		foreach ( array( true, false, null, 0, '' ) as $value ) {
			llms_tests_set_constant( 'LLMS_TEST_MOCK_CONSTANT', $value );
			$this->assertTrue( llms_defined( 'LLMS_TEST_MOCK_CONSTANT' ) );
			$this->assertSame( $value, llms_constant( 'LLMS_TEST_MOCK_CONSTANT' ) );
		}

		llms_tests_reset_constants();
		$this->assertFalse( llms_defined( 'LLMS_TEST_MOCK_CONSTANT' ) );

	}

	/**
	 * Test a mock takes priority over a real constant.
	 *
	 * @return void
	 */
	public function test_mock_overrides_real_constant() {
		llms_tests_set_constant( 'ABSPATH', 'mock' );
		$this->assertSame( 'mock', llms_constant( 'ABSPATH' ) );
	}

}
