<?php
/**
 * Tests for the Quizzes and Questions controllers.
 *
 * Regression coverage for creating quizzes and questions with boolean
 * ("yesno") settings via the REST API / Abilities API.
 *
 * @package LifterLMS_Rest/Tests
 *
 * @group REST
 * @group rest_quizzes
 *
 * @since [version]
 */
class LLMS_REST_Test_Quizzes_Controller extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route = '/llms/v1/quizzes';

	/**
	 * Setup our test server, endpoints, and user info.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->endpoint = new LLMS_REST_Quizzes_Controller();
	}

	/**
	 * Test creating a quiz with boolean settings converts them to "yesno" values.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_item_boolean_settings() {

		wp_set_current_user( $this->user_allowed );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			array(
				'title'               => 'Boolean Settings Quiz',
				'content'             => 'A quiz with boolean settings.',
				'limit_attempts'      => true,
				'allowed_attempts'    => 3,
				'limit_time'          => true,
				'time_limit'          => 45,
				'show_correct_answer' => true,
				'random_questions'    => false,
				'passing_percent'     => 80,
			)
		);

		$this->assertResponseStatusEquals( 201, $response );

		$data = $response->get_data();

		// Defaults to published when status is omitted.
		$this->assertEquals( 'publish', $data['status'] );

		$this->assertTrue( $data['limit_attempts'] );
		$this->assertTrue( $data['limit_time'] );
		$this->assertTrue( $data['show_correct_answer'] );
		$this->assertFalse( $data['random_questions'] );
		$this->assertEquals( 3, $data['allowed_attempts'] );
		$this->assertEquals( 45, $data['time_limit'] );
		$this->assertEquals( 80, $data['passing_percent'] );

		// Stored as "yesno" strings on the model.
		$quiz = llms_get_post( $data['id'] );
		$this->assertEquals( 'yes', $quiz->get( 'limit_attempts' ) );
		$this->assertEquals( 'yes', $quiz->get( 'limit_time' ) );
		$this->assertEquals( 'yes', $quiz->get( 'show_correct_answer' ) );
		$this->assertEquals( 'no', $quiz->get( 'random_questions' ) );
	}

	/**
	 * Test create without attempt limiting does not return a default allowed_attempts.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_item_unlimited_attempts() {

		wp_set_current_user( $this->user_allowed );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			array(
				'title'   => 'Unlimited Attempts Quiz',
				'content' => 'No attempt limit.',
			)
		);

		$this->assertResponseStatusEquals( 201, $response );

		$data = $response->get_data();
		$this->assertFalse( $data['limit_attempts'] );
		$this->assertNull( $data['allowed_attempts'] );
		$this->assertFalse( $data['limit_time'] );
		$this->assertNull( $data['time_limit'] );
	}

	/**
	 * Test questions are listed by menu_order and auto-sequenced on create.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_questions_ordered_by_menu_order() {

		wp_set_current_user( $this->user_allowed );

		$quiz_id = $this->perform_mock_request(
			'POST',
			$this->route,
			array(
				'title'   => 'Ordered Quiz',
				'content' => 'Questions should follow menu_order.',
			)
		)->get_data()['id'];

		$first = $this->perform_mock_request(
			'POST',
			'/llms/v1/questions',
			array(
				'title'     => 'First question',
				'parent_id' => $quiz_id,
			)
		);
		$this->assertResponseStatusEquals( 201, $first );
		$this->assertEquals( 1, $first->get_data()['menu_order'] );

		$second = $this->perform_mock_request(
			'POST',
			'/llms/v1/questions',
			array(
				'title'     => 'Second question',
				'parent_id' => $quiz_id,
			)
		);
		$this->assertResponseStatusEquals( 201, $second );
		$this->assertEquals( 2, $second->get_data()['menu_order'] );

		$list = $this->perform_mock_request( 'GET', '/llms/v1/quizzes/' . $quiz_id . '/questions' );
		$this->assertResponseStatusEquals( 200, $list );

		$ids = wp_list_pluck( $list->get_data(), 'id' );
		$this->assertEquals(
			array( $first->get_data()['id'], $second->get_data()['id'] ),
			$ids
		);
	}

	/**
	 * Test creating a question with boolean-backed settings.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_create_question_boolean_settings() {

		wp_set_current_user( $this->user_allowed );

		$quiz_id = $this->perform_mock_request(
			'POST',
			$this->route,
			array(
				'title'   => 'Parent Quiz',
				'content' => 'Parent quiz description.',
			)
		)->get_data()['id'];

		$response = $this->perform_mock_request(
			'POST',
			'/llms/v1/questions',
			array(
				'title'          => 'Pick all that apply',
				'content'        => 'Some description of the question.',
				'parent_id'      => $quiz_id,
				'question_type'  => 'choice',
				'multi_choices'  => true,
				'clarifications' => '<p>Both A and B are correct.</p>',
				'video_src'      => 'https://example.tld/video',
				'choices'        => array(
					array(
						'choice'  => 'Choice A',
						'correct' => true,
					),
					array(
						'choice'  => 'Choice B',
						'correct' => true,
					),
				),
			)
		);

		$this->assertResponseStatusEquals( 201, $response );

		$data = $response->get_data();

		$this->assertTrue( $data['multi_choices'] );
		$this->assertEquals( '<p>Both A and B are correct.</p>', $data['clarifications'] );
		$this->assertEquals( 'https://example.tld/video', $data['video_src'] );
		$this->assertEquals( 2, count( $data['choices'] ) );

		// Stored as "yesno" strings on the model, with enabled flags derived from content.
		$question = llms_get_post( $data['id'] );
		$this->assertEquals( 'yes', $question->get( 'multi_choices' ) );
		$this->assertEquals( 'yes', $question->get( 'clarifications_enabled' ) );
		$this->assertEquals( 'yes', $question->get( 'video_enabled' ) );
		$this->assertEquals( 'yes', $question->get( 'description_enabled' ) );
	}
}
