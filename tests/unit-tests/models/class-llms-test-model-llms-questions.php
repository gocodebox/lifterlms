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

}
