<?php
/**
 * Tests for LifterLMS Quiz Model
 *
 * @package  LifterLMS_Tests/Models
 *
 * @group post_models
 * @group quizzes
 * @group quiz
 *
 * @since 3.16.0
 * @since 3.37.2 Added test coverage for many untested methods.
 * @since [version] Added test coverage for `is_orphan()` method with `$deep` param set to true.
 */
class LLMS_Test_LLMS_Quiz extends LLMS_PostModelUnitTestCase {

	/**
	 * class name for the model being tested by the class
	 *
	 * @var  string
	 */
	protected $class_name = 'LLMS_Quiz';

	/**
	 * db post type of the model being tested
	 *
	 * @var  string
	 */
	protected $post_type = 'llms_quiz';

	/**
	 * Get properties, used by test_getters_setters
	 *
	 * This should match, exactly, the object's $properties array.
	 *
	 * @since 3.16.0
	 *
	 * @return string[]
	 */
	protected function get_properties() {
		return array(
			'lesson_id' => 'absint',
		);
	}

	/**
	 * Get data to fill a create post with
	 *
	 * This is used by test_getters_setters.
	 *
	 * @since 3.16.0
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			'lesson_id' => 123,
		);
	}


	/**
	 * Test the questions()->create_question() method.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_create_question() {

		$this->create( 'test title' );
		$this->assertTrue( is_numeric( $this->obj->questions()->create_question() ) );

	}

	/**
	 * Test the questions()->delete_question() method.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_delete_question() {

		$this->create( 'test title' );
		$qid = $this->obj->questions()->create_question();
		$this->assertTrue( $this->obj->questions()->delete_question( $qid ) );

		// belongs to another quiz, can't delete
		$this->create( 'second question' );
		$this->assertFalse( $this->obj->questions()->delete_question( $qid ) );

		// doesn't exist
		$this->assertFalse( $this->obj->questions()->delete_question( 999999999 ) );

	}

	/**
	 * Test get_course() on a quiz with no parent lesson.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_course_no_lesson() {

		$this->create( 'test title' );
		$this->assertFalse( $this->obj->get_course() );

	}

	/**
	 * Test get_course() on a quiz with a parent lesson which has no parent course.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_course_lesson_no_course() {

		$this->create( 'test title' );
		$lesson = llms_get_post( $this->factory->post->create( array( 'post_type' => 'lesson' ) ) );
		$lesson->set( 'quiz', $this->obj->get( 'id' ) );

		$this->assertFalse( $this->obj->get_course() );

	}

	/**
	 * Test get_course() success.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_course() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$lesson = $course->get_lessons()[0];
		$quiz = $lesson->get_quiz();
		$this->assertEquals( $course, $quiz->get_course() );

	}

	/**
	 * Test get_lesson() when no value is set.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_lesson_no_value() {

		$this->create( 'test title' );
		$this->obj->set( 'lesson_id', '' );
		$this->assertFalse( $this->obj->get_lesson() );

	}

	/**
	 * Test get_lesson() when the value is an invalid post.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_lesson_invalid() {

		$this->create( 'test title' );
		$post = $this->factory->post->create();
		$this->obj->set( 'lesson_id', ++$post );
		$this->assertNull( $this->obj->get_lesson() );

	}

	/**
	 * Test get_lesson() success.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_get_lesson() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$lesson = $course->get_lessons()[0];
		$quiz = $lesson->get_quiz();
		$this->assertEquals( $lesson, $quiz->get_lesson() );

	}

	/**
	 * Test the questions()->get_question() method.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_get_question() {

		$this->create( 'test title' );
		$qid = $this->obj->questions()->create_question();
		$this->assertTrue( is_a( $this->obj->questions()->get_question( $qid ), 'LLMS_Question' ) );

		// question doesn't belong to quiz so it should return false
		$this->create( 'second question' );
		$this->assertFalse( $this->obj->questions()->get_question( $qid ) );

		// question doesn't exist
		$this->assertFalse( $this->obj->questions()->get_question( 9999999 ) );

	}

	/**
	 * Test the questions() method.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_get_questions() {

		$this->create( 'test title' );
		$i = 1;
		while( $i <= 3 ) {
			$this->obj->questions()->create_question();
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

	/**
	 * Test the has_attempt_limit() method.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_has_attempt_limit() {

		$this->create();

		// No value set.
		$this->obj->set( 'limit_attempts', '' );
		$this->assertFalse( $this->obj->has_attempt_limit() );

		// Explicit no.
		$this->obj->set( 'limit_attempts', 'no' );
		$this->assertFalse( $this->obj->has_attempt_limit() );

		// Something unexpected (still no).
		$this->obj->set( 'limit_attempts', 'fake' );
		$this->assertFalse( $this->obj->has_attempt_limit() );

		// Yes..
		$this->obj->set( 'limit_attempts', 'yes' );
		$this->assertTrue( $this->obj->has_attempt_limit() );

	}

	/**
	 * Test the has_time_limit() method.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_has_time_limit() {

		$this->create();

		// No value set.
		$this->obj->set( 'limit_time', '' );
		$this->assertFalse( $this->obj->has_time_limit() );

		// Explicit no.
		$this->obj->set( 'limit_time', 'no' );
		$this->assertFalse( $this->obj->has_time_limit() );

		// Something unexpected (still no).
		$this->obj->set( 'limit_time', 'fake' );
		$this->assertFalse( $this->obj->has_time_limit() );

		// Yes..
		$this->obj->set( 'limit_time', 'yes' );
		$this->assertTrue( $this->obj->has_time_limit() );

	}

	/**
	 * Test is_open() with no student.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_is_open_no_student() {

		$this->create();
		$this->assertFalse( $this->obj->is_open() );

	}

	/**
	 * Test is_open() with a student when there's no attempt limits.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_is_open_with_student_no_limits() {

		$this->create();

		$user = $this->factory->student->create();

		// Pass in a user id.
		$this->assertTrue( $this->obj->is_open( $user ) );

		// Use the current session's user.
		wp_set_current_user( $user );
		$this->assertTrue( $this->obj->is_open() );

	}

	/**
	 * Test is_open() with a student when there are attempt limits.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_is_open_with_student_with_limits() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$lesson = $course->get_lessons()[0];
		$quiz = $lesson->get_quiz();

		$quiz->set( 'limit_attempts', 'yes' );
		$quiz->set( 'allowed_attempts', 1 );

		$user = $this->factory->student->create();

		// Use the current session's user.
		wp_set_current_user( $user );
		$this->assertTrue( $quiz->is_open() );

		// Pass in a user id.
		$this->assertTrue( $quiz->is_open( $user ) );

		// Take the quiz.
		$this->take_quiz( $quiz->get( 'id' ), $user );

		// Use the current session's user.
		$this->assertFalse( $quiz->is_open() );

		// Pass in a user id.
		$this->assertFalse( $quiz->is_open( $user ) );

	}

	/**
	 * Test the is_orphan() method.
	 *
	 * @since 3.37.2
	 *
	 * @return void
	 */
	public function test_is_orphan() {

		$this->create();

		$this->obj->set( 'lesson_id', '' );
		$this->assertTrue( $this->obj->is_orphan() );

		$this->obj->set( 'lesson_id', 123 );
		$this->assertFalse( $this->obj->is_orphan() );

	}

