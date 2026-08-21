<?php
/**
 * Tests for the Quiz Attempts controller.
 *
 * @package LifterLMS_Rest/Tests
 *
 * @group REST
 * @group rest_quiz_attempts
 *
 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
	 * @since [version]
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
}
