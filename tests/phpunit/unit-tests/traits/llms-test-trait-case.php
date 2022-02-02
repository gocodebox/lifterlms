<?php
/**
 * Tests for {@see LLMS_Trait_Case}.
 *
 * @group Traits
 *
 * @since [version]
 */
class LLMS_Test_Trait_Case extends LLMS_UnitTestCase {

	/**
	 * @var LLMS_Trait_Case
	 */
	protected $mock;

	/**
	 * Changes the case of a string.
	 *
	 * @since [version]
	 *
	 * @param string $string The string to change the case of.
	 * @param string $case   One of the CASE_ constants from {@see LLMS_Interface_Case}.
	 * @return string
	 */
	private function call_change_case( $string, $case ) {

		try {
			return LLMS_Unit_Test_Util::call_method( $this->mock, 'change_case', array( $string, $case ) );
		} catch ( Exception $exception ) {
			return $exception->getMessage();
		}
	}

	/**
	 * Setup before running each test in this class.
	 *
	 * @since [version]
	 */
	public function set_up() {

		parent::set_up();

		$this->mock = new class() implements LLMS_Interface_Case {

			use LLMS_Trait_Case;
		};
	}

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
			$this->call_change_case( 'Madam, in Eden, I’m Adam', LLMS_Interface_Case::CASE_LOWER )
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
			$this->call_change_case( 'Al lets Della call Ed “Stella.”', LLMS_Interface_Case::CASE_NO_CHANGE )
		);

		// Undefined case constant.
		$this->assertEquals(
			'Al lets Della call Ed “Stella.”',
			$this->call_change_case( 'Al lets Della call Ed “Stella.”', - 1 )
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
			$this->call_change_case( 'Yo, banana boy!', LLMS_Interface_Case::CASE_UPPER )
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
			$this->call_change_case( 'taco cat', LLMS_Interface_Case::CASE_UPPER_FIRST )
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
			$this->call_change_case( 'Was it a car or a cat I saw?', LLMS_Interface_Case::CASE_UPPER_WORDS )
		);
	}
}
