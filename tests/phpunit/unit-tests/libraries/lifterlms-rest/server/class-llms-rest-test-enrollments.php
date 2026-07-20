<?php
/**
 * Tests for Enrollments API
 *
 * @package LifterLMS_Rest/Tests
 *
 * @group REST
 * @group rest_enrollments
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Test_Enrollments extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route = '/llms/v1/students/(?P<id>[\d]+)/enrollments';

	/**
	 * Consider dates equal for +/- 1 mins
	 *
	 * @var integer
	 */
	private $date_delta = 60;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	protected $object_type = 'students-enrollments';

	/**
	 * Array of link $rels expected for each item.
	 *
	 * @var array
	 */
	private $expected_link_rels = array( 'self', 'collection', 'student', 'post' );

	/**
	 * Update method.
	 *
	 * @var string
	 */
	protected $update_method = 'PATCH';

	/**
	 * Setup our test server, endpoints, and user info.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.27 Users creation moved in the `parent::set_up()`.
	 *
	 * @return void.
	 */
	public function set_up() {
		parent::set_up();

		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->prefix}lifterlms_user_postmeta" );
		$this->endpoint = new LLMS_REST_Enrollments_Controller();
		$this->user_student = $this->factory->user->create( array( 'role' => 'subscriber' ) );
	}

	/**
	 * Test route registration.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_register_routes() {

		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( $this->route, $routes );
		$this->assertArrayHasKey( $this->route . '/(?P<post_id>[\d]+)', $routes );

	}

	/**
	 * Test list student enrollments.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_get_enrollments() {

		wp_set_current_user( $this->user_allowed );

		$user_id = $this->user_student;

		// Create new courses.
		$course_ids = $this->factory->post->create_many( 5, array( 'post_type' => 'course' ) );

		foreach ( $course_ids as $course_id ) {
			// Enroll Student in newly created course.
			llms_enroll_student( $user_id, $course_id, 'test_get_enrollments' );
		}

		$response = $this->perform_mock_request( 'GET',  $this->parse_route( $user_id ) );

		// Success.
		$this->assertResponseStatusEquals( 200, $response );
		$res_data = $response->get_data();

		// Expect 5 enrollments.
		$this->assertEquals( 5, count( $res_data ) );

		// Check enrollments post_id.
		$i = 0;
		foreach ( $res_data as $enrollment ) {
			$this->assertEquals( $course_ids[$i], $res_data[$i]['post_id'] );
			// Make sure post_id and student_id are integers.
			$this->assertIsInt( $res_data[$i]['post_id'] );
			$this->assertIsInt( $res_data[$i]['student_id'] );
			$i++;
		}

	}

	/**
	 * Test list student enrollments pagination.
	 *
	 * @since 1.0.0-beta.3
	 * @since 1.0.0-beta.11 Fixed pagination test taking into account course post revisions.
	 */
	public function test_get_enrollments_pagination() {

		wp_set_current_user( $this->user_allowed );

		// Create enrollments.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Create new courses.
		$course_ids      = $this->factory->post->create_many( 25, array( 'post_type' => 'course' ) );
		$start_course_id = $course_ids[0];

		foreach ( $course_ids as $course_id ) {
			// Enroll Student in newly created course.
			llms_enroll_student( $user_id, $course_id, 'test_get_enrollments_pagination' );
		}

		$route = $this->parse_route( $user_id );

		$this->pagination_test( $route, $start_course_id, 10, 'post_id', $total = 25, $ids_step = 2 );
	}

	/**
	 * Test list student enrollments filter by post_id.
	 *
	 * @since 1.0.0-beta.1
	 */
    public function test_get_enrollments_filter_post() {

		wp_set_current_user( $this->user_allowed );

		// Create enrollments.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Create new courses.
		$course_ids = $this->factory->post->create_many( 10, array( 'post_type' => 'course' ) );

		$j = 0;
		$courses = array();
		foreach ( $course_ids as $course_id ) {
			if ( 0 === ( $j++ % 2 ) ) {
				// Enroll Student in newly created course.
				llms_enroll_student( $user_id, $course_id, 'test_filter_enrollments' );
				$courses[] = $course_id;
			}
		}

		$response = $this->perform_mock_request( 'GET', $this->parse_route( $user_id ), array(), array( 'post' =>  "$courses[1],$courses[2]" ) );

	    // Success.
	    $this->assertResponseStatusEquals( 200, $response );
	    $res_data = $response->get_data();

	    // Expect 2 enrollments.
	    $this->assertEquals( 2, count( $res_data ) );

	    // Check enrollments post_id.
	    $i = 0;
	    foreach ( $res_data as $enrollment ) {
			$this->assertEquals( $courses[$i+1], $res_data[$i++]['post_id'] );
		}

	}

	/**
	 * Test getting current user enrollments permissions.
	 *
	 * @since 1.0.0-beta.4
	 */
	public function test_get_current_user_enrollments_permissions() {

		// Create an user.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		// Setup course.
		$course_id = $this->factory->course->create();
		wp_set_current_user( $this->user_allowed );
		llms_enroll_student( $user_id, $course_id, 'test_get_current_user_enrollments' );

		wp_set_current_user( $user_id );

		// Check we can list our own enrollments.
		$response = $this->perform_mock_request( 'GET', $this->parse_route( $user_id ) );

		// Check we have permissions to make this request.
		$this->assertNotEquals( 403, $response->get_status() );
		// And that the list of enrollments contains the enrolled course.
		$enrollments = $response->get_data();
		$this->assertEquals( $course_id, $enrollments[0]['post_id'] );

		// Check we can get our own single enrollment.
		$response = $this->perform_mock_request( 'GET', $this->parse_route( $user_id ) . '/' . $course_id );

		// Check we have permissions to make this request.
		$this->assertNotEquals( 403, $response->get_status() );
		// And that the list of enrollments contains the enrolled course.
		$enrollment = $response->get_data();
		$this->assertEquals( $course_id, $enrollment['post_id'] );

	}

	/**
	 * Test getting enrollments without permission.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_get_enrollments_without_permission() {

		wp_set_current_user( 0 );

		// Setup course.
		$this->factory->course->create();

		$response = $this->perform_mock_request( 'GET',  $this->parse_route( 1 ) );
		// Check we don't have permissions to make this request.
		$this->assertResponseStatusEquals( 401, $response );

	}

	/**
	 * Test getting enrollments: forbidden request.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_get_enrollments_forbidden() {

		wp_set_current_user( $this->user_forbidden );

		// Setup course.
		$this->factory->course->create();

		$response = $this->perform_mock_request( 'GET',  $this->parse_route( 1 ) );

		// Check we're not allowed to get results.
		$this->assertResponseStatusEquals( 403, $response );

	}

	/**
	 * Test get single student enrollment
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.10 Added test on the trigger property.
	 * @since 1.0.0-beta.16 Compare dates as timestamps.
	 */
    public function test_get_enrollment() {

		wp_set_current_user( $this->user_allowed );

		// Create enrollment.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Create new courses.
		$course_id = $this->factory->post->create_many( 2, array( 'post_type' => 'course' ) );
		$date_now  = date( 'Y-m-d H:i:s' );
		llms_enroll_student( $user_id, $course_id[0], 'test_get_enrollment' );

		$response = $this->perform_mock_request( 'GET',  $this->parse_route( $user_id )  . '/' . $course_id[0] );

	    // Success.
	    $this->assertResponseStatusEquals( 200, $response );
	    $res_data = $response->get_data();

		// Check:
		$this->assertEquals( $user_id, $res_data['student_id'] );
		$this->assertEquals( $course_id[0], $res_data['post_id'] );
		$this->assertEquals( 'enrolled', $res_data['status'] );
		$this->assertEqualsWithdelta( strtotime( $date_now ), strtotime( $res_data['date_created'] ), $this->date_delta );
		$this->assertEquals( $res_data['date_created'], $res_data['date_updated'] );

		$student = new LLMS_Student($user_id);
		$this->assertEquals( $res_data['status'], $student->get_enrollment_status( $course_id[0] ) );
		$this->assertEquals( $res_data['date_created'], $student->get_enrollment_date( $course_id[0], 'enrolled', 'Y-m-d H:i:s' ) );
		$this->assertEquals( $res_data['date_updated'], $student->get_enrollment_date( $course_id[0], 'updated', 'Y-m-d H:i:s' ) );
		$this->assertEquals( $res_data['trigger'], $student->get_enrollment_trigger( $course_id[0] ) );

	}

	/**
	 * Test getting enrollment without permission.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_get_enrollment_without_permission() {

		wp_set_current_user( $this->user_allowed );

		// Create enrollment.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		// Create new courses.
		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		llms_enroll_student( $user_id, $course_id, 'test_get_enrollment_noperm' );

		wp_set_current_user( 0 );

		// Setup course.
		$this->factory->course->create();

		$response = $this->perform_mock_request( 'GET',  $this->parse_route( $user_id )  . '/' . $course_id );

		// Check we don't have permissions to make this request.
		$this->assertResponseStatusEquals( 401, $response );

	}

	/**
	 * Test getting enrollment: forbidden request.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_get_enrollment_forbidden() {

		wp_set_current_user( $this->user_forbidden );

		// Create enrollment.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		// Create new courses.
		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		llms_enroll_student( $user_id, $course_id, 'test_get_enrollment_forbidden' );

		// Setup course.
		$this->factory->course->create();

		$response = $this->perform_mock_request( 'GET',  $this->parse_route( $user_id )  . '/' . $course_id );

		// Check we're not allowed to get results.
		$this->assertResponseStatusEquals( 403, $response );

	}

	/**
	 * Get resource creation args.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return array
	 */
	public function get_creation_args() {
		return array(
			'post_id' => $this->factory->course->create( array( 'sections' => 1, 'lessons' => 1 ) ),
		);
	}

	/**
	 * Test create enrollment.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.10 Added test on the trigger property.
	 * @since 1.0.0-beta.16 Compare dates as timestamps.
	 */
	public function test_create_enrollment() {

		wp_set_current_user( $this->user_allowed );

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$user_id   = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$response = $this->perform_mock_request( 'POST',  $this->parse_route( $user_id )  . '/' . $course_id );
		$date_now  = date( 'Y-m-d H:i:s' );

		// Success.
		$this->assertResponseStatusEquals( 201, $response );
		$res_data = $response->get_data();

		// Check:
		$this->assertEquals( $user_id, $res_data['student_id'] );
		$this->assertEquals( $course_id, $res_data['post_id'] );
		$this->assertEquals( 'enrolled', $res_data['status'] );
		$this->assertEqualsWithDelta( strtotime( $date_now ), strtotime( $res_data['date_created'] ), $this->date_delta );
		$this->assertEquals( $res_data['date_created'], $res_data['date_updated'] );
		$this->assertEquals( 'unspecified', $res_data['trigger'] );

		// Links.
		$this->assertEquals( $this->expected_link_rels, array_keys( $response->get_links() ) );

	}

	/**
	 * Test producing bad request error when creating a single enrollment.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_create_enrollment_bad_request() {

		wp_set_current_user( $this->user_allowed );

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$user_id   = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Bad request: post is not enrollable.
		$lesson_id = $this->factory->post->create( array( 'post_type' => 'lesson' ) );
		$response = $this->perform_mock_request( 'POST',  $this->parse_route( $user_id )  . '/' . $lesson_id );

		$this->assertResponseStatusEquals( 400, $response );

		// Invalid date.
		llms_enroll_student( $user_id, $course_id );
		$response = $this->perform_mock_request( $this->update_method,  $this->parse_route( $user_id ) . '/' . $course_id, array( 'date_created' => 'some_invalid_date' ) );
		$this->assertResponseStatusEquals( 400, $response );

	}

	/**
	 * Test producing 404 error when creating a single enrollment.
	 *
	 * @since 1.0.0-beta.26
	 * @return void
	 */
	public function test_create_enrollment_404() {

		wp_set_current_user( $this->user_allowed );

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		$response = $this->perform_mock_request( 'POST',  $this->parse_route( 0 )  . '/' . $course_id );
		$date_now = date( 'Y-m-d H:i:s' );

		$this->assertResponseStatusEquals( 404, $response );

	}

	/**
	 * Test update enrollment status.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.10 Added test on the trigger property/arg.
	 * @since 1.0.0-beta.26 Fixed expected trigger.
	 */
	public function test_update_enrollment_status() {

		wp_set_current_user( $this->user_allowed );

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$user_id   = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Enroll Student in newly created course/membership
		llms_enroll_student( $user_id, $course_id, 'test_update_status' );

		sleep(1); //<- to be sure the new status is subsequent the one set on creation.

		$response = $this->perform_mock_request( $this->update_method,  $this->parse_route( $user_id ) . '/' . $course_id, array( 'status' => 'expired' ) );

		// Success.
		$this->assertResponseStatusEquals( 200, $response );
		$res_data = $response->get_data();

		// Check:
		$this->assertEquals( $user_id, $res_data['student_id'] );
		$this->assertEquals( $course_id, $res_data['post_id'] );
		$this->assertEquals( 'expired', $res_data['status'] );
		$student = new LLMS_Student( $user_id );
		$this->assertEquals( $res_data['status'], $student->get_enrollment_status( $course_id, false ) );

		// Enroll and check the trigger is admin_{$this->user_allowed}.
		// Clean:
		$student->delete_enrollment( $course_id );
		// Insert an enrollment with a "different" trigger.
		$student->enroll( $course_id, 'whatever_trigger' );
		// Unenroll.
		$student->unenroll( $course_id );
		$this->assertEquals( 'expired', $student->get_enrollment_status( $course_id ) );
		$this->assertEquals( 'whatever_trigger', $student->get_enrollment_trigger( $course_id, false ) );

		// Enroll via api.
		sleep(1); //<- To be sure the new status is subsequent the one previously set.
		$response = $this->perform_mock_request( $this->update_method,  $this->parse_route( $user_id ) . '/' . $course_id, array( 'status' => 'enrolled' ) );

		// Success.
		$this->assertResponseStatusEquals( 200, $response );
		$res_data = $response->get_data();
		$this->assertEquals( 'enrolled', $res_data['status'] );
		$this->assertEquals( 'enrolled', $student->get_enrollment_status( $course_id, true ) );
		$this->assertEquals( 'unspecified', $student->get_enrollment_trigger( $course_id ) );

		// Check update passing a trigger.
		$status   = 'expired';
		$trigger  = ( new LLMS_Student( $user_id ) )->get_enrollment_trigger( $course_id );
		sleep(1); //<- To be sure the new status is subsequent the one previously set.

		$response = $this->perform_mock_request(
			$this->update_method,  $this->parse_route( $user_id ) . '/' . $course_id,
			array(
				'status'  => $status,
			),
			array(
				'trigger' => $trigger,
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );
		$res_data = $response->get_data();
		$this->assertEquals( $status, $res_data['status'] );
		$this->assertEquals( $status, $student->get_enrollment_status( $course_id, true ) );
		$this->assertEquals( $trigger, $student->get_enrollment_trigger( $course_id ) );
	}

	/**
	 * Test update enrollment creation date.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.10 Added test on the trigger arg.
	 */
	public function test_update_enrollment_creation_date() {

		wp_set_current_user( $this->user_allowed );

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$user_id   = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		// Enroll Student in newly created course/membership.
		llms_enroll_student( $user_id, $course_id, 'test_update_creation' );

		$new_date = date( 'Y-m-d H:i:s', strtotime('+1 year') );
		$response = $this->perform_mock_request(
			$this->update_method,  $this->parse_route( $user_id ) . '/' . $course_id,
			array( 'date_created' => $new_date )
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );
		$res_data = $response->get_data();

		// Check:
		$this->assertEquals( $user_id, $res_data['student_id'] );
		$this->assertEquals( $course_id, $res_data['post_id'] );
		$this->assertEquals( $new_date, $res_data['date_created'] );

		$student = new LLMS_Student( $user_id );
		$this->assertEquals( $res_data['date_created'], $student->get_enrollment_date( $course_id, 'enrolled', 'Y-m-d H:i:s' ) );

		// Check update passing a trigger.
		$new_date = date( 'Y-m-d H:i:s', strtotime('+2 year') );

		$response = $this->perform_mock_request(
			$this->update_method,  $this->parse_route( $user_id ) . '/' . $course_id,
			array(
				'date_created' => $new_date,
			),
			array(
				'trigger' => ( new LLMS_Student( $user_id ) )->get_enrollment_trigger( $course_id )
			)
		);

		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();

		$this->assertEquals( $user_id, $res_data['student_id'] );
		$this->assertEquals( $course_id, $res_data['post_id'] );
		$this->assertEquals( $new_date, $res_data['date_created'] );

	}

	/**
	 * Test producing 404 request errort.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.10 Improve tests on update and add tests on the trigger arg.
	 */
	public function test_enrollments_not_found() {

		wp_set_current_user( $this->user_allowed );

		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$user_id   = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		/* Create */
		// User not enrolled.
		$response = $this->perform_mock_request( 'GET', $this->parse_route( $user_id ) );
		$this->assertResponseStatusEquals( 404, $response );

		// User id doesn't exist.
		$response = $this->perform_mock_request( 'POST', $this->parse_route( $user_id . '1234' ) . '/' . $course_id );
		$this->assertResponseStatusEquals( 404, $response );

		// Course id doesn't exist.
		$response = $this->perform_mock_request( 'POST', $this->parse_route( $user_id ) . '/' . $course_id . '1245' );
		$this->assertResponseStatusEquals( 404, $response );

		// Update and Retrieve.
		foreach ( array( $this->update_method, 'GET' ) as $method ) {
			// User id doesn't exist.
			$response = $this->perform_mock_request( $method, $this->parse_route( $user_id . '1234' ) . '/' . $course_id );
			$this->assertResponseStatusEquals( 404, $response );

			// Course id doesn't exist.
			$response = $this->perform_mock_request( $method, $this->parse_route( $user_id ) . '/' . $course_id . '1245' );
			$this->assertResponseStatusEquals( 404, $response );

			// User id and course id exist but the enrollment is not found.
			$response = $this->perform_mock_request( $method, $this->parse_route( $user_id ) . '/' . $course_id  );
			$this->assertResponseStatusEquals( 404, $response );
		}

		// Enroll Student in newly created course/membership.
		llms_enroll_student( $user_id, $course_id, 'test_update_creation' );

		$new_date = date( 'Y-m-d H:i:s', strtotime('+1 year') );
		$response = $this->perform_mock_request( $this->update_method,  $this->parse_route( $user_id ) . '/' . $course_id, array( 'date_created' => $new_date ), array( 'trigger' => 'wrong' ) );

		// Cannot update because the enrollment with the specified trigger doesn't exist.
		$this->assertResponseStatusEquals( 404, $response );

	}

	/**
	 * Test retrieving enrollment trigger.
	 *
	 * @since 1.0.0-beta.18
	 */
	public function test_enrollment_trigger() {

		wp_set_current_user( $this->user_allowed );

		// Create an enrollment, we need a student and a course/membership.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$student = new LLMS_Student( $user_id );
		// Create new course.
		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		// Enroll Student in newly created course/membership.
		llms_enroll_student( $user_id, $course_id, 'test_enroll' );

		// Unenroll the student.
		sleep(1); //<- To be sure the new status is subsequent the one previously set.
		llms_unenroll_student( $user_id, $course_id, 'expired', 'test_enroll' );
		$this->assertEquals( 'expired', $student->get_enrollment_status( $course_id, false ) );

		// Enroll the student again.
		sleep(1); //<- To be sure the new status is subsequent the one previously set.
		llms_enroll_student( $user_id, $course_id, 'test_enroll_again' );

		$response = $this->perform_mock_request(
			'GET',
			$this->parse_route( $user_id )  . '/' . $course_id
		);

		$this->assertEquals(
			$student->get_enrollment_trigger( $course_id, false ),
			$response->get_data()['trigger']
		);

	}

	/**
	 * Test deleting a single enrollment.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.10 Added tests on trigger arg.
	 */
	public function test_delete_enrollment() {

		wp_set_current_user( $this->user_allowed );

		// Create an enrollment, we need a student and a course/membership.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		// Create new course.
		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );

		// Enroll Student in newly created course/membership.
		llms_enroll_student( $user_id, $course_id, 'test_delete' );

		// Delete user's enrollment.
		$response = $this->perform_mock_request(
			'DELETE',
			$this->parse_route( $user_id ) . '/' . $course_id
		);

		// Success.
		$this->assertResponseStatusEquals( 204, $response );
		// Student should not be enrolled in course.
		$this->assertFalse( llms_is_user_enrolled( $user_id, $course_id ) );

		// Enroll Student in newly created course/membership.
		llms_enroll_student( $user_id, $course_id, 'test_delete' );

		$response = $this->perform_mock_request(
			'DELETE',
			$this->parse_route( $user_id ) . '/' . $course_id,
			array(),
			array( 'trigger' => 'wrong' )
		);

		// Success (idempotency).
		$this->assertResponseStatusEquals( 204, $response );
		// Student should still be enrolled in the course.
		$this->assertTrue( llms_is_user_enrolled( $user_id, $course_id ) );

		// Using the right trigger.
		$response = $this->perform_mock_request(
			'DELETE',  $this->parse_route( $user_id ) . '/' . $course_id,
			array(),
			array(
				'trigger' => ( new LLMS_Student( $user_id ) )->get_enrollment_trigger( $course_id )
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 204, $response );
		// Student should still be enrolled in the course.
		$this->assertFalse( llms_is_user_enrolled( $user_id, $course_id ) );
	}

	/**
	 * Test protected enrollment_exists method.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.26 Added tests on checking enrollment existence of a logged out user by a logged in user
	 *
	 * @return void
	 */
	public function test_enrollment_exists() {

		$error_code = 'llms_rest_not_found';

		$result = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'enrollment_exists', array( 789, 879 ) );
		// Enrollment doesn't exist because both student and course/membership do not exist.
		$this->assertWPError( $result );

		$student_id = $this->factory->user->create( array( 'role' => 'student' ) );
		$result = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'enrollment_exists', array( $student_id, 879 ) );
		// Enrollment doesn't exist because course/membership do not exist.
		$this->assertWPError( $result );

		// Create new course.
		$course_id = $this->factory->post->create( array( 'post_type' => 'course' ) );
		$result = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'enrollment_exists', array( $student_id, $course_id ) );
		// Enrollment doesn't exist because the $student has not been enrolled yet.
		$this->assertWPError( $result );
		$this->assertWPErrorCodeEquals( $error_code, $result );

		// Enroll Student.
		llms_enroll_student( $student_id, $course_id, 'test_exists' );
		$result = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'enrollment_exists', array( $student_id, $course_id ) );
		// Enrollment exists because the $student has been enrolled yet.
		$this->assertTrue( $result );

		// Unenroll Student.
		llms_unenroll_student( $student_id, $course_id );
		$result = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'enrollment_exists', array( $student_id, $course_id ) );
		// Enrollment still exists because the $student has been unenrolled but not deleted.
		$this->assertTrue( $result );

		// Delete student's enrollment
		llms_delete_student_enrollment( $student_id, $course_id );
		$result = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'enrollment_exists', array( $student_id, $course_id ) );
		// Enrollment still exists because the $student has been unenrolled but not deleted.
		$this->assertWPError( $result );
		$this->assertWPErrorCodeEquals( $error_code, $result );

		// Check we don't fall back on the current user when a falsy user ID is supplied.
		wp_set_current_user( $this->user_allowed );
		$result = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'enrollment_exists', array( 0, $course_id ) );
		$this->assertWPError( $result );
		$this->assertWPErrorCodeEquals( $error_code, $result );

	}

	/**
	 * Get route.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @param int $student_id Student identifier.
	 * @param int $post_id    Post identifier.
	 * @return string
	 */
	protected function get_route( $student_id = null, $post_id = null ) {
		if ( is_null( $student_id) ) {
			$student_id = $this->user_student;
			if ( is_null( $post_id ) ) { // When both are null, we guess we're creating an enrollment.
				$post_id  = $this->factory->course->create( array( 'sections' => 1, 'lessons' => 1 ) );
			}
		}
		$route = $this->parse_route( $student_id );
		if ( $post_id ) {
			$route .= '/' . $post_id;
		}
		return $route;
	}

	/**
	 * Create resource.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return mixed The resource identifier.
	 */
	protected function create_resource() {
		$student = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$course  = $this->factory->course->create( array( 'sections' => 1, 'lessons' => 1 ) );
		llms_enroll_student( $student, $course );
		return array( $student, $course );
	}

	/**
	 * Parse route.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $student_id Student identifier.
	 * @return string
	 */
	private function parse_route( $student_id ) {
		return str_replace( '(?P<id>[\d]+)', $student_id, $this->route );
	}

}
