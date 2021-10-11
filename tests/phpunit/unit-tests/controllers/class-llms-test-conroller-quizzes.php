<?php
/**
 * Test LLMS_Controller_Quizzes
 *
 * @package LifterLMS/Tests/Controllers
 *
 * @group controllers
 * @group quizzes
 * @group controller_quizzes
 *
 * @since 3.37.8
 */
class LLMS_Test_Controller_Quizzes extends LLMS_UnitTestCase {

	/**
	 * Setup the test case.
	 *
	 * @since 3.37.8
	 * @since 5.3.3 Renamed from `setUp()` for compat with WP core changes.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->controller = new LLMS_Controller_Quizzes();

	}

	/**
	 * Test maybe_handle_reporting_actions(): form not submitted
	 *
	 * @since 3.37.8
	 *
	 * @return void
	 */
	public function test_maybe_handle_reporting_actions_not_submitted() {

		$this->assertNull( $this->controller->maybe_handle_reporting_actions() );

	}

	/**
	 * Test maybe_handle_reporting_actions(): invalid nonce
	 *
	 * @since 3.37.8
	 *
	 * @return void
	 */
	public function test_maybe_handle_reporting_actions_invalid_nonce() {

		$this->mockPostRequest( array(
			'_llms_quiz_actions_nonce' => 'fake',
		) );

		$this->assertNull( $this->controller->maybe_handle_reporting_actions() );

	}

	/**
	 * Test maybe_handle_reporting_actions(): there's no quiz id passed via to the button form element.
	 *
	 * @since 3.37.8
	 *
	 * @return void
	 */
	public function test_maybe_handle_reporting_actions_no_button() {

		// Button not set.
		$this->mockPostRequest( array(
			'_llms_quiz_actions_nonce' => wp_create_nonce( 'llms-quiz-actions' ),
		) );

		$this->assertFalse( $this->controller->maybe_handle_reporting_actions() );

		// Button empty
		$this->mockPostRequest( array(
			'_llms_quiz_actions_nonce' => wp_create_nonce( 'llms-quiz-actions' ),
			'llms_del_quiz' => '',
		) );

		$this->assertFalse( $this->controller->maybe_handle_reporting_actions() );

	}

	/**
	 * Test maybe_handle_reporting_actions(): submitted WP Post ID isn't a quiz id.
	 *
	 * @since 3.37.8
	 *
	 * @return void
	 */
	public function test_maybe_handle_reporting_actions_not_a_quiz() {

		$this->mockPostRequest( array(
			'_llms_quiz_actions_nonce' => wp_create_nonce( 'llms-quiz-actions' ),
			'llms_del_quiz' => $this->factory->post->create(),
		) );

		$this->assertFalse( $this->controller->maybe_handle_reporting_actions() );

	}

	/**
	 * Test maybe_handle_reporting_actions(): the quiz isn't an orphan.
	 *
	 * @since 3.37.8
	 *
	 * @return void
	 */
	public function test_maybe_handle_reporting_actions_not_an_orphan() {

		$courses = $this->generate_mock_courses( 1, 1, 1, 1, 1 );
		$lesson  = llms_get_post( llms_get_post( $courses[0] )->get_lessons( 'ids' )[0] );
		$quiz    = $lesson->get_quiz();

		$this->mockPostRequest( array(
			'_llms_quiz_actions_nonce' => wp_create_nonce( 'llms-quiz-actions' ),
			'llms_del_quiz' => $quiz->get( 'id' ),
		) );

		$this->assertFalse( $this->controller->maybe_handle_reporting_actions() );

	}

	/**
	 * Test maybe_handle_reporting_actions() success: the quiz is an orphan and can be deleted.
	 *
	 * @since 3.37.8
	 *
	 * @return void
	 */
	public function test_maybe_handle_reporting_actions_is_orphan() {

		$quiz = $this->factory->post->create_and_get( array( 'post_type' => 'llms_quiz' ) );

		$this->mockPostRequest( array(
			'_llms_quiz_actions_nonce' => wp_create_nonce( 'llms-quiz-actions' ),
			'llms_del_quiz' => $quiz->ID,
		) );

		$this->assertEquals( $quiz, $this->controller->maybe_handle_reporting_actions() );

	}

	/**
	 * Test maybe_handle_reporting_actions() success: the quiz's parent course doesn't exist anymore and the quiz can be deleted.
	 *
	 * @since 3.37.8
	 *
	 * @return void
	 */
	public function test_maybe_handle_reporting_actions_no_course() {

		$courses = $this->generate_mock_courses( 1, 1, 1, 1, 1 );
		$lesson  = llms_get_post( llms_get_post( $courses[0] )->get_lessons( 'ids' )[0] );
		$quiz    = $lesson->get_quiz();

		$this->mockPostRequest( array(
			'_llms_quiz_actions_nonce' => wp_create_nonce( 'llms-quiz-actions' ),
			'llms_del_quiz' => $quiz->get( 'id' ),
		) );

		// Now it's attached to an orphaned lesson, we should still be able to delete it.
		$lesson->set( 'parent_course', '' );

		$this->assertEquals( $quiz->post, $this->controller->maybe_handle_reporting_actions() );

	}







}
