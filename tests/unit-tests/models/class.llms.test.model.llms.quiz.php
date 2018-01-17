<?php
/**
 * Tests for LifterLMS Lesson Model
 * @group     post_models
 * @group     quizzes
 * @group     quiz
 * @since     [version]
 * @version   [version]
 */
class LLMS_Test_LLMS_Quiz extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 * @var  string
	 */
	protected $class_name = 'LLMS_Quiz';

	/**
	 * db post type of the model being tested
	 * @var  string
	 */
	protected $post_type = 'llms_quiz';

	/**
	 * Get properties, used by test_getters_setters
	 * This should match, exactly, the object's $properties array
	 * @return   array
	 * @since    3.14.8
	 * @version  3.14.8
	 */
	protected function get_properties() {
		return array(
			'lesson_id' => 'absint',
		);
	}

	/**
	 * Get data to fill a create post with
	 * This is used by test_getters_setters
	 * @return   array
	 * @since    3.14.8
	 * @version  3.14.8
	 */
	protected function get_data() {
		return array(
			'lesson_id' => 123,
		);
	}



	public function test_create_question() {

		$this->create( 'test title' );
		$this->assertTrue( is_numeric( $this->obj->create_question() ) );

	}

	public function test_delete_question() {

		$this->create( 'test title' );
		$qid = $this->obj->create_question();
		$this->assertTrue( $this->obj->delete_question( $qid ) );

		// belongs to another quiz, can't delete
		$this->create( 'second question' );
		$this->assertFalse( $this->obj->delete_question( $qid ) );

		// doesn't exist
		$this->assertFalse( $this->obj->delete_question( 999999999 ) );

	}

	public function test_get_question() {

		$this->create( 'test title' );
		$qid = $this->obj->create_question();
		$this->assertTrue( is_a( $this->obj->get_question( $qid ), 'LLMS_Question' ) );

		// question doesn't belong to quiz so it should return false
		$this->create( 'second question' );
		$this->assertFalse( $this->obj->get_question( $qid ) );

		// question doesn't exist
		$this->assertFalse( $this->obj->get_question( 9999999 ) );

	}

	public function test_get_questions() {

		$this->create( 'test title' );
		$i = 1;
		while( $i <= 3 ) {
			$this->obj->create_question();
			$i++;
		}

		// check default 'questions'
		$questions = $this->obj->get_questions();
		$this->assertEquals( 3, count( $questions ) );
		foreach ( $questions as $question ) {
			$this->assertInstanceOf( 'LLMS_Question', $question );
		}

		// check posts return
		$questions = $this->obj->get_questions( 'posts' );
		$this->assertEquals( 3, count( $questions ) );
		foreach ( $questions as $question ) {
			$this->assertInstanceOf( 'WP_Post', $question );
		}

		// check id return
		$questions = $this->obj->get_questions( 'ids' );
		$this->assertEquals( 3, count( $questions ) );
		foreach ( $questions as $question ) {
			$this->assertTrue( is_numeric( $question ) );
		}

	}

	public function test_update_question() {

		$this->create( 'test title' );

		// create when no id supplied
		$id = $this->obj->update_question();
		$this->assertTrue( is_numeric( $id ) );

		// update should return it's own id
		$this->assertEquals( $id, $this->obj->update_question( array( 'id' => $id ) ) );

		// can't update from another quiz
		$this->create( 'second question' );
		$this->assertFalse( $this->obj->update_question( array( 'id' => $id ) ) );

	}

}
