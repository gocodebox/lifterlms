<?php
/**
 * Tests for LifterLMS Lesson Progression Forms & Functios
 * @group    controllers
 * @group    lessons
 * @since    3.17.1
 * @version  [version]
 */
class LLMS_Test_Controller_Lesson_Progression extends LLMS_UnitTestCase {

	public function setUp() {
		llms_clear_notices();
		parent::setUp();
	}

	/**
	 * Test the submission of the mark lesson complete form
	 * @return   void
	 * @since    3.17.1
	 * @version  3.17.1
	 */
	public function test_handle_complete_form() {

		// form not submitted
		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_trigger_lesson_completion' ) );

		// form submitted but missing required fields
		$this->setup_post( array(
			'_wpnonce' => wp_create_nonce( 'mark_complete' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_trigger_lesson_completion' ) );

		// form submitted but invalid lesson id
		$this->setup_post( array(
			'_wpnonce' => wp_create_nonce( 'mark_complete' ),
			'mark-complete' => 'wut', // lesson id
			'mark_complete' => '', // button
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_trigger_lesson_completion' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$lesson_id = $course->get_lessons( 'ids' )[0];

		$student = $this->get_mock_student();
		$student->enroll( $course->get( 'id' ) );
		wp_set_current_user( $student->get_id() );

		$this->setup_post( array(
			'_wpnonce' => wp_create_nonce( 'mark_complete' ),
			'mark-complete' => $lesson_id, // lesson id
			'mark_complete' => '', // button
		) );
		do_action( 'init' );
		$this->assertEquals( 1, did_action( 'llms_trigger_lesson_completion' ) );
		$this->assertTrue( $student->is_complete( $lesson_id, 'lesson' ) );

	}

	/**
	 * Test the submission of the mark lesson incomplete form
	 * @return   void
	 * @since    3.17.1
	 * @version  3.17.1
	 */
	public function test_handle_inccomplete_form() {

		// form not submitted
		$this->setup_post( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );

		// form submitted but missing required fields
		$this->setup_post( array(
			'_wpnonce' => wp_create_nonce( 'mark_incomplete' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );

		// form submitted but invalid lesson id
		$this->setup_post( array(
			'_wpnonce' => wp_create_nonce( 'mark_incomplete' ),
			'mark-incomplete' => 'wut', // lesson id
			'mark_incomplete' => '', // button
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$lesson_id = $course->get_lessons( 'ids' )[0];

		$student = $this->get_mock_student();
		$student->enroll( $course->get( 'id' ) );
		$student->mark_complete( $lesson_id, 'lesson' );
		wp_set_current_user( $student->get_id() );

		$this->setup_post( array(
			'_wpnonce' => wp_create_nonce( 'mark_incomplete' ),
			'mark-incomplete' => $lesson_id, // lesson id
			'mark_incomplete' => '', // button
		) );
		do_action( 'init' );
		$this->assertFalse( $student->is_complete( $lesson_id, 'lesson' ) );

	}

	/**
	 * Test the Mark Complete function as triggered by the `llms_trigger_lesson_completion` action
	 * @return   void
	 * @since    3.17.1
	 * @version  3.17.1
	 */
	public function test_mark_complete() {

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$lesson_id = $course->get_lessons( 'ids' )[0];

		$student = $this->get_mock_student();
		$student->enroll( $course->get( 'id' ) );

		do_action( 'llms_trigger_lesson_completion', $student->get( 'id' ), $lesson_id );
		$this->assertTrue( $student->is_complete( $lesson_id, 'lesson' ) );

	}

}
