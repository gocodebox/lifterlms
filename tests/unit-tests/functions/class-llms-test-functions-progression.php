<?php
/**
 * Test course and lesson progression functions.
 *
 * @group functions
 * @group progression_functions
 * @package  LifterLMS/Tests/Functions
 * @since    [version]
 * @version  [version]
 */
class LLMS_Test_Functions_Progression extends LLMS_Unit_Test_Case {

	/**
	 * Test the llms_allow_lesson_completion() method.
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_llms_allow_lesson_completion() {

		$student = $this->factory->student->create_and_get();
		$course = $this->factory->course->create_and_get();
		$lesson_id = $course->get_lessons( 'ids' )[0];

		// progression is okay with no intervention.
		$this->assertTrue( llms_allow_lesson_completion( $student->get( 'id' ), $lesson_id ) );

		// something somewhere prevents progression.
		add_filter( 'llms_allow_lesson_completion', '__return_false' );
		$this->assertFalse( llms_allow_lesson_completion( $student->get( 'id' ), $lesson_id ) );

		// remove the filter so we don't potentially break other tests.
		remove_filter( 'llms_allow_lesson_completion', '__return_false' );

	}

	public function test_llms_show_mark_complete_button() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 3, 'quizzes' => 2 ) );
		$no_quiz = $course->get_lessons()[0];
		$has_quiz = $course->get_lessons()[1];

		$has_unpublished_quiz = $course->get_lessons()[2];
		$has_unpublished_quiz->get_quiz()->set( 'status', 'draft' );

		$this->assertTrue( llms_show_mark_complete_button( $no_quiz ) );
		$this->assertFalse( llms_show_mark_complete_button( $has_quiz ) );
		$this->assertTrue( llms_show_mark_complete_button( $has_unpublished_quiz ) );

	}

	public function test_llms_show_take_quiz_button() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 3, 'quizzes' => 2 ) );
		$no_quiz = $course->get_lessons()[0];
		$has_quiz = $course->get_lessons()[1];

		$has_unpublished_quiz = $course->get_lessons()[2];
		$has_unpublished_quiz->get_quiz()->set( 'status', 'draft' );

		$this->assertFalse( llms_show_take_quiz_button( $no_quiz ) );
		$this->assertTrue( llms_show_take_quiz_button( $has_quiz ) );
		$this->assertFalse( llms_show_take_quiz_button( $has_unpublished_quiz ) );

	}

}
