<?php
/**
 * Tests for Sections API.
 *
 * @package LifterLMS_Rest/Tests/Controllers
 *
 * @group REST
 * @group rest_sections
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_Sections extends LLMS_REST_Unit_Test_Case_Posts {

	/**
	 * Route.
	 *
	 * @var string
	 */
	private $route = '/llms/v1/sections';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'section';

	/**
	 * Setup our test server, endpoints, and user info.
	 */
	public function setUp() {

		parent::setUp();
		$this->user_allowed = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);

		$this->user_forbidden = $this->factory->user->create(
			array(
				'role' => 'subscriber',
			)
		);

		$this->sample_section_args = array(
			'title' => array(
				'rendered' => 'Introduction',
				'raw'      => 'Introduction',
			),
		);

		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'posts', array( 'post_type' => $this->post_type ) );

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

		$i = 0;
		// Check retrieved sections are the same as the generated ones.
		foreach ( $courses as $course ) {
			$course_obj = new LLMS_Course( $course );
			$sections   = $course_obj->get_sections();

			// Easy sequential check as sections are by default oredered by id.
			$j = 0;
			foreach ( $sections as $section ) {
				$res_section = $res_data[ $i + $j ];
				$this->llms_posts_fields_match( $section, $res_section );
				$j++;
			}

			$i++;
		}

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

	protected function filter_expected_fields( $expected, $llms_post ) {

		unset( $expected['content'] );
		$expected[ 'order' ] = $llms_post->get('order');
		$expected[ 'order' ] = $llms_post->get_parent_course();

		return $expected;

	}

}
