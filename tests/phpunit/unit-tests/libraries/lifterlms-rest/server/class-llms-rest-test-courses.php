<?php
/**
 * Tests for Courses API.
 *
 * @package LifterLMS_Rest/Tests/Controllers
 *
 * @group REST
 * @group rest_courses
 * @group rest_posts
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0 Removed `generates_revision_on_creation` property, not needed anymore since LifterLMS 7.3.0.
 */
class LLMS_REST_Test_Courses extends LLMS_REST_Unit_Test_Case_Posts {

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route = '/llms/v1/courses';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'course';

	/**
	 * Taxonomies shown in rest.
	 *
	 * @var string[]
	 */
	protected $rest_taxonomies = array(
		'course_cat',
		'course_difficulty',
		'course_tag',
		'course_track',
	);

	/**
	 *
	 * Setup our test server, endpoints, and user info.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.7 Block migration forcing and db cleanup moved in LLMS_REST_Unit_Test_Case_Posts::set_up()
	 * @since 1.0.0-beta.27 Updated sample course args for compatibility with WordPress 6.2.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->endpoint     = new LLMS_REST_Courses_Controller();
		$this->sample_course_args = array(
			'title'        => array(
				'rendered' => 'Getting Started with LifterLMS',
				'raw'      => 'Getting Started with LifterLMS',
			),
			'content'      => array(
				'rendered' => "\\n<h2 class=\"wp-block-heading\">Lorem ipsum dolor sit amet.</h2>\\n\\n\\n\\n<p class=\"wp-block-paragraph\">Expectoque quid ad id, quod quaerebam, respondeas. Nec enim, omnes avaritias si aeque avaritias esse dixerimus, sequetur ut etiam aequas esse dicamus.</p>\\n",
				'raw'      => "<!-- wp:heading -->\\n<h2 class=\"wp-block-heading\">Lorem ipsum dolor sit amet.</h2>\\n<!-- /wp:heading -->\\n\\n<!-- wp:paragraph -->\\n<p>Expectoque quid ad id, quod quaerebam, respondeas. Nec enim, omnes avaritias si aeque avaritias esse dixerimus, sequetur ut etiam aequas esse dicamus.</p>\\n<!-- /wp:paragraph -->",
			),
			'date_created' => '2019-05-20 17:22:05',
			'status'       => 'publish',
		);

	}

	/**
	 * Test route registration.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_register_routes() {

		$routes = $this->server->get_routes();
		$this->assertArrayHasKey( $this->route, $routes );
		$this->assertArrayHasKey( $this->route . '/(?P<id>[\d]+)', $routes );

		// Enrollments.
		$this->assertArrayHasKey( $this->route . '/(?P<id>[\d]+)/enrollments', $routes );
		// Child sections.
		$this->assertArrayHasKey( $this->route . '/(?P<id>[\d]+)/content', $routes );
	}


	/**
	 * Test list courses.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_get_courses() {

		wp_set_current_user( $this->user_allowed );

		// create 12 courses.
		$courses = $this->factory->course->create_many( 12, array( 'sections' => 0 ) );

		$response = $this->perform_mock_request(
			'GET',
			$this->route
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();
		$this->assertEquals( 10, count( $res_data ) ); // default per_page is 10.

		// Check retrieved courses are the same as the generated ones.
		// Note: the check can be done in this simple way as by default the rest api courses are ordered by id.
		for ( $i = 0; $i < 10; $i++ ) {
			$this->llms_posts_fields_match( new LLMS_Course( $courses[ $i ] ), $res_data[ $i ] );
		}

		$headers = $response->get_headers();
		$this->assertEquals( 12, $headers['X-WP-Total'] );
		$this->assertEquals( 2, $headers['X-WP-TotalPages'] );

	}

	/**
	 * Test list courses pagination success.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @return void
	 */
	public function test_get_courses_with_pagination() {

		wp_set_current_user( $this->user_allowed );

		$course_ids      = $this->factory->course->create_many( 25, array( 'sections' => 0 ) );
		$start_course_id = $course_ids[0];
		$this->pagination_test( $this->route, $start_course_id );

	}

