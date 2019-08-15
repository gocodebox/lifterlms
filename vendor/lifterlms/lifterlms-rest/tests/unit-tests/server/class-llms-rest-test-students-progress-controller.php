<?php
/**
 * Tests for Student Progress controller.
 *
 * @package LifterLMS_Rest/Tests
 *
 * @group REST
 * @group rest_progress
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_Students_Progress_Controller extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Route.
	 *
	 * @var string
	 */
	private $route = '/llms/v1/students/(?P<id>[\d]+)/progress';

	/**
	 * Setup our test server, endpoints, and user info.
	 */
	public function setUp() {

		parent::setUp();

		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_user_postmeta" );

		$this->endpoint = new LLMS_REST_Students_Progress_Controller();

		$this->user_allowed = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$this->user_forbidden = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$this->user_student = $this->factory->student->create();

	}

	private function get_route( $student_id, $post_id = null ) {
		$route = str_replace( '(?P<id>[\d]+)', $student_id, $this->route );
		if ( $post_id ) {
			$route .= '/' . $post_id;
		}
		return $route;
	}

	/**
	 * Test route registration.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_register_routes() {

		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( $this->route . '/(?P<post_id>[\d]+)', $routes );

	}

	public function test_delete_item() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );
		$route = $this->get_route( $this->user_student, $course );
		llms_enroll_student( $this->user_student, $course );
		$student = llms_get_student( $this->user_student );

		wp_set_current_user( $this->user_allowed );
		$response = $this->perform_mock_request( 'DELETE', $route );
		$this->assertResponseStatusEquals( 204, $response );

		// Mark the course complete.
		$response = $this->perform_mock_request( 'POST', $route, array(
			'status' => 'complete'
		) );

		$response = $this->perform_mock_request( 'DELETE', $route );
		$this->assertResponseStatusEquals( 204, $response );

		$this->assertEquals( 0, $student->get_progress( $course, 'course' ) );

	}

	public function test_get_item_errors() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );
		$route = $this->get_route( $this->user_student, $course );
		llms_enroll_student( $this->user_student, $course );

		// Unauthorized.
		$response = $this->perform_mock_request( 'GET', $route );
		$this->assertResponseStatusEquals( 401, $response );
		$this->assertResponseCodeEquals( 'llms_rest_unauthorized_request', $response );

		// Forbidden.
		wp_set_current_user( $this->user_forbidden );
		$response = $this->perform_mock_request( 'GET', $route );
		$this->assertResponseStatusEquals( 403, $response );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $response );

	}

	public function test_get_item_course() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );
		$route = $this->get_route( $this->user_student, $course );
		llms_enroll_student( $this->user_student, $course );

		wp_set_current_user( $this->user_allowed );
		$response = $this->perform_mock_request( 'GET', $route );
		$this->assertResponseStatusEquals( 200, $response );
		$data = $response->get_data();

		$this->assertEquals( (float) 0, $data['progress'] );
		$this->assertEquals( 'incomplete', $data['status'] );
		$this->assertEquals( $course, $data['post_id'] );
		$this->assertEquals( $this->user_student, $data['student_id'] );
		$this->assertNull( $data['date_created'] );
		$this->assertNull( $data['date_updated'] );

	}

	public function test_update_item_errors() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );
		$route = $this->get_route( $this->user_student, $course );
		llms_enroll_student( $this->user_student, $course );

		// Missing required params.
		$response = $this->perform_mock_request( 'POST', $route );
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseCodeEquals( 'rest_missing_callback_param', $response );

		$args = array(
			'status' => 'fake',
		);

		// Invalid status.
		$response = $this->perform_mock_request( 'POST', $route, $args );
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseCodeEquals( 'rest_invalid_param', $response );

		$args['status'] = 'complete';

		// Unauthorized.
		$response = $this->perform_mock_request( 'POST', $route, $args );
		$this->assertResponseStatusEquals( 401, $response );
		$this->assertResponseCodeEquals( 'llms_rest_unauthorized_request', $response );

		// Forbidden.
		wp_set_current_user( $this->user_forbidden );
		$response = $this->perform_mock_request( 'POST', $route, $args );
		$this->assertResponseStatusEquals( 403, $response );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $response );

	}

	public function test_update_item_course() {

		$student = llms_get_student( $this->user_student );
		$course = $this->factory->course->create( array( 'sections' => 1 ) );
		$route = $this->get_route( $this->user_student, $course );
		llms_enroll_student( $this->user_student, $course );

		// Mark course complete.
		wp_set_current_user( $this->user_allowed );
		$response = $this->perform_mock_request( 'POST', $route, array(
			'status' => 'complete'
		) );
		$this->assertResponseStatusEquals( 200, $response );
		$data = $response->get_data();

		$this->assertEquals( (float) 100, $data['progress'] );
		$this->assertEquals( 'complete', $data['status'] );
		$this->assertEquals( $course, $data['post_id'] );
		$this->assertEquals( $this->user_student, $data['student_id'] );
		$this->assertTrue( ! empty( $data['date_created'] ) );
		$this->assertTrue( ! empty( $data['date_updated'] ) );

		$this->assertEquals( array( 'self', 'post', 'student' ), array_keys( $response->get_links() ) );

		$this->assertEquals( (float) 100, $student->get_progress( $course, 'course' ) );

		// Mark Incomplete.
		$response = $this->perform_mock_request( 'POST', $route, array(
			'status' => 'incomplete'
		) );
		$this->assertResponseStatusEquals( 200, $response );
		$data = $response->get_data();
		$this->assertEquals( (float) 0, $data['progress'] );
		$this->assertEquals( 'incomplete', $data['status'] );

		$this->assertEquals( (float) 0, $student->get_progress( $course, 'course' ) );

	}

	public function test_update_item_section() {

		$student = llms_get_student( $this->user_student );
		$course = llms_get_post( $this->factory->course->create( array( 'sections' => 1, 'lessons' => 3 ) ) );
		$section = $course->get_sections( 'ids' )[0];
		$route = $this->get_route( $this->user_student, $section );
		llms_enroll_student( $this->user_student, $course->get( 'id' ) );

		// Mark course complete.
		wp_set_current_user( $this->user_allowed );
		$response = $this->perform_mock_request( 'POST', $route, array(
			'status' => 'complete'
		) );
		$this->assertResponseStatusEquals( 200, $response );
		$data = $response->get_data();

		$this->assertEquals( (float) 100, $data['progress'] );
		$this->assertEquals( 'complete', $data['status'] );
		$this->assertEquals( $section, $data['post_id'] );
		$this->assertEquals( $this->user_student, $data['student_id'] );
		$this->assertTrue( ! empty( $data['date_created'] ) );
		$this->assertTrue( ! empty( $data['date_updated'] ) );

		$this->assertEquals( array( 'self', 'post', 'student' ), array_keys( $response->get_links() ) );

		$this->assertEquals( (float) 100, $student->get_progress( $section, 'section' ) );

		// Mark Incomplete.
		$response = $this->perform_mock_request( 'POST', $route, array(
			'status' => 'incomplete'
		) );
		$this->assertResponseStatusEquals( 200, $response );
		$data = $response->get_data();
		$this->assertEquals( (float) 0, $data['progress'] );
		$this->assertEquals( 'incomplete', $data['status'] );

		$this->assertEquals( (float) 0, $student->get_progress( $section, 'section' ) );

	}

	public function test_update_item_lesson() {

		$student = llms_get_student( $this->user_student );
		$course = llms_get_post( $this->factory->course->create( array( 'sections' => 1, 'lessons' => 3 ) ) );
		$lesson = $course->get_lessons( 'ids' )[0];
		$route = $this->get_route( $this->user_student, $lesson );
		llms_enroll_student( $this->user_student, $course->get( 'id' ) );

		// Mark course complete.
		wp_set_current_user( $this->user_allowed );
		$response = $this->perform_mock_request( 'POST', $route, array(
			'status' => 'complete'
		) );
		$this->assertResponseStatusEquals( 200, $response );
		$data = $response->get_data();

		$this->assertEquals( (float) 100, $data['progress'] );
		$this->assertEquals( 'complete', $data['status'] );
		$this->assertEquals( $lesson, $data['post_id'] );
		$this->assertEquals( $this->user_student, $data['student_id'] );
		$this->assertTrue( ! empty( $data['date_created'] ) );
		$this->assertTrue( ! empty( $data['date_updated'] ) );

		$this->assertEquals( array( 'self', 'post', 'student' ), array_keys( $response->get_links() ) );

		$this->assertTrue( llms_is_complete( $this->user_student, $lesson, 'lesson' ) );

		// Mark Incomplete.
		$response = $this->perform_mock_request( 'POST', $route, array(
			'status' => 'incomplete'
		) );
		$this->assertResponseStatusEquals( 200, $response );
		$data = $response->get_data();
		$this->assertEquals( (float) 0, $data['progress'] );
		$this->assertEquals( 'incomplete', $data['status'] );

		$this->assertFalse( llms_is_complete( $this->user_student, $lesson, 'lesson' ) );

	}

	public function test_validate_date_created() {

		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$course_id = $course->get( 'id' );
		$request = new WP_REST_Request( 'POST', $this->get_route( $this->user_student, $course_id ) );

		$res = $this->endpoint->validate_date_created( date( DATE_RFC3339, strtotime( '+1 day' ) ), $request, 'date_created' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_rest_bad_request', $res );
		$this->assertWPErrorMessageEquals( 'Created date cannot be in the future.', $res );

		$this->assertTrue( $this->endpoint->validate_date_created( date( DATE_RFC3339, strtotime( '-1 day' ) ), $request, 'date_created' ) );

	}

	public function test_validate_post_id() {

		$post = $this->factory->post->create();
		$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 1 ) );
		$course_id = $course->get( 'id' );
		$request = new WP_REST_Request( 'POST', $this->get_route( $this->user_student, $course_id ) );
		$request->set_url_params( array( 'id' => $this->user_student ) );

		// Post doesn't exist.
		$this->assertFalse( $this->endpoint->validate_post_id( $post + 1, $request, 'post_id' ) );

		// Post type isn't supported.
		$this->assertFalse( $this->endpoint->validate_post_id( $post, $request, 'post_id' ) );

		// User isn't enrolled.
		$this->assertFalse( $this->endpoint->validate_post_id( $course_id, $request, 'post_id' ) );

		llms_enroll_student( $this->user_student, $course_id );

		// Valid course.
		$this->assertTrue( $this->endpoint->validate_post_id( $course_id, $request, 'post_id' ) );

		// Valid section.
		$this->assertTrue( $this->endpoint->validate_post_id( $course->get_sections( 'ids' )[0], $request, 'post_id' ) );

		// Valid lesson.
		$this->assertTrue( $this->endpoint->validate_post_id( $course->get_lessons( 'ids' )[0], $request, 'post_id' ) );

	}

}
