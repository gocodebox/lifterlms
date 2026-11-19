<?php
/**
 * Tests for the Quiz Attempts controller.
 *
 * @package LifterLMS_Rest/Tests
 *
 * @group REST
 * @group rest_quiz_attempts
 *
 * @since 10.2.0
 */
class LLMS_REST_Test_Quiz_Attempts_Controller extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route = '/llms/v1/quiz-attempts';

	/**
	 * Setup our test server, endpoints, and user info.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->endpoint = new LLMS_REST_Quiz_Attempts_Controller();
	}

	/**
	 * Create an attachment post with an attached file meta.
	 *
	 * @since 10.2.0
	 *
	 * @param bool $protected Whether to add protection meta to the attachment.
	 * @return int Attachment post ID.
	 */
	private function create_test_attachment( $protected = false ) {

		$attachment_id = $this->factory->post->create(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'application/pdf',
			)
		);
		update_post_meta( $attachment_id, '_wp_attached_file', '2026/08/essay.pdf' );

		if ( $protected ) {
			update_post_meta( $attachment_id, LLMS_Media_Protector::AUTHORIZATION_FILTER_KEY, 'llms_attachment_is_access_allowed' );
		}

		return $attachment_id;
	}

	/**
	 * Build a mock attempt question whose answer resolves to the given values.
	 *
	 * @since 10.2.0
	 *
	 * @param array $answer Raw answer values.
	 * @return LLMS_Quiz_Attempt_Question
	 */
	private function mock_attempt_question( $answer ) {

		$question = $this->createMock( LLMS_Quiz_Attempt_Question::class );
		$question->method( 'get' )->with( 'answer' )->willReturn( $answer );

		return $question;
	}

	/**
	 * Test that unprotected upload answers get a download_url matching the plain url.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_get_answer_files_unprotected() {

		$attachment_id = $this->create_test_attachment();

		$files = LLMS_Unit_Test_Util::call_method(
			$this->endpoint,
			'get_answer_files',
			array( $this->mock_attempt_question( array( (string) $attachment_id ) ) )
		);

		$this->assertCount( 1, $files );
		$this->assertEquals( $attachment_id, $files[0]['id'] );
		$this->assertEquals( wp_get_attachment_url( $attachment_id ), $files[0]['url'] );
		$this->assertEquals( $files[0]['url'], $files[0]['download_url'] );
		$this->assertEquals( 'essay.pdf', $files[0]['filename'] );
		$this->assertEquals( 'application/pdf', $files[0]['mime_type'] );
	}

	/**
	 * Test that protected upload answers get a signed, expiring download_url.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_get_answer_files_protected() {

		$attachment_id = $this->create_test_attachment( true );

		$files = LLMS_Unit_Test_Util::call_method(
			$this->endpoint,
			'get_answer_files',
			array( $this->mock_attempt_question( array( (string) $attachment_id ) ) )
		);

		$this->assertCount( 1, $files );
		$this->assertStringContainsString( LLMS_Media_Protector::URL_PARAMETER_TOKEN . '=', $files[0]['download_url'] );
		$this->assertStringContainsString( LLMS_Media_Protector::URL_PARAMETER_EXPIRES . '=', $files[0]['download_url'] );
		$this->assertStringContainsString( LLMS_Media_Protector::URL_PARAMETER_ID . '=' . $attachment_id, $files[0]['download_url'] );
		$this->assertNotEquals( $files[0]['url'], $files[0]['download_url'] );
	}

	/**
	 * Test that non-attachment answer values produce no file data.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_get_answer_files_non_attachment_answers() {

		$files = LLMS_Unit_Test_Util::call_method(
			$this->endpoint,
			'get_answer_files',
			array( $this->mock_attempt_question( array( 'a free text answer', '999999999' ) ) )
		);

		$this->assertSame( array(), $files );
	}

	/**
	 * An instructor's attempt total does not include attempts on quizzes they cannot edit.
	 *
	 * The student filter, a missing student, and an unscoped list must all count
	 * only readable quizzes. An LMS manager still receives the full total.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function test_get_items_instructor_total_matches_readable_attempts() {

		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$other      = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$manager    = $this->factory->user->create( array( 'role' => 'lms_manager' ) );
		$hidden     = $this->factory->student->create();
		$student    = $this->factory->student->create();

		$hidden_quiz = $this->create_quiz_post( $other );
		$own_quiz    = $this->create_quiz_post( $instructor );

		$this->create_quiz_attempt( $hidden_quiz, $hidden );
		$this->create_quiz_attempt( $hidden_quiz, $student );
		$own_attempt = $this->create_quiz_attempt( $own_quiz, $student );

		wp_set_current_user( $instructor );

		$foreign = $this->perform_mock_request( 'GET', $this->route, array(), array( 'student' => $hidden ) );
		$this->assert_attempt_total( $foreign, 0, array() );

		$missing = $this->perform_mock_request( 'GET', $this->route, array(), array( 'student' => 999999999 ) );
		$this->assert_attempt_total( $missing, 0, array() );

		$by_student = $this->perform_mock_request( 'GET', $this->route, array(), array( 'student' => $student ) );
		$this->assert_attempt_total( $by_student, 1, array( $own_attempt ) );

		$by_hidden_quiz = $this->perform_mock_request( 'GET', $this->route, array(), array( 'quiz' => $hidden_quiz ) );
		$this->assert_attempt_total( $by_hidden_quiz, 0, array() );

		$unscoped = $this->perform_mock_request( 'GET', $this->route );
		$this->assert_attempt_total( $unscoped, 1, array( $own_attempt ) );

		wp_set_current_user( $manager );

		$manager_response = $this->perform_mock_request( 'GET', $this->route, array(), array( 'student' => $hidden ) );
		$this->assertResponseStatusEquals( 200, $manager_response );
		$this->assertEquals( 1, $manager_response->get_headers()['X-WP-Total'] );
		$this->assertCount( 1, $manager_response->get_data() );
	}

	/**
	 * An instructor sees attempts on a quiz they do not author when it belongs to a course they instruct.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function test_get_items_instructor_includes_attempt_on_course_quiz() {

		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$author     = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$student    = $this->factory->student->create();

		$course = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course->instructors()->set_instructors( array( array( 'id' => $instructor ) ) );

		$lesson_id = $this->factory->post->create(
			array(
				'post_type'   => 'lesson',
				'post_status' => 'publish',
				'post_author' => $author,
			)
		);
		update_post_meta( $lesson_id, '_llms_parent_course', $course->get( 'id' ) );

		$quiz_id = $this->create_quiz_post( $author );
		update_post_meta( $quiz_id, '_llms_lesson_id', $lesson_id );
		update_post_meta( $lesson_id, '_llms_quiz', $quiz_id );

		$attempt_id = $this->create_quiz_attempt( $quiz_id, $student );

		wp_set_current_user( $instructor );

		$by_student = $this->perform_mock_request( 'GET', $this->route, array(), array( 'student' => $student ) );
		$this->assert_attempt_total( $by_student, 1, array( $attempt_id ) );

		$by_lesson = $this->perform_mock_request( 'GET', $this->route, array(), array( 'lesson' => $lesson_id ) );
		$this->assert_attempt_total( $by_lesson, 1, array( $attempt_id ) );
	}

	/**
	 * Test that an attempt's score and late penalty are returned alongside the final grade.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_item_late_penalty() {

		$admin   = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$attempt = new LLMS_Quiz_Attempt( $this->create_quiz_attempt( $this->create_quiz_post( $admin ), $this->factory->student->create() ) );
		$attempt->set( 'status', 'fail' );
		$attempt->set( 'grade', 60 );
		$attempt->set( 'late_penalty', 20 );
		$attempt->set( 'late_penalty_note', 'Submitted late.' );
		$attempt->save();

		wp_set_current_user( $admin );
		$response = $this->perform_mock_request( 'GET', $this->route . '/' . $attempt->get( 'id' ) );

		$this->assertResponseStatusEquals( 200, $response );
		$data = $response->get_data();
		$this->assertEquals( 60, $data['grade'] );
		$this->assertEquals( 80, $data['score'] );
		$this->assertEquals( 20, $data['late_penalty'] );
		$this->assertSame( 'Submitted late.', $data['late_penalty_note'] );
	}

	/**
	 * Assert a collection response status, total, and attempt ids.
	 *
	 * @since 11.0.0
	 *
	 * @param WP_REST_Response $response   Response object.
	 * @param int              $total      Expected X-WP-Total and body length.
	 * @param int[]            $attempt_ids Expected attempt ids in the body.
	 * @return void
	 */
	private function assert_attempt_total( $response, $total, $attempt_ids ) {

		$this->assertResponseStatusEquals( 200, $response );
		$this->assertEquals( $total, $response->get_headers()['X-WP-Total'] );
		$this->assertEquals( $attempt_ids, wp_list_pluck( $response->get_data(), 'id' ) );
	}

	/**
	 * Create a published quiz authored by the given user.
	 *
	 * @since 11.0.0
	 *
	 * @param int $author Author WP_User ID.
	 * @return int Quiz post ID.
	 */
	private function create_quiz_post( $author ) {

		return $this->factory->post->create(
			array(
				'post_type'   => 'llms_quiz',
				'post_status' => 'publish',
				'post_author' => $author,
			)
		);
	}

	/**
	 * Insert a quiz attempt for a student.
	 *
	 * @since 11.0.0
	 *
	 * @param int $quiz_id    Quiz post ID.
	 * @param int $student_id Student WP_User ID.
	 * @return int Attempt ID.
	 */
	private function create_quiz_attempt( $quiz_id, $student_id ) {

		$attempt = LLMS_Quiz_Attempt::init( $quiz_id, 0, $student_id );
		$attempt->save();

		return (int) $attempt->get( 'id' );
	}
}
