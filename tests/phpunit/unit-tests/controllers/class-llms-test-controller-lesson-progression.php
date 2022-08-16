<?php
/**
 * Tests for LifterLMS Lesson Progression Forms & Functions
 *
 * @group controllers
 * @group lessons
 *
 * @since 3.17.1
 */
class LLMS_Test_Controller_Lesson_Progression extends LLMS_UnitTestCase {

	/**
	 * Setup tests.
	 *
	 * @since 3.17.1
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {
		llms_clear_notices();
		parent::set_up();
	}

	/**
	 * Test the handle_admin_managment_forms() method.
	 *
	 * @since 3.29.0
	 * @since [version] Added tests on user caps.
	 *
	 * @return void
	 */
	public function test_handle_admin_managment_forms() {

		$data = array();

		$class = new LLMS_Controller_Lesson_Progression();
 		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 2, 'quizzes' => 0 ) );
 		$student_id = $this->factory->student->create_and_enroll( $course->get( 'id' ) );

		// Form not submitted.
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 0, did_action( 'llms_mark_complete' ) );

		// Form submitted but missing required fields.
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

		// All data but invalid action...
		$data['llms-lesson-action'] = 'fake';
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 0, did_action( 'llms_mark_complete' ) );

		// Mark lessons complete/incomplete as users with no adequate caps, or no user.
		wp_set_current_user( 0 );

		// Mark the lesson complete...
		$data['llms-lesson-action'] = 'complete';
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 0, did_action( 'llms_mark_complete' ) );

		// Mark it incomplete...
		$data['llms-lesson-action'] = 'incomplete';
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 0, did_action( 'llms_mark_complete' ) );

		// Mark lessons complete/incomplete as users with adequate caps.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'lms_manager' ) ) );
		$data['llms-admin-progression-nonce'] = wp_create_nonce( 'llms-admin-lesson-progression' );

		// Mark the lesson complete...
		$data['llms-lesson-action'] = 'complete';
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );
		$this->assertEquals( 1, did_action( 'llms_mark_complete' ) );

		// Mark it incomplete...
		$data['llms-lesson-action'] = 'incomplete';
		$this->mockPostRequest( $data );
		$class->handle_admin_managment_forms();
		$this->assertEquals( 3, did_action( 'llms_mark_incomplete' ) ); // @note the mark_incomplete method cascades up and marks parents incomplete even if they're already incomplete, this is possibly a bug..
		$this->assertEquals( 1, did_action( 'llms_mark_complete' ) );

	}

	/**
	 * Test the submission of the mark lesson complete form
	 *
	 * @since 3.17.1
	 * @since 3.29.0 Unknown.
	 * @since [version] Call the tested method directly instead of indirectly via `do_action( 'init' )`.
	 *
	 * @return void
	 */
	public function test_handle_complete_form() {

		$main = new LLMS_Controller_Lesson_Progression();

		// Form not submitted.
		$this->mockPostRequest( array() );
		$main->handle_complete_form();
		$this->assertEquals( 0, did_action( 'llms_trigger_lesson_completion' ) );

		// Form submitted but missing required fields.
		$this->mockPostRequest( array(
			'_wpnonce' => wp_create_nonce( 'mark_complete' ),
		) );
		$main->handle_complete_form();
		$this->assertEquals( 0, did_action( 'llms_trigger_lesson_completion' ) );

		// Form submitted but invalid lesson id.
		$this->mockPostRequest( array(
			'_wpnonce' => wp_create_nonce( 'mark_complete' ),
			'mark-complete' => 'wut', // Lesson id.
			'mark_complete' => '', // Button.
		) );
		$main->handle_complete_form();
		$this->assertEquals( 0, did_action( 'llms_trigger_lesson_completion' ) );
		$this->assertEquals( 1, llms_notice_count( 'error' ) );

		$course = llms_get_post( $this->generate_mock_courses( 1, 1, 1, 0, 0 )[0] );
		$lesson_id = $course->get_lessons( 'ids' )[0];

		$student = $this->get_mock_student();
		$student->enroll( $course->get( 'id' ) );
		wp_set_current_user( $student->get_id() );

		$this->mockPostRequest( array(
			'_wpnonce' => wp_create_nonce( 'mark_complete' ),
			'mark-complete' => $lesson_id, // Lesson id.
			'mark_complete' => '', // Button.
		) );
		$main->handle_complete_form();
		$this->assertEquals( 1, did_action( 'llms_trigger_lesson_completion' ) );
		$this->assertTrue( $student->is_complete( $lesson_id, 'lesson' ) );

	}

	/**
	 * Test the submission of the mark lesson incomplete form
	 *
	 * @since 3.17.1
	 * @since 3.29.0 Unknown.
	 * @since [version] Call the tested method directly instead of indirectly via `do_action( 'init' )`.
	 *
	 * @return void
	 */
	public function test_handle_incomplete_form() {

		$main = new LLMS_Controller_Lesson_Progression();

		// Form not submitted.
		$this->mockPostRequest( array() );
		$main->handle_incomplete_form();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );

		// Form submitted but missing required fields.
		$this->mockPostRequest( array(
			'_wpnonce' => wp_create_nonce( 'mark_incomplete' ),
		) );
		$main->handle_incomplete_form();
		$this->assertEquals( 0, did_action( 'llms_mark_incomplete' ) );

		// Form submitted but invalid lesson id.
		$this->mockPostRequest( array(
			'_wpnonce' => wp_create_nonce( 'mark_incomplete' ),
			'mark-incomplete' => 'wut', // Lesson id.
			'mark_incomplete' => '', // Button.
		) );
		$main->handle_incomplete_form();
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
			'mark-incomplete' => $lesson_id, // Lesson id.
			'mark_incomplete' => '', // Button.
		) );
		$main->handle_incomplete_form();
		$this->assertFalse( $student->is_complete( $lesson_id, 'lesson' ) );

	}

	/**
	 * Test the Mark Complete function as triggered by the `llms_trigger_lesson_completion` action
	 *
	 * @since 3.17.1
	 *
	 * @return void
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
