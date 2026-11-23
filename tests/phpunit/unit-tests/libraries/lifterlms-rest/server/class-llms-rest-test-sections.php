<?php
/**
 * Tests for Sections API.
 *
 * @package LifterLMS_Rest/Tests/Controllers
 *
 * @group REST
 * @group rest_sections
 * @group rest_posts
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Test_Sections extends LLMS_REST_Unit_Test_Case_Posts {

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route = '/llms/v1/sections';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'section';


	/**
	 * Array of link $rels expected for each item.
	 *
	 * @var array
	 */
	private $expected_link_rels = array( 'self', 'collection', 'content', 'parent', 'siblings', 'next', 'previous' );

	/**
	 * Setup our test server, endpoints, and user info.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.7 Block migration forcing and db cleanup moved in LLMS_REST_Unit_Test_Case_Posts::set_up()
	 * @since 1.0.0-beta.27 Users creation moved in the `parent::set_up()`.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		$this->sample_section_args = array(
			'title' => array(
				'rendered' => 'Introduction',
				'raw'      => 'Introduction',
			),
		);

		$this->endpoint = new LLMS_REST_Sections_Controller();

	}


	/**
	 * Test route registration.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_register_routes() {

		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( $this->route, $routes );
		$this->assertArrayHasKey( $this->route . '/(?P<id>[\d]+)', $routes );

		// Child lessons.
		$this->assertArrayHasKey( $this->route . '/(?P<id>[\d]+)/content', $routes );
	}

	/**
	 * Test list sections.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.7 Fixed sections fields check.
	 */
	public function test_get_sections() {

		wp_set_current_user( $this->user_allowed );

		// create 3 courses.
		$courses = $this->factory->course->create_many( 3, array( 'sections' => 5, 'lessons' => 0 ) );

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', $this->route ) );

		// Success.
		$this->assertEquals( 200, $response->get_status() );

		$res_data = $response->get_data();
		$this->assertEquals( 10, count( $res_data ) ); // default per_page is 10.

		$headers = $response->get_headers();
		$this->assertEquals( 15, $headers['X-WP-Total'] );
		$this->assertEquals( 2, $headers['X-WP-TotalPages'] );

		array_pop($courses);
		$i = 0;
		// Check retrieved sections are the same as the generated ones.
		foreach ( $courses as $course ) {

			$course_obj = new LLMS_Course( $course );
			$sections   = $course_obj->get_sections();

			// Easy sequential check as sections are by default ordered by id.
			$j = 0;
			foreach ( $sections as $section ) {
				$res_section = $res_data[ ( $i * count( $sections ) ) + $j ];
				$this->llms_posts_fields_match( $section, $res_section );
				$j++;
			}

			$i++;
		}
	}

	/**
	 * Test filtering sections by parent via the `parent_id` alias.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_get_sections_filter_by_parent_id_alias() {

		wp_set_current_user( $this->user_allowed );

		$courses = $this->factory->course->create_many( 2, array( 'sections' => 2, 'lessons' => 0 ) );

		$request = new WP_REST_Request( 'GET', $this->route );
		$request->set_param( 'parent_id', $courses[0] );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );

		$res_data = $response->get_data();
		$this->assertEquals( 2, count( $res_data ) );
		foreach ( $res_data as $section ) {
			$this->assertEquals( $courses[0], $section['parent_id'] );
		}

		// `parent` wins when both are provided.
		$request = new WP_REST_Request( 'GET', $this->route );
		$request->set_param( 'parent', $courses[1] );
		$request->set_param( 'parent_id', $courses[0] );
		$response = $this->server->dispatch( $request );

		$res_data = $response->get_data();
		$this->assertEquals( 2, count( $res_data ) );
		foreach ( $res_data as $section ) {
			$this->assertEquals( $courses[1], $section['parent_id'] );
		}
	}

	/**
	 * Test links.
	 *
	 * @since 1.0.0-beta.7
	 * @since 11.0.0 Sections are only readable by logged in users.
	 */
	public function test_links() {

		wp_set_current_user( $this->factory->user->create( array( 'role' => 'student' ) ) );

		// create course with 3 sections and 1 lesson per section.
		$course = $this->factory->course->create_and_get( array( 'sections' => 3, 'lessons' => 1 ) );

		$course_obj = new LLMS_Course( $course );
		$sections   = $course_obj->get_sections();


		$i = 0;
		foreach ( $sections as $section ) {

			$response = $this->perform_mock_request( 'GET',  $this->route . '/' . $section->get('id')  );

			switch ( $i++ ):
				case 0:
					$expected_link_rels = array_values( array_diff( $this->expected_link_rels, array( 'previous' ) ) );
					break;
				case 2:
					$expected_link_rels = array_values( array_diff( $this->expected_link_rels, array( 'next' ) ) );
					break;
				default:
					$expected_link_rels = $this->expected_link_rels;
			endswitch;

			$this->assertEquals( $expected_link_rels, array_keys( $response->get_links() ) );

		}

	}

	/**
	 * Test list sections pagination.
	 *
	 * @since 1.0.0-beta.7
	 */
	public function test_get_sections_pagination() {

		wp_set_current_user( $this->user_allowed );

		// create sections
		$course = $this->factory->course->create_and_get( array( 'sections' => 25, 'lessons' => 0 ) );
		$start_section_id = $course->get_sections( 'ids' )[0];

		$this->pagination_test( $this->route, $start_section_id );

	}


	/**
	 * Test create a single section.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_create_section() {

		wp_set_current_user( $this->user_allowed );

		$request = new WP_REST_Request( 'POST', $this->route );

		// create a course.
		$course_id = $this->factory->course->create( array( 'sections' => 0 ) );

		$section_args = $this->sample_section_args;
		$section_args['parent_id'] = $course_id;

		$request->set_body_params( $section_args );
		$response = $this->server->dispatch( $request );

		// Success.
		$this->assertEquals( 201, $response->get_status() );

		$course = new LLMS_Course( $course_id );
		$sections = $course->get_sections(); // returns an array of one element.

		$res_data = $response->get_data();

		// Test the created section and the response are equal
		$this->llms_posts_fields_match( $sections[0], $res_data );
		$this->assertEquals( $section_args['title']['rendered'], $res_data['title']['rendered'] );
	}

	/**
	 * Test creating sections without order assigns sequential sibling order.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_create_section_auto_order() {

		wp_set_current_user( $this->user_allowed );

		$course_id    = $this->factory->course->create( array( 'sections' => 0 ) );
		$section_args = $this->sample_section_args;
		$section_args['parent_id'] = $course_id;

		$request = new WP_REST_Request( 'POST', $this->route );
		$request->set_body_params( $section_args );
		$first = $this->server->dispatch( $request );

		$request = new WP_REST_Request( 'POST', $this->route );
		$request->set_body_params( $section_args );
		$second = $this->server->dispatch( $request );

		$this->assertEquals( 201, $first->get_status() );
		$this->assertEquals( 201, $second->get_status() );
		$this->assertEquals( 1, $first->get_data()['order'] );
		$this->assertEquals( 2, $second->get_data()['order'] );
	}

	/**
	 * Test creating a section with an explicit order shifts the existing siblings.
	 *
	 * @since 10.2.0
	 *
	 * @return void
	 */
	public function test_create_section_explicit_order_shifts_siblings() {

		wp_set_current_user( $this->user_allowed );

		$course   = $this->factory->course->create_and_get( array( 'sections' => 2, 'lessons' => 0 ) );
		$existing = $course->get_sections( 'ids' );

		$section_args              = $this->sample_section_args;
		$section_args['parent_id'] = $course->get( 'id' );
		$section_args['order']     = 2;

		$request = new WP_REST_Request( 'POST', $this->route );
		$request->set_body_params( $section_args );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 201, $response->get_status() );
		$this->assertEquals( 2, $response->get_data()['order'] );

		// The former section at position 2 is shifted; orders remain unique and sequential.
		$this->assertEquals( 1, llms_get_post( $existing[0] )->get( 'order' ) );
		$this->assertEquals( 3, llms_get_post( $existing[1] )->get( 'order' ) );
	}

	/**
	 * Test that an instructor cannot create a section inside a course they cannot edit.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function test_create_section_forbidden_parent_course() {

		// Admin creates a victim course the instructor cannot edit.
		wp_set_current_user( $this->user_allowed );
		$victim_course_id = $this->factory->course->create( array( 'sections' => 0 ) );

		// Act as an instructor who can publish sections but cannot edit the victim course.
		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );
		wp_set_current_user( $instructor );

		$this->assertFalse( current_user_can( 'edit_post', $victim_course_id ) );

		$section_args              = $this->sample_section_args;
		$section_args['parent_id'] = $victim_course_id;

		$request = new WP_REST_Request( 'POST', $this->route );
		$request->set_body_params( $section_args );
		$response = $this->server->dispatch( $request );

		$this->assertEquals( 403, $response->get_status() );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $response );

		// Ensure no section was injected into the victim course.
		$victim_course = new LLMS_Course( $victim_course_id );
		$this->assertEmpty( $victim_course->get_sections() );
	}

	/**
	 * Test producing bad request error when creating a single section.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_create_section_bad_request() {

		wp_set_current_user( $this->user_allowed );

		$request = new WP_REST_Request( 'POST', $this->route );
		// create a course.
		$course = $this->factory->course->create( array( 'sections' => 0 ) );
		$post   = $this->factory->post->create();

		// create a section without parent_id.
		$section_args = $this->sample_section_args;

		$request->set_body_params( $section_args );
		$response = $this->server->dispatch( $request );
		// Bad request.
		$this->assertEquals( 400, $response->get_status() );

		// Creating a section passing a parent_id which is not a course id produces a bad request.
		$section_args = $this->sample_section_args;

		// This post doesn't exist.
		$section_args['parent_id'] = 1234;

		$request->set_body_params( $section_args );
		$response = $this->server->dispatch( $request );

		// Bad request.
		$this->assertEquals( 400, $response->get_status() );
		$this->assertResponseMessageEquals( 'Invalid parent_id param. It must be a valid Course ID.', $response );

		// This post exists but is not a course.
		$section_args['parent_id'] = $post;

		$request->set_body_params( $section_args );
		$response = $this->server->dispatch( $request );

		// Bad request.
		$this->assertEquals( 400, $response->get_status() );
		$this->assertResponseMessageEquals( 'Invalid parent_id param. It must be a valid Course ID.', $response );

		$this->sample_section_args['parent_id'] = $course;

		// Creating a section passing an order equal to 0 produces a bad request.
		$section_args          = $this->sample_section_args;
		$section_args['order'] = 0;
		$request->set_body_params( $section_args );
		$response = $this->server->dispatch( $request );

		// Bad request.
		$this->assertEquals( 400, $response->get_status() );
		$this->assertResponseMessageEquals( 'Invalid order param. It must be greater than 0.', $response );

		// create a section without title.
		$section_args = $this->sample_section_args;
		unset( $section_args['title'] );

		$request->set_body_params( $section_args );
		$response = $this->server->dispatch( $request );
		// Bad request.
		$this->assertEquals( 400, $response->get_status() );

	}

	/**
	 * Test deleting a single section.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_delete_section() {

		wp_set_current_user( $this->user_allowed );

		// create a section first.
		$section = llms_get_post( $this->factory->post->create( array( 'post_type' => 'section' ) ) );

		$request = new WP_REST_Request( 'DELETE', $this->route . '/' . $section->get( 'id' ) );

		$response = $this->server->dispatch( $request );

		// Success.
		$this->assertEquals( 204, $response->get_status() );

		// Cannot find just deleted post.
		$this->assertFalse( get_post_status( $section->get( 'id' ) ) );

	}

	/**
	 * Test trashing a single section.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_trash_section() {

		wp_set_current_user( $this->user_allowed );

		// create a section first.
		$section = llms_get_post( $this->factory->post->create( array( 'post_type' => 'section' ) ) );

		$request = new WP_REST_Request( 'DELETE', $this->route . '/' . $section->get( 'id' ) );
		$request->set_param( 'force', false );
		$response = $this->server->dispatch( $request );

		// We still expect a 204 section are always deleted and not trashed.
		$this->assertEquals( 204, $response->get_status() );

		// Cannot find just deleted post.
		$this->assertFalse( get_post_status( $section->get( 'id' ) ) );

	}

	/**
	 * Test list sections content.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @todo test order and orderby
	 */
	public function test_get_sections_content() {

		wp_set_current_user( $this->user_allowed );

		// create 1 course with 1 section but no lessons.
		$course = $this->factory->course->create_and_get(
			array(
				'sections' => 1,
				'lessons'  => 0,
			)
		);

		$section_id = $course->get_sections( 'ids' )[0];

		$response = $this->server->dispatch( new WP_REST_Request( 'GET', $this->route . '/' . $section_id . '/content' ) );

		// We have no lessons for this section so we expect a 404.
		$this->assertEquals( 404, $response->get_status() );

		// create 1 course with 5 sections and 3 lessons per section
		$course = $this->factory->course->create_and_get(
			array(
				'sections' => 5,
				'lessons'  => 3,
			)
		);

		$section_ids = $course->get_sections( 'ids' );

		foreach ( $section_ids as $section_id ) {

			$response = $this->server->dispatch( new WP_REST_Request( 'GET', $this->route . '/' . $section_id . '/content' ) );

			// Success.
			$this->assertEquals( 200, $response->get_status() );

			$res_data = $response->get_data();
			$this->assertEquals( 3, count( $res_data ) );

			for ( $i = 0; $i < 3; $i++ ) {
				$this->assertEquals( $section_id, $res_data[ $i ]['parent_id'] );
			}

		}

	}

	/**
	 * Test sections content controller not initialized when not needed.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function test_sections_content_not_init() {

		$this->assertNotNull( $this->endpoint->get_content_controller() );

		$new_sec_controller = new LLMS_REST_Sections_Controller( '' );
		$this->assertNull( $new_sec_controller->get_content_controller() );

	}

	/**
	 * Get resource creation args.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return array
	 */
	protected function get_creation_args() {

		$course_id = $this->factory->course->create(
			array(
				'sections' => 0,
			)
		);

		return array_merge(
			parent::get_creation_args(),
			array(
				'parent_id' => $course_id,
			)
		);

	}

	/**
	 * Override.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.7 Fixed expected 'order' field, added expected 'parent_id'.
	 * @since 1.0.0-beta.23 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
	 *
	 * @param array           $expected  Array of expected properties.
	 * @param LLMS_Post_Model $llms_post Instance of LLMS_Post_Model.
	 * @return array
	 */
	protected function filter_expected_fields( $expected, $llms_post ) {

		unset( $expected['content'] );
		$expected['order']     = $llms_post->get( 'order' );
		$expected['parent_id'] = $llms_post->get( 'parent_course' );

		return $expected;

	}

	/**
	 * Sections of password protected courses are not counted for callers who cannot read them.
	 *
	 * The per-item read check refuses a section whose parent course requires a
	 * password, but the section itself carries no password, so the collection
	 * query would still match (and count) it, turning the pagination totals
	 * into a search oracle.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function test_get_items_totals_exclude_password_protected_parents() {

		$open_course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 0 ) );
		$open        = $open_course->get_sections( 'ids' )[0];

		$protected = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 0 ) );
		wp_update_post(
			array(
				'ID'            => $protected->get( 'id' ),
				'post_password' => 'labpass',
			)
		);
		$hidden = $protected->get_sections( 'ids' )[0];

		$secret = 'SECRETSEC77';
		wp_update_post(
			array(
				'ID'         => $hidden,
				'post_title' => $secret,
			)
		);

		// A caller who cannot read the parent course cannot count, or search for, the protected section.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'student' ) ) );
		$res = $this->perform_mock_request( 'GET', $this->route, array(), array(
			'include' => array( $hidden ),
			'search'  => $secret,
		) );
		$this->assertResponseStatusEquals( 200, $res );
		$this->assertEquals( 0, $res->get_headers()['X-WP-Total'] );
		$this->assertEmpty( $res->get_data() );

		// A readable section is still listed, with a matching total.
		$res = $this->perform_mock_request( 'GET', $this->route, array(), array( 'include' => array( $open ) ) );
		$this->assertResponseStatusEquals( 200, $res );
		$this->assertEquals( 1, $res->get_headers()['X-WP-Total'] );
		$this->assertEquals( array( $open ), wp_list_pluck( $res->get_data(), 'id' ) );

		// Edit context is unchanged: an administrator reads the protected section.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$res = $this->perform_mock_request( 'GET', $this->route, array(), array(
			'context' => 'edit',
			'include' => array( $hidden ),
			'search'  => $secret,
		) );
		$this->assertResponseStatusEquals( 200, $res );
		$this->assertEquals( 1, $res->get_headers()['X-WP-Total'] );
		$this->assertEquals( array( $hidden ), wp_list_pluck( $res->get_data(), 'id' ) );

	}

	/**
	 * Sections are not readable by unauthenticated users, on any route that outputs them.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function test_get_sections_unauthenticated() {

		$course  = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 0 ) );
		$section = $course->get_sections( 'ids' )[0];

		wp_set_current_user( 0 );

		$routes = array(
			$this->route,
			$this->route . '/' . $section,
			'/llms/v1/courses/' . $course->get( 'id' ) . '/content',
		);
		foreach ( $routes as $route ) {
			$res = $this->perform_mock_request( 'GET', $route );
			$this->assertResponseStatusEquals( 401, $res, $route );
		}

		// Any logged in user can still read the published course's sections.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'student' ) ) );
		foreach ( $routes as $route ) {
			$res = $this->perform_mock_request( 'GET', $route, array(), array( 'include' => array( $section ) ) );
			$this->assertResponseStatusEquals( 200, $res, $route );
		}

	}

	/**
	 * Sections of draft and private courses are only readable by users who can read the course.
	 *
	 * @since 11.0.0
	 *
	 * @return void
	 */
	public function test_get_sections_of_unpublished_courses() {

		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );

		$sections = array();
		foreach ( array( 'publish', 'draft', 'private' ) as $status ) {
			$course = $this->factory->course->create_and_get( array( 'sections' => 1, 'lessons' => 0 ) );
			wp_update_post( array( 'ID' => $course->get( 'id' ), 'post_status' => $status ) );
			$course->set_instructors( array( array( 'id' => $instructor ) ) );
			$sections[ $status ] = $course->get_sections( 'ids' )[0];
		}

		$cases = array(
			'student'           => array( $this->factory->user->create( array( 'role' => 'student' ) ), array( 'publish' ) ),
			'other instructor'  => array( $this->factory->user->create( array( 'role' => 'instructor' ) ), array( 'publish' ) ),
			'course instructor' => array( $instructor, array( 'publish', 'draft', 'private' ) ),
			'administrator'     => array( $this->user_allowed, array( 'publish', 'draft', 'private' ) ),
		);

		foreach ( $cases as $who => $case ) {

			list( $user_id, $readable ) = $case;
			wp_set_current_user( $user_id );

			$expected = array_values( array_intersect_key( $sections, array_flip( $readable ) ) );

			$res = $this->perform_mock_request( 'GET', $this->route, array(), array( 'include' => array_values( $sections ) ) );
			$this->assertResponseStatusEquals( 200, $res, $who );
			$this->assertEqualSets( $expected, wp_list_pluck( $res->get_data(), 'id' ), $who );
			$this->assertEquals( count( $expected ), $res->get_headers()['X-WP-Total'], $who );

			foreach ( $sections as $status => $section ) {
				$res = $this->perform_mock_request( 'GET', $this->route . '/' . $section );
				$this->assertResponseStatusEquals( in_array( $status, $readable, true ) ? 200 : 403, $res, "$who $status" );
			}
		}

	}

}
