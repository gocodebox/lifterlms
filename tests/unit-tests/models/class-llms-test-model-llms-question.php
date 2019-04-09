<?php
/**
 * Tests for LifterLMS Quiz Model
 * @group     post_models
 * @group     quizzes
 * @group     questions
 * @since     3.16.12
 * @version   3.16.12
 */
class LLMS_Test_LLMS_Question extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Question';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'llms_question';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    3.16.12
	 * @version  3.16.12
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
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    3.16.12
	 * @version  3.16.12
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
	 * Test the has_description() method.
	 *
	 * @since 3.16.12
	 * @version 3.16.12
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
	 * @version 3.16.12
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
	 * @version 3.30.1
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
	 * Test the get_questions() method.
	 *
	 * @since 3.30.1
	 * @version 3.30.1
	 *
	 * @return void
	 */
	public function test_get_questions() {

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

}
