<?php
/**
 * Tests for LifterLMS Core Functions
 * @group    functions
 * @group    functions_quiz
 * @group    quizzes
 * @group    quiz
 * @since    3.16.0
 * @version  3.16.12
 */
class LLMS_Test_Functions_Quiz extends LLMS_UnitTestCase {

	/**
	 * Test picture choice columns
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function test_llms_get_picture_choice_question_cols() {

		$combos = array(
			1 => 1,
			2 => 2,
			3 => 3,
			4 => 4,
			5 => 3,
			6 => 3,
			7 => 4,
			8 => 4,
			9 => 3,
			10 => 5,
			11 => 4,
			12 => 4,
			13 => 5,
			14 => 5,
			15 => 5,
			16 => 4,
			17 => 3,
			18 => 3,
			19 => 5,
			20 => 5,
			21 => 3,
			22 => 4,
			23 => 4,
			24 => 4,
			25 => 5,
			26 => 5,
			27 => 5,
			45 => 5,
			999 => 5,
			9999 => 5,
		);

		foreach ( $combos as $choices => $expected_cols ) {

			$this->assertEquals( $expected_cols, llms_get_picture_choice_question_cols( $choices ) );

		}

	}

	/**
	 * Test llms_shuffle_choices
	 * @return   void
	 * @since    3.16.12
	 * @version  3.16.12
	 */
	public function test_llms_shuffle_choices() {

		// 0 & 1 elements can't really be shuffled...
		$choices = array();
		$this->assertEquals( $choices, llms_shuffle_choices( $choices ) );

		$choices = array( 1 );
		$this->assertEquals( $choices, llms_shuffle_choices( $choices ) );

		// 2 or more items will never match the original after shuffling
		$i = 2;
		while( $i <= 26 ) {

			$choices = range( 0, $i );
			$this->assertNotEquals( $choices, llms_shuffle_choices( $choices ) );
			$i++;

		}


	}

}
