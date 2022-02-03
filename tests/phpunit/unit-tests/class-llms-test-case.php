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
	 * Test change_case() where all characters in the string are changed to lowercase.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_lower() {

		$this->assertEquals(
			'madam, in eden, i’m adam',
			LLMS_Case::change( 'Madam, in Eden, I’m Adam', LLMS_Case::LOWER )
		);
	}

	/**
	 * Test change_case() where no characters in the string are changed to lowercase.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_no_change() {

		$this->assertEquals(
			'Al lets Della call Ed “Stella.”',
			LLMS_Case::change( 'Al lets Della call Ed “Stella.”', LLMS_Case::NO_CHANGE )
		);

		// Undefined case constant.
		$this->assertEquals(
			'Al lets Della call Ed “Stella.”',
			LLMS_Case::change( 'Al lets Della call Ed “Stella.”', - 1 )
		);
	}

	/**
	 * Test change_case() where all characters in the string are changed to uppercase.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_upper() {

		$this->assertEquals(
			'YO, BANANA BOY!',
			LLMS_Case::change( 'Yo, banana boy!', LLMS_Case::UPPER )
		);
	}

	/**
	 * Test change_case() where the first character in the string is changed to uppercase.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_upper_first() {

		$this->assertEquals(
			'Taco cat',
			LLMS_Case::change( 'taco cat', LLMS_Case::UPPER_FIRST )
		);
	}

	/**
	 * Test change_case() where the first character of each word in the string is changed to uppercase.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_change_case_upper_words() {

		$this->assertEquals(
			'Was It A Car Or A Cat I Saw?',
			LLMS_Case::change( 'Was it a car or a cat I saw?', LLMS_Case::UPPER_WORDS )
		);
	}
}
