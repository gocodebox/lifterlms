<?php
/**
 * Tests for LLMS_Quiz_Attempt_Question.
 *
 * @group quizzes
 * @group quiz_attempt
 *
 * @since 10.2.0
 */
class LLMS_Test_Model_Quiz_Attempt_Question extends LLMS_UnitTestCase {

	/**
	 * Test answer retrieval when the question post has been deleted.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_get_answer_array_deleted_question() {

		$question = new LLMS_Quiz_Attempt_Question(
			array(
				'id'     => 999999,
				'answer' => array( 'foo' ),
				'points' => 1,
			)
		);

		$this->assertEquals( array( 'foo' ), $question->get_answer_array() );
		$this->assertEquals( array(), $question->get_correct_answer_array() );
	}
}