	/**
	 * Test the is_orphan() method with the $deep param set to true
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_is_orphan_deep() {

		$this->create();
		$lesson = llms_get_post( $this->factory->post->create( array( 'post_type' => 'lesson' ) ) );
		$lesson->set( 'quiz', $this->obj->get( 'id' ) );

		// Quiz `lesson_id` meta unset, we expect both `is_orpan()` and `is_orphan( $deep = true )` to return true.
		$this->obj->set( 'lesson_id', '' );
		$this->assertTrue( $this->obj->is_orphan() );
		$this->assertTrue( $this->obj->is_orphan( true ) );

		// Quiz `lesson_id` set as `$lesson`'s id, we expect both `is_orpan()` and `is_orphan( $deep = true )` to return false.
		$this->obj->set( 'lesson_id', $lesson->get( 'id' ) );
		$this->assertFalse( $this->obj->is_orphan() );
		$this->assertFalse( $this->obj->is_orphan( true ) );

		// quiz `lesson_id` and the lesson's quiz id differ: we expect `is_orpan()` to return false but `is_orphan( $deep = true )` to return true.
		$lesson->set( 'quiz', 123 );
		$this->assertFalse( $this->obj->is_orphan() );
		$this->assertTrue( $this->obj->is_orphan( true ) );

		// quiz `lesson_id` and lesson's quiz id are equal but 123 is not a real lesson's id: we expect `is_orpan()` to return false but `is_orphan( $deep = true )` to return true.
		$this->obj->set( 'lesson_id', 123 );
		$this->assertFalse( $this->obj->is_orphan() );
		$this->assertTrue( $this->obj->is_orphan( true ) );

	}

	/**
	 * Test the questions()->update_question() method.
	 *
	 * @since 3.16.0
	 *
	 * @return void
	 */
	public function test_update_question() {

		$this->create( 'test title' );

		// create when no id supplied
		$id = $this->obj->questions()->update_question();
		$this->assertTrue( is_numeric( $id ) );

		// update should return it's own id
		$this->assertEquals( $id, $this->obj->questions()->update_question( array( 'id' => $id ) ) );

		// can't update from another quiz
		$this->create( 'second question' );
		$this->assertFalse( $this->obj->questions()->update_question( array( 'id' => $id ) ) );

	}

}
