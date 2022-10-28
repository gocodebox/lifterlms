<?php
/**
 * Tests for the LLMS_Abstract_Enum class
 *
 * @package LifterLMS/Tests/Abstracts
 *
 * @group abstracts
 * @group enum
 *
 * @since [version]
 */
class LLMS_Test_Abstract_Enum extends LLMS_UnitTestCase {

	/**
	 * Tests {@see LLMS_Test_Abstract_Enum::cases}.
	 *
	 * @since [version]
	 */
	public function test_cases() {

		$cases = LLMS_Enum_Mock::cases();

		$this->assertTrue( is_array( $cases ) );

		$this->assertSame( LLMS_Enum_Mock::MOCK_CASE_A, $cases['MOCK_CASE_A'] );
		$this->assertSame( LLMS_Enum_Mock::MOCK_CASE_B, $cases['MOCK_CASE_B'] );
		$this->assertSame( LLMS_Enum_Mock::MOCK_CASE_C, $cases['MOCK_CASE_C'] );
		$this->assertSame( LLMS_Enum_Mock::MOCK_CASE_INT, $cases['MOCK_CASE_INT'] );
		$this->assertSame( LLMS_Enum_Mock::MOCK_CASE_BOOL, $cases['MOCK_CASE_BOOL'] );



	}

}
