<?php
/**
 * Tests for {@see LLMS_Case}.
 *
 * @package LifterLMS/Tests
 *
 * @group case
 *
 * @since [version]
 */
class LLMS_Test_Case extends LLMS_UnitTestCase {

	/**
	 * Changes the case of a string using either multibyte or non-multibyte methods.
	 *
	 * @since [version]
	 *
	 * @param string $string       The string to change the case of.
	 * @param string $case         One of the CASE_ constants from {@see LLMS_Case}.
	 * @param bool   $is_multibyte If true, change the case using multibyte functions,
	 *                             else use non-multibyte functions.
	 * @return string
	 */
	private function change( $string, $case, $is_multibyte ) {

		$method = $is_multibyte ? 'change_with_multibyte' : 'change_without_multibyte';

		try {
			$string = LLMS_Unit_Test_Util::call_method( LLMS_Case::class , $method, array( $string, $case ) );
		} catch ( Exception $exception ) {
			$string = $exception->getMessage();
		}

		return $string;
	}

	/**
	 * Test change_case() where all characters in the string are changed to lowercase.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_lower() {

		$string   = 'Madam, in Eden, I’m Adam';
		$expected = 'madam, in eden, i’m adam';
		$this->assertEquals( $expected, $this->change( $string, LLMS_Case::LOWER, false ) );
		$this->assertEquals( $expected, $this->change( $string, LLMS_Case::LOWER, true ) );
	}

	/**
	 * Test change_case() where no characters in the string are changed.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_no_change() {

		$string   = 'Al lets Della call Ed “Stella.”';
		$expected = 'Al lets Della call Ed “Stella.”';
		$this->assertEquals( $expected, $this->change( $string, LLMS_Case::NO_CHANGE, false ) );
		$this->assertEquals( $expected, $this->change( $string, LLMS_Case::NO_CHANGE, true ) );
	}

	/**
	 * Test change_case() with an undefined case type.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_undefined() {

		$string   = 'Amore, Roma.';
		$expected = 'Amore, Roma.';
		$this->assertEquals( $expected, $this->change( $string, - 1, false ) );
		$this->assertEquals( $expected, $this->change( $string, - 1, true ) );
	}

	/**
	 * Test change_case() where all characters in the string are changed to uppercase.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_upper() {

		$string   = 'Yo, banana boy!';
		$expected = 'YO, BANANA BOY!';
		$this->assertEquals( $expected, $this->change( $string, LLMS_Case::UPPER, false ) );
		$this->assertEquals( $expected, $this->change( $string, LLMS_Case::UPPER, true ) );
	}

	/**
	 * Test change_case() where the first character in the string is changed to uppercase.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_upper_first() {

		$string   = 'taco cat';
		$expected = 'Taco cat';
		$this->assertEquals( $expected, $this->change( $string, LLMS_Case::UPPER_FIRST, false ) );
		$this->assertEquals( $expected, $this->change( $string, LLMS_Case::UPPER_FIRST, true ) );
	}

	/**
	 * Test change_case() where the first character of each word in the string is changed to uppercase.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_upper_words() {

		$string   = 'Was it a car or a cat I saw?';
		$expected = 'Was It A Car Or A Cat I Saw?';
		$this->assertEquals( $expected, $this->change( $string, LLMS_Case::UPPER_WORDS, false ) );
		$this->assertEquals( $expected, $this->change( $string, LLMS_Case::UPPER_WORDS, true ) );
	}
}
