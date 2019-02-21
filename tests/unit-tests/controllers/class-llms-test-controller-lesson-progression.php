<?php
/**
 * Tests for LifterLMS Lesson Progression Forms & Functios
 * @group    controllers
 * @group    lessons
 * @since    3.17.1
 * @version  [version]
 */
class LLMS_Test_Controller_Lesson_Progression extends LLMS_UnitTestCase {

	/**
	 * setup tests.
	 * @return   void
	 * @since    3.17.1
	 * @version  3.17.1
	 */
	public function setUp() {
		llms_clear_notices();
		parent::setUp();
	}

	/**
	 * Test the handle_admin_managment_forms() method.
	 *
	 * @return  void
	 * @since   [version]
	 * @version [version]
	 */
	public function test_handle_admin_managment_forms() {

		$data = array();

		$class = new LLMS_Controller_Lesson_Progression();
 		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 2, 'quizzes' => 0 ) );
 		$student_id = $this->factory->student->create_and_enroll( $course->get( 'id' ) );

		// form not submitted
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 0, did_action( 'llms_mark_complete' ) );

		// form submitted but missing required fields
		$data['llms-admin-progression-nonce'] = wp_create_nonce( 'llms-admin-lesson-progression' );
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 0, did_action( 'llms_mark_complete' ) );

		$data['lesson_id'] = $course->get_lessons( 'ids' )[0];
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 0, did_action( 'llms_mark_complete' ) );

		$data['student_id'] = $student_id;
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 0, did_action( 'llms_mark_complete' ) );

		// all data but invalid action.
		$data['llms-lesson-action'] = 'fake';
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 0, did_action( 'llms_mark_complete' ) );

		// Mark the lesson complete.
		$data['llms-lesson-action'] = 'complete';
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 1, did_action( 'llms_mark_complete' ) );

		// Mark it incomplete.
		$data['llms-lesson-action'] = 'incomplete';
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 3, did_action( 'llms_mark_incomplete' ) ); // @note the mark_incomplete method cascades up and marks parents incomplete even if they're already incomplete, this is possibly a bug.
		$this->assertEquals( 1, did_action( 'llms_mark_complete' ) );

	}

	/**
	 * Test the submission of the mark lesson complete form
	 * @return   void
	 * @since    3.17.1
	 * @version  [version]
	 */
	public function test_handle_complete_form() {

		// form not submitted
		$this->mockPostRequest( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_trigger_lesson_completion' ) );

		// form submitted but missing required fields
		$this->mockPostRequest( array(
			'_wpnonce' => wp_create_nonce( 'mark_complete' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_trigger_lesson_completion' ) );

		// form submitted but invalid lesson id
		$this->mockPostRequest( array(
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

		$this->mockPostRequest( array(
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
	 *
	 * @return   void
	 * @since    3.17.1
	 * @version  [version]
	 */
	public function test_handle_incomplete_form() {

		// form not submitted
		$this->mockPostRequest( array() );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );

		// form submitted but missing required fields
		$this->mockPostRequest( array(
			'_wpnonce' => wp_create_nonce( 'mark_incomplete' ),
		) );
		do_action( 'init' );
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );

		// form submitted but invalid lesson id
		$this->mockPostRequest( array(
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

		$this->mockPostRequest( array(
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
