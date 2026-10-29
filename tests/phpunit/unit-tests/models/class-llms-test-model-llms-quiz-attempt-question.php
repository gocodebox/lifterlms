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

	/**
	 * A single answer is not announced as a one-item list.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_answer_single_is_not_a_list() {

		$question = new LLMS_Quiz_Attempt_Question(
			array(
				'id'     => 999999,
				'answer' => array( 'True' ),
			)
		);

		$this->assertSame( '<div class="llms-quiz-attempt-answer">True</div>', $question->get_answer() );
	}

	/**
	 * Multiple answers stay in a list.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_answer_multiple_is_a_list() {

		$question = new LLMS_Quiz_Attempt_Question(
			array(
				'id'     => 999999,
				'answer' => array( 'A', 'B' ),
			)
		);

		$html = $question->get_answer();

		$this->assertStringContainsString( '<ul class="llms-quiz-attempt-answers">', $html );
		$this->assertStringContainsString( '<li class="llms-quiz-attempt-answer">A</li>', $html );
		$this->assertStringContainsString( '<li class="llms-quiz-attempt-answer">B</li>', $html );
	}

	/**
	 * Answer HTML keeps formatting but loses form controls and data attributes, including HTML returned by filters.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_answer_strips_form_controls_and_data_attributes() {

		$payload = '<p class="ql-align-center"><strong>Bold</strong></p>'
			. '<div class="llms-quiz-attempt-question" data-question-id="&quot;&gt;&lt;img src=x onerror=alert(1)&gt;">x</div>'
			. '<textarea name="points[1]">99</textarea><button name="llms_quiz_attempt_action" value="llms_attempt_delete">y</button>';

		$question = new LLMS_Quiz_Attempt_Question(
			array(
				'id'     => 999999,
				'answer' => array( $payload ),
			)
		);

		$filtered = function () use ( $payload ) {
			return $payload;
		};

		$results = array( $question->get_answer() );
		add_filter( 'llms_quiz_attempt_question_get_answer_pre', $filtered );
		$results[] = $question->get_answer();
		remove_filter( 'llms_quiz_attempt_question_get_answer_pre', $filtered );

		foreach ( $results as $html ) {
			$this->assertStringContainsString( '<p class="ql-align-center"><strong>Bold</strong></p>', $html );
			$this->assertStringContainsString( '<div class="llms-quiz-attempt-question">x</div>', $html );
			$this->assertStringNotContainsString( 'data-', $html );
			$this->assertStringNotContainsString( '<textarea', $html );
			$this->assertStringNotContainsString( '<button', $html );
		}
	}
}
