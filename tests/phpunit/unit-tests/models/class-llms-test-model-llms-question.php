<?php
/**
 * Tests for LifterLMS Quiz Model
 *
 * @package LifterLMS_Tests/Models
 *
 * @group post_models
 * @group quizzes
 * @group questions
 *
 * @since 3.16.12
 * @since 3.30.1 Added more tests for `get_next_choice_marker()` and `get_choices()`
 * @since [version] Add tests for the `grade()` method.
 */
class LLMS_Test_LLMS_Question extends LLMS_PostModelUnitTestCase {

	/**
	 * Class name for the model being tested by the class
	 *
	 * @var string
	 */
	protected $class_name = 'LLMS_Question';

	/**
	 * DB post type of the model being tested
	 *
	 * @var  string
	 */
	protected $post_type = 'llms_question';

	/**
	 * Get properties, used by test_getters_setters
	 *
	 * This should match, exactly, the object's $properties array
	 *
	 * @since 3.16.12
	 *
	 * @return array
	 */
	protected function get_properties() {
		return array(
			'clarifications' => 'html',
			'clarifications_enabled' => 'yesno',
			'description_enabled' => 'yesno',
			// 'image' => 'array',
			'multi_choices' => 'yesno',
			'parent_id' => 'absint',
			'points' => 'absint',
			'question_type' => 'string',
			'title' => 'html',
			'video_enabled' => 'yesno',
			'video_src' => 'string',
		);
	}

	/**
	 * Get data to fill a create post with
	 *
	 * This is used by test_getters_setters
	 *
	 * @since 3.16.12
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			'clarifications' => '<p>this is <b>a</b> clarification</p>',
			'clarifications_enabled' => 'yes',
			'description_enabled' => 'yes',
			// 'image' => 'array',
			'multi_choices' => 'no',
			'parent_id' => 123,
			'points' => 3,
			'question_type' => 'choice',
			'title' => 'this <b>is</b> <i>a</i> question',
			'video_enabled' => 'yes',
			'video_src' => 'http://example.tld/video_embed',
		);
	}

	/**
	 * Overwrite unnecessary parent test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_edit_date() {
		$this->assertTrue( true );
	}

	/**
	 * Test the has_description() method.
	 *
	 * @since 3.16.12
	 *
	 * @return void
	 */
	public function test_has_description() {

		$this->create( 'title' );
		$this->assertFalse( $this->obj->has_description() );

		$this->obj->set( 'content', 'arstarst' );
		$this->assertFalse( $this->obj->has_description() );

		$this->obj->set( 'description_enabled', 'yes' );
		$this->assertTrue( $this->obj->has_description() );

		$this->obj->set( 'content', '' );
		$this->assertFalse( $this->obj->has_description() );

	}

	/**
	 * Test the has_video() method.
	 *
	 * @since 3.16.12
	 *
	 * @return void
	 */
	public function test_has_video() {

		$this->create( 'title' );
		$this->assertFalse( $this->obj->has_video() );

		$this->obj->set( 'video_src', 'http://example.tld/video_embed' );
		$this->assertFalse( $this->obj->has_video() );

		$this->obj->set( 'video_enabled', 'yes' );
		$this->assertTrue( $this->obj->has_video() );

		$this->obj->set( 'video_src', '' );
		$this->assertFalse( $this->obj->has_video() );

	}