	/**
	 * Test list courses include arg.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_get_courses_include() {

		wp_set_current_user( $this->user_allowed );

		// create 15 courses.
		$courses = $this->factory->course->create_many( 5, array( 'sections' => 0 ) );

		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				// get only the 2nd and 3rd course.
				'include' => "$courses[1], $courses[2]",
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();
		$this->assertEquals( 2, count( $res_data ) );

		// Check retrieved courses are the same as the second and third generated courses.
		for ( $i = 0; $i < 2; $i++ ) {
			$this->llms_posts_fields_match( new LLMS_Course( $courses[ $i + 1 ] ), $res_data[ $i ] );
		}

	}

	/**
	 * Test list courses exclude arg.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_get_courses_exclude() {

		wp_set_current_user( $this->user_allowed );

		// create 15 courses.
		$courses = $this->factory->course->create_many( 5, array( 'sections' => 0 ) );

		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			// esclude 1st and 2nd course.
			array(
				'exclude' => "$courses[0], $courses[1]",
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();
		$this->assertEquals( 3, count( $res_data ) );

		// Check retrieved data do not contain first and second created courses.
		$this->assertEquals( array_slice( $courses, 2 ), wp_list_pluck( $res_data, 'id' ) );
	}

	/**
	 * Test list courses ordered by id desc.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_get_courses_ordered_by_id_desc() {

		wp_set_current_user( $this->user_allowed );

		// create 5 courses.
		$courses = $this->factory->course->create_many( 5, array( 'sections' => 0 ) );

		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'order' => 'desc', // default is 'asc'.
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();
		// Check retrieved courses are the same as the generated ones but in the reversed order.
		// Note: the check can be done in this simple way as by default the rest api courses are ordered by id.
		$reversed_data = array_reverse( $res_data );
		for ( $i = 0; $i < 5; $i++ ) {
			$this->llms_posts_fields_match( new LLMS_Course( $courses[ $i ] ), $reversed_data[ $i ] );
		}

	}

	/**
	 * Test list courses ordered by title.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_get_courses_ordered_by_title() {

		wp_set_current_user( $this->user_allowed );

		// create 3 courses.
		$courses = $this->factory->course->create_many( 3, array( 'sections' => 0 ) );

		$course_first = new LLMS_Course( $courses[0] );
		$course_first->set( 'title', 'Course B' );
		$course_second = new LLMS_Course( $courses[1] );
		$course_second->set( 'title', 'Course A' );
		$course_second = new LLMS_Course( $courses[2] );
		$course_second->set( 'title', 'Course C' );

		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'orderby' => 'title', // default is id.
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();

		// Check retrieved courses are ordered by title asc.
		$this->assertEquals( 'Course A', $res_data[0]['title']['rendered'] );
		$this->assertEquals( 'Course B', $res_data[1]['title']['rendered'] );
		$this->assertEquals( 'Course C', $res_data[2]['title']['rendered'] );

	}

	/**
	 * Test list courses ordered by title
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_get_courses_ordered_by_title_desc() {

		wp_set_current_user( $this->user_allowed );

		// create 3 courses.
		$courses = $this->factory->course->create_many( 3, array( 'sections' => 0 ) );

		$course_first = new LLMS_Course( $courses[0] );
		$course_first->set( 'title', 'Course B' );
		$course_second = new LLMS_Course( $courses[1] );
		$course_second->set( 'title', 'Course A' );
		$course_second = new LLMS_Course( $courses[2] );
		$course_second->set( 'title', 'Course C' );

		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'orderby' => 'title', // default is id.
				'order'   => 'desc', // default is 'asc'.
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();

		// Check retrieved courses are ordered by title desc.
		$this->assertEquals( 'Course C', $res_data[0]['title']['rendered'] );
		$this->assertEquals( 'Course B', $res_data[1]['title']['rendered'] );
		$this->assertEquals( 'Course A', $res_data[2]['title']['rendered'] );
	}

	/**
	 * Test getting courses: bad request.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_get_courses_bad_request() {

		wp_set_current_user( $this->user_allowed );

		// create 5 courses.
		$courses = $this->factory->course->create_many( 5, array( 'sections' => 0 ) );

		// Bad request, there's no page 2.
		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'page' => 2,
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 400, $response );

		// Bad request, order param allowed are only "desc" and "asc" (enum).
		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'order' => 'not_desc',
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 400, $response );

	}

	/**
	 * Test getting a single course.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.8 Added check on `sales_page_*` defaults.
	 *                     Also renamed `sales_page_page_type` and `sales_page_page_url` properties,
	 *                     respectively to `sales_page_type` and `sales_page_url` according to the specs.
	 * @since 1.0.0-beta.9 Added checks on `sales_page_page_id` and
	 *                     `sales_page_page_url` always returned in `edit` context.
	 *                     Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_get_course() {

		wp_set_current_user( $this->user_allowed );

		// Setup course.
		$course   = $this->factory->course->create_and_get();
		$response = $this->perform_mock_request(
			'GET',
			$this->route . '/' . $course->get( 'id' )
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();

		// Check retrieved course matches the created ones.
		$this->llms_posts_fields_match( $course, $res_data );

		// Sales page type.
		$this->assertEquals( 'none', $res_data['sales_page_type'] );
		$this->assertFalse( array_key_exists( 'sales_page_page_id', $res_data ) );
		$this->assertFalse( array_key_exists( 'sales_page_url', $res_data ) );

		// Check that in edit context `sales_page_page_id` and `sales_page_url`,
		// are still returned.
		$response = $this->perform_mock_request(
			'GET',
			$this->route . '/' . $course->get( 'id' ),
			array(),
			array( 'context' => 'edit' )
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();

		$this->assertEquals( 'none', $res_data['sales_page_type'] );
		$this->assertTrue( array_key_exists( 'sales_page_page_id', $res_data ) );
		$this->assertTrue( array_key_exists( 'sales_page_url', $res_data ) );

	}

	/**
	 * Test getting single course that doesn't exist
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_course() {

		wp_set_current_user( 0 );

		// Setup course.
		$course_id = $this->factory->course->create();

		$response = $this->perform_mock_request(
			'GET',
			$this->route . '/' . $course_id . '4'
		);

		// The Course doesn't exist.
		$this->assertResponseStatusEquals( 404, $response );

	}

	/**
	 * Test creating a single course.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.7 Add checks on nullable dates.
	 * @since 1.0.0-beta.8 Add missing quotes in enrollment/access default messages shortcodes.
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_create_course() {

		wp_set_current_user( $this->user_allowed );

		$catalog_visibility = array_keys( llms_get_product_visibility_options() )[2];
		$sample_course_args = array_merge(
			$this->sample_course_args,
			array(
				'catalog_visibility'     => $catalog_visibility,
				'instructors'            => array(
					get_current_user_id(),
					$this->factory->user->create(
						array(
							'role' => 'instructor',
						)
					),
				),
				'video_tile'             => true,
				'access_opens_date'      => '',
				'access_closes_date'     => '',
				'enrollment_opens_date'  => '',
				'enrollment_closes_date' => '',
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_course_args
		);

		// Success.
		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		$this->assertEquals( $sample_course_args['title']['rendered'], $res_data['title']['rendered'] );
		/**
		 * The rtrim below is not ideal but at the moment we have templates printed after the course summary (e.g. prerequisites) that,
		 * even when printing no data they still print "\n". Let's pretend we're not interested in testing the trailing "\n" presence.
		 *
		 * The `wp-block-paragraph` class is only added by newer WordPress versions, strip it so the assertion is version agnostic.
		 */
		$this->assertEquals(
			str_replace( ' class="wp-block-paragraph"', '', rtrim( $sample_course_args['content']['rendered'], "\n" ) ),
			str_replace( ' class="wp-block-paragraph"', '', rtrim( $res_data['content']['rendered'], "\n" ) )
		);

