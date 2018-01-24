<?php
/**
 * Tests for LifterLMS Core Functions
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Functions_Quiz extends LLMS_UnitTestCase {

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

}