	/**
	 * Test the get_next_choice_marker() method.
	 *
	 * @since 3.30.1
	 *
	 * @return void
	 */
	public function test_get_next_choice_marker() {

		$this->create();
		$this->obj->set( 'question_type', 'choice' );
		foreach( range( 'A', 'Z' ) as $expected ) {
			$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( $this->obj, 'get_next_choice_marker' ) );
			$choice = $this->obj->get_choice( $this->obj->create_choice( array() ) );
			$this->assertEquals( $expected, $choice->get( 'marker' ) );
		}

	}

	/**
	 * Test the get_choices() method.
	 *
	 * @since 3.30.1
	 *
	 * @return void
	 */
	public function test_get_choices() {

		foreach ( array( range( 'A', 'Z' ), range( 1, 26 ) ) as $i => $markers ) {

			$last_marker = false;

			if ( 1 === $i ) {
				// Filter marker for testing numeric values.
				add_filter( 'llms_get_question_type', function( $data, $type ) {
					if ( 'choice' === $type ) {
						$data['choices']['markers'] = range( 1, 26 );
					}
					return $data;
				}, 923, 2 );
			}

			$this->create();
			$this->obj->set( 'question_type', 'choice' );

			// No choices.
			$this->assertSame( array(), $this->obj->get_choices() );

			$expected_ids = array();
			foreach ( $markers as $marker ) {
				$expected_ids[] = $this->obj->create_choice( array( 'marker' => $marker ) );
			}

			$choices = $this->obj->get_choices( 'choices' );
			$this->assertEquals( 26, count( $choices ) );
			foreach ( $choices as $choice ) {

				// Ensure the correct order.
				if ( ! empty( $last_marker ) ) {
					$this->assertEquals( -1, strnatcmp( $last_marker, $choice->get( 'marker' ) ), $last_marker . ':' . $choice->get( 'marker' ) );
				}
				$last_marker = $choice->get( 'marker' );

				// Must be a choice.
				$this->assertTrue( is_a( $choice, 'LLMS_Question_Choice' ) );

				// Make sure the ID exists in the array.
				$this->assertTrue( in_array( $choice->get( 'id' ), $expected_ids, true ) );

			}

			// Only ids.
			$ids = $this->obj->get_choices( 'ids' );
			$this->assertSame( 26, count( $ids ) );
			foreach( $expected_ids as $id ) {
				$this->assertTrue( in_array( '_llms_choice_' . $id, $ids, true ) );
			}

		}

		// Remove marker filter.
		remove_all_filters( 'llms_get_question_type', 923 );

	}

	/**
	 * Test grade() for a question with no points.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_grade_no_points() {

		$question = new LLMS_Question( 'new' );
		$this->assertNull( $question->grade( array( 1 ) ) );

	}

	/**
	 * Test grade() when grading is handled by a filter from a 3rd party
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_grade_custom() {

		$question = new LLMS_Question( 'new' );
		$question->set( 'question_type', 'custom_grading_test_type' );

		add_filter( 'llms_custom_grading_test_type_question_pre_grade', function( $grade ) {
			return 'yes';
		}, 529 );

		$this->assertEquals( 'yes', $question->grade( array( 1 ) ) );

		remove_all_filters( 'llms_custom_grading_test_type_question_pre_grade', 529 );

	}

	/**
	 * Test grade() for a multiple choice question with multiple correct answers.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_grade_choices_multi() {

		$question = new LLMS_Question( 'new' );
		$question->set( 'question_type', 'choice' );
		$question->set( 'multi_choices', 'yes' );
		$question->set( 'points', 1 );

		$choices = array(
			$question->create_choice( array( 'choice' => 'A', 'correct' => true ) ),
			$question->create_choice( array( 'choice' => 'B' ) ),
			$question->create_choice( array( 'choice' => 'C', 'correct' => true ) ),
		);

		// Correct answer.
		$this->assertEquals( 'yes', $question->grade( array( $choices[0], $choices[2] ) ) );

		// Order doesn't matter.
		$this->assertEquals( 'yes', $question->grade( array( $choices[2], $choices[0] ) ) );

		// Various potential incorrect answers.
		$this->assertEquals( 'no', $question->grade( array( $choices[0] ) ) );
		$this->assertEquals( 'no', $question->grade( array( $choices[1] ) ) );
		$this->assertEquals( 'no', $question->grade( array( $choices[2] ) ) );
		$this->assertEquals( 'no', $question->grade( array( $choices[0], $choices[1] ) ) );
		$this->assertEquals( 'no', $question->grade( array( $choices[1], $choices[2] ) ) );
		$this->assertEquals( 'no', $question->grade( array( $choices[0], $choices[1], $choices[2] ) ) );

	}

	/**
	 * Test grade() for a multiple choice with a single correct answer and true_false questions.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_grade_choices_single() {

		foreach ( array( 'choice', 'true_false' ) as $question_type ) {

			$question = new LLMS_Question( 'new' );
			$question->set( 'question_type', $question_type );
			$question->set( 'points', 1 );

			$choices = array(
				'correct'   => $question->create_choice( array( 'choice' => 'A', 'correct' => true ) ),
				'incorrect' => $question->create_choice( array( 'choice' => 'B' ) ),
			);

			$this->assertEquals( 'yes', $question->grade( array( $choices['correct'] ) ) );
			$this->assertEquals( 'no', $question->grade( array( $choices['incorrect'] ) ) );

		}

	}

	/**
	 * Test grade() for a conditionally auto-graded question.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_grade_conditional() {

		$question = new LLMS_Question( 'new' );
		$question->set( 'question_type', 'fake_conditional_type' );
		$question->set( 'points', 1 );
		$question->set( 'auto_grade', 'yes' );
		$question->set( 'correct_value', 'This is correct.' );

		// Mock Conditional grading enabled.
		add_filter( 'llms_fake_conditional_type_question_supports', function( $ret, $feature, $option ) {
			return ( 'grading' === $feature && 'conditional' === $option );
		}, 329, 3 );

		// Correct answers, case sensitivity doesn't matter.
		$this->assertEquals( 'yes', $question->grade( array( 'This is correct.' ) ) );
		$this->assertEquals( 'yes', $question->grade( array( 'THIS IS CORRECT.' ) ) );
		$this->assertEquals( 'yes', $question->grade( array( 'this is correct.' ) ) );
		$this->assertEquals( 'yes', $question->grade( array( 'tHiS is coRrECt.' ) ) );
		$this->assertEquals( 'yes', $question->grade( array( 'this IS correct.' ) ) );

		// Incorrect.
		$this->assertEquals( 'no', $question->grade( array( 'This is not correct.' ) ) );

		// Case matters now.
		add_filter( 'llms_quiz_grading_case_sensitive', '__return_true' );
		$this->assertEquals( 'yes', $question->grade( array( 'This is correct.' ) ) );
		$this->assertEquals( 'no', $question->grade( array( 'this is correct.' ) ) );
		remove_filter( 'llms_quiz_grading_case_sensitive', '__return_true' );

		// Add an additional value.
		$question->set( 'correct_value', 'one|TWO' );

		// Correct.
		$this->assertEquals( 'yes', $question->grade( array( 'one', 'TWO' ) ) );
		$this->assertEquals( 'yes', $question->grade( array( 'ONE', 'two' ) ) );
		$this->assertEquals( 'yes', $question->grade( array( 'OnE', 'Two' ) ) );

		// Incorrect.
		$this->assertEquals( 'no', $question->grade( array( 'TWO' ) ) );
		$this->assertEquals( 'no', $question->grade( array( 'one' ) ) );
		$this->assertEquals( 'no', $question->grade( array( 'fake' ) ) );

		// Incorrect, order matters.
		$this->assertEquals( 'no', $question->grade( array( 'TWO', 'one' ) ) );

		// Make case matter.
		add_filter( 'llms_quiz_grading_case_sensitive', '__return_true' );
		$this->assertEquals( 'yes', $question->grade( array( 'one', 'TWO' ) ) );
		$this->assertEquals( 'no', $question->grade( array( 'One', 'Two' ) ) );
		$this->assertEquals( 'no', $question->grade( array( 'Two' ) ) );
		remove_filter( 'llms_quiz_grading_case_sensitive', '__return_true' );

		// Unmock.
		remove_all_filters( 'llms_blank_question_supports', 329 );

	}

}
