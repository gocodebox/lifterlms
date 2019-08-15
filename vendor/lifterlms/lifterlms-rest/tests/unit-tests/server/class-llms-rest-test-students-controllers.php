<?php
/**
 * Test the REST controller for the students resource
 *
 * @package LifterLMS_Rest/Tests/Controllers
 *
 * @group REST
 * @group rest_students
 * @group rest_users
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_Students_Controllers extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Route.
	 *
	 * @var string
	 */
	private $route = '/llms/v1/students';

	/**
	 * Array of link $rels expected for each item.
	 *
	 * @var array
	 */
	private $expected_link_rels = array( 'self', 'collection', 'enrollments', 'progress' );

	private $mock_student_data = array(
		'email' => 'jamief_%d@mockstudent.tld',
		'first_name' => 'Jamie',
		'last_name' => 'Fitzgerald',
		'name' => 'Jamie Fitzgerald',
		'nickname' => 'JamieF1932',
		'username' => 'jamief_%d',
		'url' => 'http://jamief.geocities.com',
		'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
		'billing_address_1' => '123 Some Street',
		'billing_address_2' => 'Suite A',
		'billing_city' => 'Some City',
		'billing_state' => 'NH',
		'billing_postcode' => '32319',
		'billing_country' => 'USA',
	);

	private function get_mock_student_data( $i ) {

		$data = $this->mock_student_data;

		$data['email'] = sprintf( $data['email'], $i );
		$data['username'] = sprintf( $data['username'], $i );

		return $data;

	}

	/**
	 * Retrieve an LLMS_Student with data.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data Array of user information.
	 * @return LLMS_Student
	 */
	private function get_student_with_data( $data = array() ) {

		$student = $this->factory->student->create_and_get( array(
			'user_email' => $data['email'],
			'user_login' => $data['username'],
			'user_url' => $data['url'],
			'display_name' => $data['name']
		) );

		unset( $data['email'], $data['username'], $data['url'], $data['name'] );

		foreach ( $data as $key => $val ) {
			$student->set( $key, $val, 0 === strpos( $key, 'billing_' ) ? true : false );
		}

		return $student;

	}

	/**
	 * Setup the test case.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->user_admin = $this->factory->user->create( array( 'role' => 'administrator', ) );
		$this->user_instructor = $this->factory->user->create( array( 'role' => 'instructor', ) );
		$this->user_subscriber = $this->factory->user->create( array( 'role' => 'subscriber', ) );
		$this->endpoint = new LLMS_REST_Students_Controller();

	}

	/**
	 * Teardown test
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function tearDown() {

		parent::tearDown();

		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->users}" );

	}

	/**
	 * Test the create_item method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_item() {

		// Unauthorized user.
		wp_set_current_user( null );
		$data = $this->get_mock_student_data( 3 );
		$res = $this->perform_mock_request( 'POST', $this->route, $data );
		$this->assertResponseStatusEquals( 401, $res );
		$this->assertResponseCodeEquals( 'llms_rest_unauthorized_request', $res );

		// Forbidden user.
		wp_set_current_user( $this->user_subscriber );
		$res = $this->perform_mock_request( 'POST', $this->route, $data );
		$this->assertResponseStatusEquals( 403, $res );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $res );

		// Instructor's can't create.
		wp_set_current_user( $this->user_instructor );
		$res = $this->perform_mock_request( 'POST', $this->route, $data );
		$this->assertResponseStatusEquals( 403, $res );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $res );

		// Okay.
		wp_set_current_user( $this->user_admin );
		$data = $this->get_mock_student_data( 4 );
		$password = wp_generate_password();
		$res = $this->perform_mock_request( 'POST', $this->route, array_merge( compact( 'password' ), $data ) );
		$this->assertResponseStatusEquals( 201, $res );

		$res_data = $res->get_data();
		foreach ( $data as $key => $expected ) {
			$this->assertEquals( $expected, $res_data[ $key ], $key );
		}

		$this->assertEquals( array( 'student' ), $res_data['roles'] );

		$this->assertArrayHasKey( 'id', $res_data );
		$this->assertArrayHasKey( 'registered_date', $res_data );
		$this->assertArrayHasKey( 'avatar_urls', $res_data );

		// Check password.
		$user = get_user_by( 'id', $res_data['id'] );
		$this->assertTrue( wp_check_password( $password, $user->user_pass ) );

		// Location header.
		$headers = $res->get_headers();
		$this->assertEquals( rest_url( sprintf( '%1$s/%2$d', $this->route, $res_data['id'] ) ), $headers['Location'] );

		// Links.
		$this->assertEquals( $this->expected_link_rels, array_keys( $res->get_links() ) );

	}

	/**
	 * Test create user permission check
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_item_permissions_check() {

		$request = new WP_REST_Request( 'POST', $this->route );

		// Unauthorized user.
		wp_set_current_user( null );
		$ret = $this->endpoint->create_item_permissions_check( $request );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $ret );

		// Cannot create students.
		$nay = array( 'subscriber', 'instructor', 'instructors_assistant' );
		foreach ( $nay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$ret = $this->endpoint->create_item_permissions_check( $request );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_forbidden_request', $ret );
		}

		// Can create.
		$yay = array( 'administrator', 'lms_manager' );
		foreach ( $yay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$this->assertTrue( $this->endpoint->create_item_permissions_check( $request ) );
		}

	}

	public function test_delete_item() {

		$id = $this->factory->student->create();

		// no user.
		$res = $this->perform_mock_request( 'DELETE', sprintf( '%1$s/%2$d', $this->route, $id ) );
		$this->assertResponseStatusEquals( 401, $res );
		$this->assertResponseCodeEquals( 'llms_rest_unauthorized_request', $res );

		// Forbidden user.
		wp_set_current_user( $this->user_subscriber );
		$res = $this->perform_mock_request( 'DELETE', sprintf( '%1$s/%2$d', $this->route, $id ) );
		$this->assertResponseStatusEquals( 403, $res );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $res );

		// Good.
		wp_set_current_user( $this->user_admin );
		$res = $this->perform_mock_request( 'DELETE', sprintf( '%1$s/%2$d', $this->route, $id ) );
		$this->assertTrue( is_null( $res->get_data() ) );
		$this->assertResponseStatusEquals( 204, $res );

		// deleting the same user again has the same result.
		$res = $this->perform_mock_request( 'DELETE', sprintf( '%1$s/%2$d', $this->route, $id ) );
		$this->assertTrue( is_null( $res->get_data() ) );
		$this->assertResponseStatusEquals( 204, $res );

	}

	/**
	 * Test delete item permission check
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_delete_item_permissions_check() {

		$user_id = $this->factory->student->create();

		$request = new WP_REST_Request( 'DELETE', sprintf( '%1$s/%2$d', $this->route, $user_id ) );
		$request->set_url_params( array( 'id' => $user_id ) );

		// Unauthorized user.
		wp_set_current_user( null );
		$ret = $this->endpoint->delete_item_permissions_check( $request );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $ret );

		// Cannot delete students.
		$nay = array( 'subscriber', 'instructor', 'instructors_assistant' );
		foreach ( $nay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$ret = $this->endpoint->delete_item_permissions_check( $request );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_forbidden_request', $ret );
		}

		// Can delete student.
		$yay = array( 'administrator', 'lms_manager' );
		foreach ( $yay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$this->assertTrue( $this->endpoint->delete_item_permissions_check( $request ) );
		}

	}

	/**
	 * Test get item permission check
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_item_permissions_check() {

		$user_id = $this->factory->student->create();

		$request = new WP_REST_Request( 'GET', sprintf( '%1$s/%2$d', $this->route, $user_id ) );
		$request->set_url_params( array( 'id' => $user_id ) );

		// Unauthorized user.
		wp_set_current_user( null );
		$ret = $this->endpoint->get_item_permissions_check( $request );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $ret );

		// can get self.
		wp_set_current_user( $user_id );
		$this->assertTrue( $this->endpoint->get_item_permissions_check( $request ) );

		// Cannot get students.
		$nay = array( 'subscriber', 'instructor', 'instructors_assistant' );
		foreach ( $nay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$ret = $this->endpoint->get_item_permissions_check( $request );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_forbidden_request', $ret );
		}

		// Can get student.
		$yay = array( 'administrator', 'lms_manager' );
		foreach ( $yay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$this->assertTrue( $this->endpoint->get_item_permissions_check( $request ) );
		}

		// Can get their own students.
		$own = array( 'instructor', 'instructors_assistant' );
		foreach( $own as $role ) {

			$user = $this->factory->user->create( array( 'role' => $role ) );
			$course = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
			$course->instructors()->set_instructors( array( array( 'id' => $user ) ) );
			llms_enroll_student( $user_id, $course->get( 'id' ) );

			wp_set_current_user( $user );

			$this->assertTrue( $this->endpoint->get_item_permissions_check( $request ) );

		}

	}

	/**
	 * Test get item permission check
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_items_permissions_check() {

		$user_id = $this->factory->student->create();

		$request = new WP_REST_Request( 'GET', $this->route );

		// Unauthorized user.
		wp_set_current_user( null );
		$ret = $this->endpoint->get_items_permissions_check( $request );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $ret );

		// Cannot get students.
		$nay = array( 'subscriber' );
		foreach ( $nay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$ret = $this->endpoint->get_items_permissions_check( $request );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_forbidden_request', $ret );
		}

		// Can get students.
		$yay = array( 'administrator', 'lms_manager', 'instructor', 'instructors_assistant' );
		foreach ( $yay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$this->assertTrue( $this->endpoint->get_items_permissions_check( $request ) );
		}

		// Add roles to the request.
		$request->set_query_params( array( 'roles' => 'student' ) );

		// Cannot get students by role.
		$nay = array( 'instructor', 'instructors_assistant' );
		foreach ( $nay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$ret = $this->endpoint->get_items_permissions_check( $request );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_forbidden_request', $ret );
		}

		// Can filter students by role.
		$yay = array( 'administrator', 'lms_manager' );
		foreach ( $yay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$this->assertTrue( $this->endpoint->get_items_permissions_check( $request ) );
		}

	}

	/**
	 * Ensure all collection parameters have been registered.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_collection_params() {

		$params = $this->endpoint->get_collection_params();
		$this->assertArrayHasKey( 'context', $params );
		$this->assertArrayHasKey( 'page', $params );
		$this->assertArrayHasKey( 'per_page', $params );
		$this->assertArrayHasKey( 'order', $params );
		$this->assertArrayHasKey( 'orderby', $params );
		$this->assertArrayHasKey( 'include', $params );
		$this->assertArrayHasKey( 'roles', $params );
		$this->assertArrayHasKey( 'enrolled_in', $params );
		$this->assertArrayHasKey( 'enrolled_not_in', $params );

	}

	/**
	 * Test the get_item() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_item() {

		$data = $this->get_mock_student_data( 2 );
		$student = $this->get_student_with_data( $data );

		$route = sprintf( '%1$s/%2$d', $this->route, $student->get( 'id' ) );

		// No user.
		$res = $this->perform_mock_request( 'GET', $route );
		$this->assertResponseCodeEquals( 'llms_rest_unauthorized_request', $res );
		$this->assertResponseStatusEquals( 401, $res );

		// Forbidden user.
		wp_set_current_user( $this->user_subscriber );
		$res = $this->perform_mock_request( 'GET', $route );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $res );
		$this->assertResponseStatusEquals( 403, $res );

		// Admin okay.
		wp_set_current_user( $this->user_admin );

		// Test default (view), view (explicit), and edit contexts.
		foreach ( array( null, 'view', 'edit' ) as $context ) {

			if ( $context ) {
				$res = $this->perform_mock_request( 'GET', $route, array(), array( 'context' => $context ) );
			} else {
				$res = $this->perform_mock_request( 'GET', $route );
			}

			$this->assertResponseStatusEquals( 200, $res );

			$data = $res->get_data();
			$this->assertEquals( $student->get( 'id' ), $data['id'] );
			$this->assertEquals( $data['name'], $data['name'] );
			$this->assertEquals( $data['url'], $data['url'] );
			$this->assertEquals( $data['description'], $data['description'] );
			$this->assertArrayHasKey( 'avatar_urls', $data );

			$this->assertEquals( $this->expected_link_rels, array_keys( $res->get_links() ) );

			if ( 'edit' === $context ) {

				$this->assertEquals( $data['first_name'], $data['first_name'] );
				$this->assertEquals( $data['last_name'], $data['last_name'] );
				$this->assertEquals( $data['username'], $data['username'] );
				$this->assertEquals( $data['email'], $data['email'] );
				$this->assertEquals( $data['nickname'], $data['nickname'] );

				$this->assertEquals( $data['billing_address_1'], $data['billing_address_1'] );
				$this->assertEquals( $data['billing_address_2'], $data['billing_address_2'] );
				$this->assertEquals( $data['billing_city'], $data['billing_city'] );
				$this->assertEquals( $data['billing_state'], $data['billing_state'] );
				$this->assertEquals( $data['billing_postcode'], $data['billing_postcode'] );
				$this->assertEquals( $data['billing_country'], $data['billing_country'] );

				$this->assertEquals( array( 'student' ), $data['roles'] );
				$this->assertArrayHasKey( 'registered_date', $data );

			}

		}

		// Instructor is forbidden.
		wp_set_current_user( $this->user_instructor );
		$res = $this->perform_mock_request( 'GET', $route );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $res );
		$this->assertResponseStatusEquals( 403, $res );

		// Instructor can retrieve because student's in their course.
		$course = $this->factory->course->create_and_get( array( 'sections' => 0 ) );
		$course->set_instructors( array( array( 'id' => $this->user_instructor ) ) );
		$student->enroll( $course->get( 'id' ) );

		$res = $this->perform_mock_request( 'GET', $route );
		$this->assertResponseStatusEquals( 200, $res );

	}

	public function test_get_items_errors() {


		// No user.
		$res = $this->perform_mock_request( 'GET', $this->route );
		$this->assertResponseCodeEquals( 'llms_rest_unauthorized_request', $res );
		$this->assertResponseStatusEquals( 401, $res );

		// Forbidden user.
		wp_set_current_user( $this->user_subscriber );
		$res = $this->perform_mock_request( 'GET', $this->route );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $res );
		$this->assertResponseStatusEquals( 403, $res );


	}

	public function test_get_items_pagination() {

		global $wpdb;

		$this->factory->user->create_many( 5 );
		$this->factory->student->create_many( 25 );
		$db_total = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" ) );


		$db_pages = ceil( $db_total / 10 );

		wp_set_current_user( $this->user_admin );
		$res = $this->perform_mock_request( 'GET', $this->route );

		// Correct # of results to default 10 / page
		$this->assertEquals( 10, count( $res->get_data() ) );

		// Check Pagination headers.
		$headers = $res->get_headers();
		$this->assertEquals( $db_total, $headers['X-WP-Total'] );
		$this->assertEquals( $db_pages, $headers['X-WP-TotalPages'] );

		// Link headers.
		$links = $this->parse_link_headers( $res );
		$this->assertEquals( array( 'next', 'last' ), array_keys( $links ) );


		// Page 2.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), array( 'page' => 2 ) );

		// Link headers.
		$links = $this->parse_link_headers( $res );
		$this->assertEquals( array( 'first', 'prev', 'next', 'last' ), array_keys( $links ) );


		// Last page.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), array( 'page' => $db_pages ) );

		// Link headers.
		$links = $this->parse_link_headers( $res );
		$this->assertEquals( array( 'first', 'prev' ), array_keys( $links ) );


		// Big per page.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), array( 'per_page' => 100 ) );

		// Check Pagination headers.
		$headers = $res->get_headers();
		$this->assertEquals( $db_total, $headers['X-WP-Total'] );
		$this->assertEquals( 1, $headers['X-WP-TotalPages'] );

		// No links because this is the only page.
		$links = $this->parse_link_headers( $res );
		$this->assertEquals( array(), array_keys( $links ) );

		// Out of bounds.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), array( 'page' => 25, 'per_page' => 100 ) );
		$this->assertEquals( array(), $res->get_data() );

	}

	public function test_get_items_orderby_id() {

		wp_set_current_user( $this->user_admin );
		$low = $this->factory->user->create( array() );
		$high = $this->factory->user->create( array() );
		$args = array( 'include' => array( $low, $high ), 'orderby' => 'id' );

		// Default / asc.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( $low, $res->get_data()[0]['id'] );

		// Desc.
		$args['order'] = 'desc';
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( $high, $res->get_data()[0]['id'] );

	}

	public function test_get_items_orderby_email() {

		wp_set_current_user( $this->user_admin );
		$low = $this->factory->user->create( array( array( 'user_email' => 'aemail@mock.tld' ) ) );
		$high = $this->factory->user->create( array( array( 'user_email' => 'bemail@mock.tld' ) ) );
		$args = array( 'include' => array( $low, $high ), 'orderby' => 'email' );

		// Default / asc.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( $low, $res->get_data()[0]['id'] );

		// Desc.
		$args['order'] = 'desc';
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( $high, $res->get_data()[0]['id'] );

	}

	public function test_get_items_orderby_name() {

		wp_set_current_user( $this->user_admin );
		$low = $this->factory->user->create( array( 'display_name' => 'A Name' ) );
		$high = $this->factory->user->create( array( 'display_name' => 'B Name' ) );
		$args = array( 'include' => array( $low, $high ), 'orderby' => 'name' );

		// Default / asc.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( $low, $res->get_data()[0]['id'] );

		// Desc.
		$args['order'] = 'desc';
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( $high, $res->get_data()[0]['id'] );

	}

	public function test_get_items_orderby_registered_date() {

		wp_set_current_user( $this->user_admin );
		$low = $this->factory->user->create( array( 'user_registered' => date( 'Y-m-d h:i:s', strtotime( '-5 days', time() ) ) ) );
		$high = $this->factory->user->create();
		$args = array( 'include' => array( $low, $high ), 'orderby' => 'registered_date' );

		// Default / asc.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( $low, $res->get_data()[0]['id'] );

		// Desc.
		$args['order'] = 'desc';
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( $high, $res->get_data()[0]['id'] );

	}

	public function test_get_items_enrollement_filters() {

		wp_set_current_user( $this->user_admin );

		$course = $this->factory->course->create( array( 'sections' => 0 ) );

		$args = array(
			'include' => array(),
			'enrolled_in' => $course,
		);
		for ( $i = 1; $i <= 3; $i++ ) {
			$args['include'][] = $this->factory->student->create();
		}

		// None are enrolled.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( array(), $res->get_data() );

		unset( $args['enrolled_in'] );
		$args['enrolled_not_in'] = $course;

		// All are not enrolled.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( $args['include'], wp_list_pluck( $res->get_data(), 'id' ) );

		// Enroll a student.
		llms_enroll_student( $args['include'][0], $course );

		// Return only the non-enrolled students.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( array( $args['include'][1], $args['include'][2] ), wp_list_pluck( $res->get_data(), 'id' ) );

		// Only return the enrolled student.
		unset( $args['enrolled_not_in'] );
		$args['enrolled_in'] = $course;
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( array( $args['include'][0] ), wp_list_pluck( $res->get_data(), 'id' ) );

		// No one's enrolled in both.
		$course_2 = $this->factory->course->create( array( 'sections' => 0 ) );
		$args['enrolled_in'] .= ',' . $course_2;
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( array(), wp_list_pluck( $res->get_data(), 'id' ) );

		// One enrolled in both.
		llms_enroll_student( $args['include'][0], $course_2 );
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( array( $args['include'][0] ), wp_list_pluck( $res->get_data(), 'id' ) );

		unset( $args['enrolled_in'] );
		$args['enrolled_not_in'] = array( $course, $course_2 );
		$res = $this->perform_mock_request( 'GET', $this->route, array(), $args );
		$this->assertEquals( array( $args['include'][1], $args['include'][2] ), wp_list_pluck( $res->get_data(), 'id' ) );

	}

	/**
	 * Test the item schema.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_item_schema() {

		$schema = $this->endpoint->get_item_schema();

		$this->assertEquals( 'student', $schema['title'] );

		$props = array(
			'id',
			'username',
			'name',
			'first_name',
			'last_name',
			'email',
			'url',
			'description',
			'nickname',
			'registered_date',
			'roles',
			'password',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_country',
			'avatar_urls',
		);

		$this->assertEquals( $props, array_keys( $schema['properties'] ) );

		$this->assertEquals( array( 'student' ), $schema['properties']['roles']['default'] );

		$schema = $this->endpoint->get_item_schema();
		update_option( 'show_avatars', '' );
		$this->assertFalse( array_key_exists( 'avatar_urls', array_keys( $schema['properties'] ) ) );

		update_option( 'show_avatars', 1 );

	}

	/**
	 * Test the get_object method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_object() {

		$id = $this->factory->student->create();

		// Good.
		$student = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'get_object', array( $id ) );
		$this->assertTrue( is_a( $student, 'LLMS_Student' ) );

		// 404.
		$error_404 = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'get_object', array( $id + 1 ) );
		$this->assertIsWPError( $error_404 );
		$this->assertWPErrorCodeEquals( 'llms_rest_not_found', $error_404 );

	}

	/**
	 * Test the prepare_object_for_response() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_prepare_object_for_response() {

		$data = $this->get_mock_student_data( 1 );
		$student = $this->get_student_with_data( $data );
		$prepared = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'prepare_object_for_response', array( $student, new WP_REST_Request( 'GET', $this->route ) ) );

		foreach ( $data as $key => $val ) {
			$this->assertEquals( $val, $prepared[ $key ], $key );
		}

		$this->assertEquals( array( 'student' ), $prepared['roles'] );
		$this->assertArrayHasKey( 'avatar_urls', $prepared );

	}

	/**
	 * Test the prepare_item_for_database() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_prepare_item_for_database() {

		$request = new WP_REST_Request( 'POST', $this->route );
		$args = array(
			'email' => 'mock@mock.tld',
			'registered_date' => current_time( 'mysql' ),
			'first_name' => 'Sarah',
			'username' => 'mockername',
			'password' => wp_generate_password(),
		);
		$request->set_body_params( $args );
		$prepared = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'prepare_item_for_database', array( $request ) );

		$this->assertEquals( $args['email'], $prepared['user_email'] );
		$this->assertEquals( $args['registered_date'], $prepared['user_registered'] );
		$this->assertEquals( $args['first_name'], $prepared['first_name'] );
		$this->assertEquals( $args['username'], $prepared['user_login'] );
		$this->assertEquals( $args['password'], $prepared['user_pass'] );

		// Test setting of "required" optional args during a creation.
		$request = new WP_REST_Request( 'POST', $this->route );
		$args = array(
			'email' => 'mock@mock.tld',
		);
		$request->set_body_params( $args );
		$prepared = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'prepare_item_for_database', array( $request ) );

		$this->assertEquals( $args['email'], $prepared['user_email'] );
		$this->assertArrayHasKey( 'user_login', $prepared );
		$this->assertArrayHasKey( 'user_pass', $prepared );

		// Optional args won't be passed in during an UPDATE (only during creation)
		$request = new WP_REST_Request( 'POST', $this->route . '/123' );
		$args = array(
			'email' => 'mock@mock.tld',
			'password' => 'MyNewPasswordStinks',
		);
		$request->set_body_params( $args );
		$request->set_url_params( array( 'id' => 123 ) );
		$prepared = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'prepare_item_for_database', array( $request ) );

		$this->assertEquals( $args['email'], $prepared['user_email'] );
		$this->assertEquals( $args['password'], $prepared['user_pass'] );
		$this->assertTrue( ! array_key_exists( 'user_login', $prepared ) );

	}

	/**
	 * Test the prepare_links method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_prepare_links() {

		$student = $this->factory->student->create_and_get();
		$links = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'prepare_links', array( $student ) );
		foreach ( array( 'self', 'collection', 'enrollments', 'progress' ) as $rel ) {
			$this->assertArrayHasKey( $rel, $links );
		}

	}

	/**
	 * Test route registration
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_register_routes() {

		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( $this->route, $routes );
		$this->assertArrayHasKey( $this->route . '/(?P<id>[\d]+)', $routes );

	}

	/**
	 * Test the create_item method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_item() {

		$updating = $this->factory->student->create();

		$route = sprintf( '%1$s/%2$d', $this->route, $updating );

		// Unauthorized user.
		wp_set_current_user( null );
		$data = $this->get_mock_student_data( 10 );
		$res = $this->perform_mock_request( 'POST', $route, $data );
		$this->assertResponseStatusEquals( 401, $res );
		$this->assertResponseCodeEquals( 'llms_rest_unauthorized_request', $res );

		// Forbidden user.
		wp_set_current_user( $this->user_subscriber );
		$res = $this->perform_mock_request( 'POST', $route, $data );
		$this->assertResponseStatusEquals( 403, $res );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $res );

		// Instructor's can't create.
		wp_set_current_user( $this->user_instructor );
		$res = $this->perform_mock_request( 'POST', $route, $data );
		$this->assertResponseStatusEquals( 403, $res );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $res );


		// Can't edit username.
		wp_set_current_user( $this->user_admin );
		$data = $this->get_mock_student_data( 11 );
		$password = wp_generate_password();
		$res = $this->perform_mock_request( 'POST', $route, array_merge( compact( 'password' ), $data ) );
		$this->assertResponseStatusEquals( 400, $res );
		$this->assertResponseMessageEquals( 'Username is not editable.', $res );
		$this->assertResponseCodeEquals( 'llms_rest_bad_request', $res );

		unset( $data['username'] );

		// Okay.
		$res = $this->perform_mock_request( 'POST', $route, array_merge( compact( 'password' ), $data ) );
		$this->assertResponseStatusEquals( 200, $res );

		$res_data = $res->get_data();
		foreach ( $data as $key => $expected ) {
			$this->assertEquals( $expected, $res_data[ $key ], $key );
		}

		$this->assertEquals( array( 'student' ), $res_data['roles'] );

		$this->assertArrayHasKey( 'id', $res_data );
		$this->assertArrayHasKey( 'registered_date', $res_data );
		$this->assertArrayHasKey( 'avatar_urls', $res_data );

		// Check password.
		$user = get_user_by( 'id', $res_data['id'] );
		$this->assertTrue( wp_check_password( $password, $user->user_pass ) );

		// Links.
		$this->assertEquals( $this->expected_link_rels, array_keys( $res->get_links() ) );

		// user can update self.
		wp_set_current_user( $updating );
		$res = $this->perform_mock_request( 'POST', $route, array(
			'first_name' => 'Myself',
		) );
		$this->assertResponseStatusEquals( 200, $res );
		$this->assertEquals( 'Myself', $res->get_data()['first_name'] );

		// Cannot update email to an email that already exists.
		$this->factory->student->create( array( 'user_email' => 'thisemailalreadyexists@test.tld' ) );
		$res = $this->perform_mock_request( 'POST', $route, array(
			'email' => 'thisemailalreadyexists@test.tld',
		) );
		$this->assertResponseStatusEquals( 400, $res );
		$this->assertResponseMessageEquals( 'Invalid email address.', $res );
		$this->assertResponseCodeEquals( 'llms_rest_bad_request', $res );

	}


	/**
	 * Test update item permission check
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_item_permissions_check() {

		$user_id = $this->factory->student->create();

		$request = new WP_REST_Request( 'GET', sprintf( '%1$s/%2$d', $this->route, $user_id ) );
		$request->set_url_params( array( 'id' => $user_id ) );

		// Unauthorized user.
		wp_set_current_user( null );
		$ret = $this->endpoint->update_item_permissions_check( $request );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $ret );

		// can update self.
		wp_set_current_user( $user_id );
		$this->assertTrue( $this->endpoint->update_item_permissions_check( $request ) );

		// Cannot update students.
		$nay = array( 'subscriber', 'instructor', 'instructors_assistant' );
		foreach ( $nay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$ret = $this->endpoint->update_item_permissions_check( $request );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_forbidden_request', $ret );
		}

		// Can update student.
		$yay = array( 'administrator', 'lms_manager' );
		foreach ( $yay as $role ) {
			wp_set_current_user( $this->factory->user->create( array( 'role' => $role ) ) );
			$this->assertTrue( $this->endpoint->update_item_permissions_check( $request ) );
		}

	}


}
