<?php
/**
 * Tests for Student Grades controller.
 *
 * @package LifterLMS_Rest/Tests
 *
 * @group REST
 * @group rest_grades
 *
 * @since [version]
 */
class LLMS_REST_Test_Students_Grades_Controller extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route = '/llms/v1/students/(?P<id>[\d]+)/grades';

	/**
	 * Setup our test server, endpoints, and user info.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		$this->endpoint     = new LLMS_REST_Students_Grades_Controller();
		$this->user_student = $this->factory->student->create();
	}

	/**
	 * Get route.
	 *
	 * @since [version]
	 *
	 * @param int $student_id Student identifier.
	 * @return string
	 */
	protected function get_route( $student_id = null ) {

		$student_id = $student_id ? $student_id : $this->user_student;

		return str_replace( '(?P<id>[\d]+)', $student_id, $this->route );
	}

	/**
	 * Test route registration.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_register_routes() {

		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( $this->route, $routes );

		// Read-only.
		foreach ( $routes[ $this->route ] as $endpoint ) {
			$this->assertEquals( array( 'GET' => true ), $endpoint['methods'] );
		}
	}

	/**
	 * Test permissions.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_permissions() {

		// Unauthenticated.
		wp_set_current_user( 0 );
		$response = $this->perform_mock_request( 'GET', $this->get_route() );
		$this->assertResponseStatusEquals( 401, $response );

		// Another user without reporting capabilities.
		wp_set_current_user( $this->user_forbidden );
		$response = $this->perform_mock_request( 'GET', $this->get_route() );
		$this->assertResponseStatusEquals( 403, $response );

		// Students can read their own grades.
		wp_set_current_user( $this->user_student );
		$response = $this->perform_mock_request( 'GET', $this->get_route() );
		$this->assertResponseStatusEquals( 200, $response );

		// Admins can read grades of others.
		wp_set_current_user( $this->user_allowed );
		$response = $this->perform_mock_request( 'GET', $this->get_route() );
		$this->assertResponseStatusEquals( 200, $response );

		// Non-existent student.
		$response = $this->perform_mock_request( 'GET', $this->get_route( 999999 ) );
		$this->assertResponseStatusEquals( 404, $response );
	}

	/**
	 * Test getting grades: course/lesson/quiz grade math.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items() {

		$course_id = $this->factory->course->create(
			array(
				'sections'  => 1,
				'lessons'   => 1,
				'quizzes'   => 1,
				'questions' => 2,
			)
		);
		llms_enroll_student( $this->user_student, $course_id );

		$course  = llms_get_post( $course_id );
		$lessons = $course->get_lessons();
		$lesson  = $lessons[0];
		$quiz_id = absint( $lesson->get( 'quiz' ) );

		wp_set_current_user( $this->user_allowed );

		// Before any grades exist.
		$response = $this->perform_mock_request( 'GET', $this->get_route() );
		$this->assertResponseStatusEquals( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( 1, count( $data ) );

		$item = $data[0];
		$this->assertEquals( $this->user_student, $item['student_id'] );
		$this->assertEquals( $course_id, $item['post_id'] );
		$this->assertNull( $item['grade'] );

		$this->assertEquals( 1, count( $item['lessons'] ) );
		$lesson_data = $item['lessons'][0];
		$this->assertEquals( $lesson->get( 'id' ), $lesson_data['id'] );
		$this->assertEquals( $lesson->get( 'title' ), $lesson_data['title'] );
		$this->assertNull( $lesson_data['grade'] );
		$this->assertEquals( $quiz_id, $lesson_data['quiz']['id'] );
		$this->assertNull( $lesson_data['quiz']['grade'] );
		$this->assertNull( $lesson_data['quiz']['attempt_id'] );

		// Take the quiz with a perfect score.
		$this->take_quiz( $quiz_id, $this->user_student, 100 );

		$response = $this->perform_mock_request( 'GET', $this->get_route() );
		$item     = $response->get_data()[0];

		$this->assertEquals( 100.0, $item['grade'] );

		$lesson_data = $item['lessons'][0];
		$this->assertEquals( 100.0, $lesson_data['grade'] );
		$this->assertEquals( 100.0, $lesson_data['quiz']['grade'] );
		$this->assertTrue( $lesson_data['quiz']['attempt_id'] > 0 );
		$this->assertEquals( 'pass', $lesson_data['quiz']['status'] );
	}

	/**
	 * Test lessons without a quiz have a `null` quiz block.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_no_quiz() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 1,
				'lessons'  => 1,
				'quizzes'  => 0,
			)
		);
		llms_enroll_student( $this->user_student, $course_id );

		wp_set_current_user( $this->user_allowed );
		$response = $this->perform_mock_request( 'GET', $this->get_route() );
		$item     = $response->get_data()[0];

		$this->assertNull( $item['lessons'][0]['quiz'] );
	}

	/**
	 * Test the `course` collection filter and pagination totals.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_items_course_filter() {

		$course_1 = $this->factory->course->create( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 0 ) );
		$course_2 = $this->factory->course->create( array( 'sections' => 1, 'lessons' => 1, 'quizzes' => 0 ) );

		llms_enroll_student( $this->user_student, $course_1 );
		llms_enroll_student( $this->user_student, $course_2 );

		wp_set_current_user( $this->user_allowed );

		// All enrollments.
		$response = $this->perform_mock_request( 'GET', $this->get_route() );
		$this->assertEquals( 2, count( $response->get_data() ) );
		$this->assertEquals( 2, $response->get_headers()['X-WP-Total'] );

		// Filtered to a single course.
		$response = $this->perform_mock_request(
			'GET',
			$this->get_route(),
			array(),
			array(
				'course' => (string) $course_1,
			)
		);
		$data = $response->get_data();
		$this->assertEquals( 1, count( $data ) );
		$this->assertEquals( $course_1, $data[0]['post_id'] );

		// Non-course IDs are ignored.
		$response = $this->perform_mock_request(
			'GET',
			$this->get_route(),
			array(),
			array(
				'course' => (string) $this->factory->post->create(),
			)
		);
		$this->assertEquals( 0, count( $response->get_data() ) );
	}
}