		$this->assertEquals( $sample_course_args['date_created'], $res_data['date_created'] );
		$this->assertEquals( $sample_course_args['status'], $res_data['status'] );
		$this->assertEquals( $sample_course_args['catalog_visibility'], $res_data['catalog_visibility'] );
		$this->assertEquals( $sample_course_args['instructors'], $res_data['instructors'] );
		$this->assertEquals( $sample_course_args['video_tile'], $res_data['video_tile'] );

		$date_props = array(
			'access_opens_date',
			'access_closes_date',
			'enrollment_opens_date',
			'enrollment_closes_date',
		);
		foreach ( $date_props as $empty_prop ) {
			$this->assertEquals( '', $res_data[ $empty_prop ] );
		}
	}

	/**
	 * Test creating a single course without a list of instructors.
	 *
	 * @since 1.0.0-beta.9
	 */
	public function test_create_course_create_with_empty_instructors() {

		wp_set_current_user( $this->user_allowed );

		$response = $this->perform_mock_request( 'POST', $this->route, $this->sample_course_args );

		// Success.
		$this->assertResponseStatusEquals( 201, $response );
		$res_data = $response->get_data();

		// check the instructors post meta has been set as expected.
		$this->assertEquals( array( $this->user_allowed ), wp_list_pluck( get_post_meta( $res_data['id'], '_llms_instructors', true ), 'id' ) );

	}


	/**
	 * Test creating a single course with incorrect list of instructors.
	 *
	 * @since 1.0.0-beta.9
	 */
	public function test_create_course_create_with_bad_instructors_list() {
		wp_set_current_user( $this->user_allowed );

		// The list cannot contain not existent ids.
		$sample_course_args = array_merge(
			$this->sample_course_args,
			array(
				'instructors' => array(
					get_current_user_id(),
					7383931
				),
			)
		);

		$response = $this->perform_mock_request( 'POST', $this->route, $sample_course_args );

		// 400 error.
		$this->assertResponseStatusEquals( 400, $response );

		// The list cannot be empty.
		$sample_course_args = array_merge(
			$this->sample_course_args,
			array(
				'instructors' => array(),
			)
		);

		$response = $this->perform_mock_request( 'POST', $this->route, $sample_course_args );

		// 400 error.
		$this->assertResponseStatusEquals( 400, $response );

	}

	/**
	 * Test creating a single course defaults are correctly set.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.8 Renamed `sales_page_page_type` to `sales_page_type` according to the specs.
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_create_course_check_defaults() {

		wp_set_current_user( $this->user_allowed );

		$course_args = array(
			'title'                  => 'Title',
			'content'                => 'Content',
			'access_opens_date'      => '2019-05-22 17:20:05',
			'access_closes_date'     => '2019-05-22 17:23:08',
			'enrollment_opens_date'  => '2019-05-22 17:22:05',
			'enrollment_closes_date' => '2019-05-22 17:22:08',
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		$res_data = $response->get_data();

		// Check defaults.
		// Access period messages.
		$this->assertEquals( 'This course opens on [lifterlms_course_info id="' . $res_data['id'] . '" key="start_date"].', $res_data['access_opens_message']['raw'] );
		$this->assertEquals( do_shortcode( 'This course opens on [lifterlms_course_info id="' . $res_data['id'] . '" key="start_date"].' ), $res_data['access_opens_message']['rendered'] );
		$this->assertEquals( 'This course closed on [lifterlms_course_info id="' . $res_data['id'] . '" key="end_date"].', $res_data['access_closes_message']['raw'] );
		$this->assertEquals( do_shortcode( 'This course closed on [lifterlms_course_info id="' . $res_data['id'] . '" key="end_date"].' ), $res_data['access_closes_message']['rendered'] );

		// Enrollment period messages.
		$this->assertEquals( 'Enrollment in this course opens on [lifterlms_course_info id="' . $res_data['id'] . '" key="enrollment_start_date"].', $res_data['enrollment_opens_message']['raw'] );
		$this->assertEquals( do_shortcode( 'Enrollment in this course opens on [lifterlms_course_info id="' . $res_data['id'] . '" key="enrollment_start_date"].' ), $res_data['enrollment_opens_message']['rendered'] );
		$this->assertEquals( 'Enrollment in this course closed on [lifterlms_course_info id="' . $res_data['id'] . '" key="enrollment_end_date"].', $res_data['enrollment_closes_message']['raw'] );
		$this->assertEquals( do_shortcode( 'Enrollment in this course closed on [lifterlms_course_info id="' . $res_data['id'] . '" key="enrollment_end_date"].' ), $res_data['enrollment_closes_message']['rendered'] );

		// Capacity enabled.
		$this->assertFalse( $res_data['capacity_enabled'] );

		// Catalog visibility.
		$this->assertEquals( 'catalog_search', $res_data['catalog_visibility'] );

		// Categories.
		$this->assertEquals( array(), $res_data['categories'] );

		// Comment_status.
		$this->assertEquals( 'open', $res_data['comment_status'] );

		// Difficulties.
		$this->assertEquals( array(), $res_data['difficulties'] );

		// Instructors. If empty, llms core responds with the course author in an array.
		$this->assertEquals( array( get_current_user_id() ), $res_data['instructors'] );

		// Menu order.
		$this->assertEquals( 0, $res_data['menu_order'] );

		// Comment_status.
		$this->assertEquals( 'open', $res_data['ping_status'] );

		// Sales page type.
		$this->assertEquals( 'none', $res_data['sales_page_type'] );

		// Status.
		$this->assertEquals( 'publish', $res_data['status'] );

		// Tags.
		$this->assertEquals( array(), $res_data['tags'] );

		// Tracks.
		$this->assertEquals( array(), $res_data['tracks'] );

		// Video tile.
		$this->assertFalse( $res_data['video_tile'] );
	}

	/**
	 * Test creating a single course special props.
	 * These props, when set, alter the rendered content so we test them separetaly.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_create_course_special() {

		wp_set_current_user( $this->user_allowed );

		$sample_course_args = array_merge(
			$this->sample_course_args,
			array(
				'audio_embed'            => 'https://www.youtube.com/abc',
				'video_embed'            => 'www.youtube.com/efg',
				'capacity_limit'         => 22,
				'capacity_enabled'       => true,
				'capacity_message'       => 'Enrollment has closed because the maximum number of allowed students has been reached.',
				'access_opens_date'      => '2019-05-22 17:20:05',
				'access_closes_date'     => '2019-05-22 17:23:08',
				'enrollment_opens_date'  => '2019-05-22 17:22:05',
				'enrollment_closes_date' => '2019-05-22 17:22:08',
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_course_args
		);

		// Success.
		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		$this->assertEquals( esc_url_raw( $sample_course_args['audio_embed'] ), $res_data['audio_embed'] );
		$this->assertEquals( esc_url_raw( $sample_course_args['video_embed'] ), $res_data['video_embed'] );
		$this->assertEquals( $sample_course_args['capacity_enabled'], $res_data['capacity_enabled'] );
		$this->assertEquals( do_shortcode( $sample_course_args['capacity_message'] ), $res_data['capacity_message']['rendered'] );
		$this->assertEquals( $sample_course_args['capacity_limit'], $res_data['capacity_limit'] );

		// Dates.
		$this->assertEquals( $sample_course_args['access_opens_date'], $res_data['access_opens_date'] );
		$this->assertEquals( $sample_course_args['access_closes_date'], $res_data['access_closes_date'] );
		$this->assertEquals( $sample_course_args['enrollment_opens_date'], $res_data['enrollment_opens_date'] );
		$this->assertEquals( $sample_course_args['enrollment_closes_date'], $res_data['enrollment_closes_date'] );
	}

	/**
	 * Test creating a single course with taxonomies
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_create_course_with_taxonomies() {

		wp_set_current_user( $this->user_allowed );
		$taxonomies = array(
			'categories'   => array(
				1,
				2,
				3,
			),
			'tags'         => array(
				6,
				4,
				8,
			),
			'difficulties' => array(
				9,
			),
			'tracks'       => array(
				7,
				5,
				6,
			),
		);

		$course_args = array_merge(
			$this->sample_course_args,
			$taxonomies
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Terms have not ben created, I expect the course is created with empty taxonomies.
		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		foreach ( $taxonomies as $tax => $tid ) {
			$this->assertEquals( array(), $res_data[ $tax ] );
		}

		// let's create the terms.
		$taxonomies = array(
			'categories'   => $this->factory()->term->create_many(
				3,
				array(
					'taxonomy' => 'course_cat',
				)
			),
			'tags'         => $this->factory()->term->create_many(
				3,
				array(
					'taxonomy' => 'course_tag',
				)
			),
			'difficulties' => $this->factory()->term->create_many(
				1,
				array(
					'taxonomy' => 'course_difficulty',
				)
			),
			'tracks'       => $this->factory()->term->create_many(
				3,
				array(
					'taxonomy' => 'course_track',
				)
			),
		);

		$course_args = array_merge(
			$this->sample_course_args,
			$taxonomies
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Terms have been created, I expect the course is created with taxonomies set.
		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		foreach ( $taxonomies as $tax => $tid ) {
			$this->assertEquals( $tid, $res_data[ $tax ] );
		}
	}

	/**
	 * Test creating a single course with taxonomies
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_create_course_with_prerequisites() {
		wp_set_current_user( $this->user_allowed );

		$course_args = array_merge(
			$this->sample_course_args,
			array(
				'prerequisite'       => 2,
				'prerequisite_track' => 5,
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Success.
		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		// Courses with id 2 do not exist, hence I expect an empty prerequisite property.
		$this->assertEquals( 0, $res_data['prerequisite'] );
		// Tracks with id 5 do not exist, hence I expect an empty prerequisite track.
		$this->assertEquals( 0, $res_data['prerequisite_track'] );

		$course = new LLMS_Course( $res_data['id'] );

		// Check that the created course's has_prerequisite is set accordingly.
		$this->assertEquals( 'no', $course->get( 'has_prerequisite' ) );

		// Create a course and a track.
		$prereq_course = $this->factory->course->create( array( 'sections' => 0 ) );
		$prereq_track  = $this->factory()->term->create(
			array(
				'taxonomy' => 'course_track',
			)
		);

		$course_args = array_merge(
			$this->sample_course_args,
			array(
				'prerequisite'       => $prereq_course,
				'prerequisite_track' => $prereq_track,
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Success.
		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		// I expect prerequisites now match.
		$this->assertEquals( $prereq_course, $res_data['prerequisite'] );
		$this->assertEquals( $prereq_track, $res_data['prerequisite_track'] );

		$course = new LLMS_Course( $res_data['id'] );

		// Check that the created course's has_prerequisite is set accordingly.
		$this->assertEquals( 'yes', $course->get( 'has_prerequisite' ) );

	}

	/**
	 * Test course "periods".
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_create_course_and_periods() {

		wp_set_current_user( $this->user_allowed );

		$course_args = array_merge(
			$this->sample_course_args,
			array(
				'access_opens_date'      => '2019-05-22 17:20:05',
				'enrollment_closes_date' => '2019-05-22 17:22:05',
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Success.
		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		$course = new LLMS_Course( $res_data['id'] );

		// Check that the created course's 'time_period' is enabled, since one of access_opens_date and access_closes_date is set.
		$this->assertEquals( 'yes', $course->get( 'time_period' ) );
		// Check that the created course's 'enrollment_period' is enabled, since one of enrollment_opens_date and enrollment_closes_date is set.
		$this->assertEquals( 'yes', $course->get( 'enrollment_period' ) );

		$course_args = array_merge(
			$this->sample_course_args,
			array(
				'access_closes_date'    => '2019-05-22 17:20:05',
				'enrollment_opens_date' => '2019-05-22 17:22:05',
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Success.
		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		$course = new LLMS_Course( $res_data['id'] );

		// Check that the created course's 'time_period' is enabled, since one of access_opens_date and access_closes_date is set.
		$this->assertEquals( 'yes', $course->get( 'time_period' ) );
		// Check that the created course's 'enrollment_period' is enabled, since one of enrollment_opens_date and enrollment_closes_date is set.
		$this->assertEquals( 'yes', $course->get( 'enrollment_period' ) );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$this->sample_course_args
		);

		// Success.
		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		$course = new LLMS_Course( $res_data['id'] );

		// Check that the created course's 'time_period' is not enabled, since none of access_opens_date and access_closes_date is set.
		$this->assertEquals( 'no', $course->get( 'time_period' ) );
		// Check that the created course's 'enrollment_period' is not enabled, since none of enrollment_opens_date and enrollment_closes_date is set.
		$this->assertEquals( 'no', $course->get( 'enrollment_period' ) );

	}

	/**
	 * Test create course with raw properties.
	 * Check textual properties are still set when supplying them as 'raw'.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_create_course_and_raws() {

		wp_set_current_user( $this->user_allowed );

		$course_raw_messages = array(
			'length'                    => array(
				'raw' => 'Length raw message',
			),
			'restricted_message'        => array(
				'raw' => 'Restricted raw message',
			),
			'capacity_message'          => array(
				'raw' => 'Capacity raw message',
			),
			'access_opens_message'      => array(
				'raw' => 'Access opens raw message',
			),
			'access_closes_message'     => array(
				'raw' => 'Access closes raw message',
			),
			'enrollment_opens_message'  => array(
				'raw' => 'Enrollment opens raw message',
			),
			'enrollment_closes_message' => array(
				'raw' => 'Enrollment closess raw message',
			),
		);

		$course_args = array_merge(
			$this->sample_course_args,
			$course_raw_messages
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Success.
		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		foreach ( $course_raw_messages as $property => $content ) {
			$this->assertEquals( $content['raw'], $res_data[ $property ]['raw'] );
		}

	}

	/**
	 * Test producing bad request error when creating a single course.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *                     Use the far less predictable `wp_wp_rand()` in place of `wp_rand()`.
	 * @return void
	 */
	public function test_create_course_bad_request() {

		wp_set_current_user( $this->user_allowed );

		$course_args = $this->sample_course_args;

		// Creating a course passing an id produces a bad request.
		$course_args['id'] = '123';

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Bad request.
		$this->assertResponseStatusEquals( 400, $response );

		// create a course without title.
		$course_args = $this->sample_course_args;
		unset( $course_args['title'] );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Bad request.
		$this->assertResponseStatusEquals( 400, $response );

		// create a course without content.
		$course_args = $this->sample_course_args;
		unset( $course_args['content'] );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Bad request.
		$this->assertResponseStatusEquals( 400, $response );

		// status param must respect the item scehma, hence one of "publish" "pending" "draft" "auto-draft" "future" "private" "trash".
		$course_args           = $this->sample_course_args;
		$status                = array_merge( array_keys( get_post_statuses() ), array( 'future', 'trash', 'auto-draft' ) );
		$course_args['status'] = $status[0] . wp_rand() . 'not_in_enum';

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Bad request.
		$this->assertResponseStatusEquals( 400, $response );

		// catalog_visibility param must respect the item schema, hence one of array_keys( llms_get_product_visibility_options() ).
		$course_args                       = $this->sample_course_args;
		$catalog_visibility                = array_keys( llms_get_product_visibility_options() );
		$course_args['catalog_visibility'] = $catalog_visibility[0] . wp_rand() . 'not_in_enum';

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$course_args
		);

		// Bad request.
		$this->assertResponseStatusEquals( 400, $response );

	}

	/**
	 * Test creating single course without permissions.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_create_course_without_permissions() {

		wp_set_current_user( 0 );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$this->sample_course_args
		);

		// Unhauthorized.
		$this->assertResponseStatusEquals( 401, $response );

	}

	/**
	 * Test forbidden single course creation.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_create_course_forbidden() {

		wp_set_current_user( $this->user_forbidden );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$this->sample_course_args
		);

		// Forbidden.
		$this->assertResponseStatusEquals( 403, $response );

	}


	/**
	 * Test updating a course.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.7 Add tests on prerequisites.
	 * @since 1.0.0-beta.9 Use `$this->assertResponseStatusEquals()` util.
	 *
	 * @return void
	 */
	public function test_update_course() {

		// create a course first.
		$course = $this->factory->course->create_and_get();

		wp_set_current_user( $this->user_allowed );

		// update.
		$update_data = array(
			'title'        => 'A TITLE UPDTAED',
			'content'      => '<p>CONTENT UPDATED</p>',
			'date_created' => '2019-05-22 17:22:05',
			'status'       => 'draft',
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $course->get( 'id' ),
			$update_data
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();

		$this->assertEquals( $update_data['title'], $res_data['title']['raw'] );
		$this->assertEquals( $update_data['title'], $res_data['title']['rendered'] );
		$this->assertEquals( rtrim( apply_filters( 'the_content', $update_data['content'] ), "\n" ), rtrim( $res_data['content']['rendered'], "\n" ) );
		$this->assertEquals( $update_data['date_created'], $res_data['date_created'] );
		$this->assertEquals( $update_data['status'], $res_data['status'] );

		// Check the course has no prerequisites.
		$course = new LLMS_Course( $res_data['id'] );
		$this->assertFalse( $course->has_prerequisite() );

		// Create a course prerequisite.
		$prerequisite_id       = $this->factory->course->create();
		$track                 = wp_insert_term( 'mock track', 'course_track' );
		$prerequisite_track_id = $track['term_id'];

		// Update prerequisites.
		$update_data = array(
			'prerequisite'       => (int) $prerequisite_id,
			'prerequisite_track' => $prerequisite_track_id,
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $course->get( 'id' ),
			$update_data
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();

		// Check the course has prerequisites.
		$course = new LLMS_Course( $res_data['id'] );
		$this->assertTrue( $course->has_prerequisite() );
		$this->assertTrue( $course->has_prerequisite( 'course' ) );
		$this->assertTrue( $course->has_prerequisite( 'course_track' ) );
		$this->assertEquals( $course->get( 'prerequisite' ), $res_data['prerequisite'] );
		$this->assertEquals( $update_data['prerequisite'], $res_data['prerequisite'] );
		$this->assertEquals( $course->get( 'prerequisite_track' ), $res_data['prerequisite_track'] );
		$this->assertEquals( $update_data['prerequisite_track'], $res_data['prerequisite_track'] );

	}

	/**
	 * Test updating a nonexistent course.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_update_nonexistent_course() {

		wp_set_current_user( $this->user_allowed );

		$id = 48987456;

		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $id,
			$this->sample_course_args
		);

		// Not found.
		$this->assertResponseStatusEquals( 404, $response );

	}

	/**
	 * Test forbidden single course update.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_update_forbidden_course() {

		// create a course first.
		$course = $this->factory->course->create_and_get();

		wp_set_current_user( $this->user_forbidden );

		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $course->get( 'id' ),
			$this->sample_course_args
		);

		// Bad request.
		$this->assertResponseStatusEquals( 403, $response );

	}

	/**
	 * Test single course update without authorization.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_update_course_without_authorization() {

		// create a course first.
		$course = $this->factory->course->create_and_get();

		wp_set_current_user( 0 );

		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $course->get( 'id' ),
			$this->sample_course_args
		);

		// Unauthorized.
		$this->assertResponseStatusEquals( 401, $response );

	}

	/**
	 * Test deleting a single course.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_delete_course() {

		wp_set_current_user( $this->user_allowed );

		// create a course first.
		$course = $this->factory->course->create_and_get();

		$response = $this->perform_mock_request(
			'DELETE',
			$this->route . '/' . $course->get( 'id' ),
			array(),
			array(
				'force' => true,
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 204, $response );
		// empty body.
		$this->assertEquals( null, $response->get_data() );

		// Cannot find just deleted post.
		$this->assertFalse( get_post_status( $course->get( 'id' ) ) );

	}

	/**
	 * Test trashing a single course.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_trash_course() {

		wp_set_current_user( $this->user_allowed );

		// create a course first.
		$course = $this->factory->course->create_and_get();

		$response = $this->perform_mock_request(
			'DELETE',
			$this->route . '/' . $course->get( 'id' ),
			array(),
			array(
				'force' => false,
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();
		// Non empty body.
		$this->assertTrue( ! empty( $res_data ) );
		// Deleted post status should be 'trash'.
		$this->assertEquals( 'trash', get_post_status( $course->get( 'id' ) ) );
		// check the trashed post returned into the response is the correct one.
		$this->assertEquals( $course->get( 'id' ), $res_data['id'] );
		// check the trashed post returned into the response has the correct status 'trash'.
		$this->assertEquals( 'trash', $res_data['status'] );

		// Trash again I expect the same as above.
		$response = $this->perform_mock_request(
			'DELETE',
			$this->route . '/' . $course->get( 'id' ),
			array(),
			array(
				'force' => false,
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();
		// Non empty body.
		$this->assertTrue( ! empty( $res_data ) );
		// Deleted post status should be 'trash'.
		$this->assertEquals( 'trash', get_post_status( $course->get( 'id' ) ) );
		// check the trashed post returned into the response is the correct one.
		$this->assertEquals( $course->get( 'id' ), $res_data['id'] );
		// check the trashed post returned into the response has the correct status 'trash'.
		$this->assertEquals( 'trash', $res_data['status'] );

	}

	/**
	 * Test deleting a nonexistent single course.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_delete_nonexistent_course() {

		wp_set_current_user( $this->user_allowed );

		$response = $this->perform_mock_request(
			'DELETE',
			$this->route . '/747484940'
		);

		// Post not found, so it's "deleted".
		$this->assertResponseStatusEquals( 204, $response );
		$this->assertEquals( '', $response->get_data() );

	}

	/**
	 * Test getting bad request response when deleting a course.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_delete_bad_request_course() {

		wp_set_current_user( $this->user_allowed );

		// create a course first.
		$course = $this->factory->course->create_and_get();

		$response = $this->perform_mock_request(
			'DELETE',
			$this->route . '/' . $course->get( 'id' ),
			array(),
			array(
				'force' => 'bad_parameter_value',
			)
		);

		// Bad request because of a bad parameter.
		$this->assertResponseStatusEquals( 400, $response );

	}

	/**
	 * Test single course update without authorization.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_delete_forbidden_course() {

		// create a course first.
		$course = $this->factory->course->create_and_get();

		wp_set_current_user( $this->user_forbidden );

		$response = $this->perform_mock_request(
			'DELETE',
			$this->route . '/' . $course->get( 'id' )
		);

		// Forbidden.
		$this->assertResponseStatusEquals( 403, $response );

	}

	/**
	 * Test single course deletion without authorization.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @return void
	 */
	public function test_delete_course_without_authorization() {

		// create a course first.
		$course = $this->factory->course->create_and_get();

		wp_set_current_user( 0 );

		$response = $this->perform_mock_request(
			'DELETE',
			$this->route . '/' . $course->get( 'id' )
		);

		// Unauthorized.
		$this->assertResponseStatusEquals( 401, $response );

	}

	/**
	 * Test list course content.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @todo test order and orderby
	 *
	 * @return void
	 */
	public function test_get_course_content() {

		wp_set_current_user( $this->user_allowed );

		// create 1 course with no sections.
		$course = $this->factory->course->create(
			array(
				'sections' => 0,
			)
		);

		$response = $this->perform_mock_request(
			'GET',
			$this->route . '/' . $course . '/content'
		);

		// We have no sections for this course so we expect a 404.
		$this->assertResponseStatusEquals( 404, $response );

		// create 1 course with 5 sections.
		$course = $this->factory->course->create(
			array(
				'sections' => 5,
			)
		);

		$response = $this->perform_mock_request(
			'GET',
			$this->route . '/' . $course . '/content'
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();
		$this->assertEquals( 5, count( $res_data ) );

	}

	/**
	 * Test list course content.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Use `$this->perform_mock_request()` and `$this->assertResponseStatusEquals()` utils.
	 *
	 * @todo test order and orderby
	 *
	 * @return void
	 */
	public function test_get_course_enrollments() {

		wp_set_current_user( $this->user_allowed );

		// create 1 course.
		$course = $this->factory->course->create();

		$response = $this->perform_mock_request(
			'GET',
			$this->route . '/' . $course . '/enrollments'
		);

		// We have no students enrolled for this course so we expect a 404.
		$this->assertResponseStatusEquals( 404, $response );

		// create 5 students and enroll them.
		$student_ids = $this->factory->student->create_and_enroll_many( 5, $course );

		$response = $this->perform_mock_request(
			'GET',
			$this->route . '/' . $course . '/enrollments'
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();
		$this->assertEquals( 5, count( $res_data ) );

		// Filter by student_id.
		$response = $this->perform_mock_request(
			'GET',
			$this->route . '/' . $course . '/enrollments',
			array(),
			array(
				'student' => "$student_ids[0]",
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();
		$this->assertEquals( 1, count( $res_data ) );
		$this->assertEquals( $student_ids[0], $res_data[0]['student_id'] );

	}

}
