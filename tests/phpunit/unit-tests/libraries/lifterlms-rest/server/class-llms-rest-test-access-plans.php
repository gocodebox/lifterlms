<?php
/**
 * Tests for Access Plans API
 *
 * @package LifterLMS_Rest/Tests/Controllers
 *
 * @group REST
 * @group rest_access_plans
 * @group rest_posts
 *
 * @since 1.0.0-beta.18
 */
class LLMS_REST_Test_Access_Plans extends LLMS_REST_Unit_Test_Case_Posts {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'llms_access_plan';

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route = '/llms/v1/access-plans';

	/**
	 * Arguments to access plan API calls.
	 *
	 * @var array
	 */
	protected $sample_access_plan_args = array(
		'price' => '1.99',
		'title' => 'What a title',
	);

	/**
	 * This is an internal flag we use to determine whether or not
	 * we need to use a step of 2 ids when testing the pagination.
	 *
	 * @var array
	 */
	protected $generates_revision_on_creation = false;

	/**
	 * Schema properties
	 *
	 * @var array
	 */
	private $schema_properties = array(
		'access_expiration',
		'access_expires',
		'access_length',
		'access_period',
		'availability_restrictions',
		'content',
		'date_created',
		'date_created_gmt',
		'date_updated',
		'date_updated_gmt',
		'enroll_text',
		'frequency',
		'id',
		'length',
		'menu_order',
		'period',
		'permalink',
		'price',
		'post_id',
		'post_type',
		'redirect_forced',
		'redirect_page',
		'redirect_type',
		'redirect_url',
		'sale_date_end',
		'sale_date_start',
		'sale_enabled',
		'sale_price',
		'sku',
		'title',
		'trial_enabled',
		'trial_length',
		'trial_period',
		'trial_price',
		'visibility',
	);

	/**
	 * Array of link $rels expected for each item.
	 *
	 * @var string[]
	 */
	private $expected_link_rels = array(
		'self',
		'collection',
		'post',
		'restrictions',
	);

	/**
	 *
	 * Setup our test server, endpoints, and user info.
	 *
	 * @since 1.0.0-beta.9
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		$this->endpoint       = new LLMS_REST_Access_Plans_Controller();
		$this->user_allowed   = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$this->user_forbidden = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$this->set_defaults();
	}

	/**
	 * Test the item schema
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_get_item_schema() {
		$schema = $this->endpoint->get_item_schema();

		$this->assertEquals( 'llms_access_plan', $schema['title'] );

		$props = $this->schema_properties;
		$schema_keys = array_keys( $schema['properties'] );

		$this->assertEqualSets( $props, $schema_keys );
	}

	/**
	 * Test list access plans pagination success.
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_get_access_plans_with_pagination() {
		wp_set_current_user( $this->user_allowed );

		$access_plan_ids      = $this->factory->post->create_many( 25, array( 'post_type' => $this->post_type ) );
		$course               = $this->factory->course->create();
		foreach ( $access_plan_ids as $id ) {
			update_post_meta( $id, '_llms_product_id', $course );
		}
		$start_access_plan_id = $access_plan_ids[0];
		$this->pagination_test( $this->route, $start_access_plan_id );
	}

	/**
	 * Test getting single access plan that doesn't exist.
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_get_nonexistent_access_plan() {

		wp_set_current_user( 0 );

		// Setup access plan
		$access_plan_id = $this->factory->post->create( array( 'post_type' => $this->post_type ) );

		$response = $this->perform_mock_request(
			'GET',
			$this->route . '/' . $access_plan_id . '2'
		);

		// The access plan doesn't exist.
		$this->assertResponseStatusEquals( 404, $response );

	}

	/**
	 * Test creating a single access plan.
	 *
	 * @since 1.0.0-beta.20
	 *
	 * @return void
	 */
	public function test_create_and_get_access_plan() {

		wp_set_current_user( $this->user_allowed );

		$course      = $this->factory->course->create_and_get();
		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $course->get( 'id' ),
			)
		);

		$create_response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);
		$access_plan = $create_response->get_data();

		// Success.
		$this->assertResponseStatusEquals( 201, $create_response );

		// Get the access plan.
		$get_response = $this->perform_mock_request(
			'GET',
			trailingslashit( $this->route ) . $access_plan['id'],
			array(),
			array( 'context' => 'edit' )
		);

		$this->assertResponseStatusEquals( 200, $get_response );
		$this->assertEquals( $access_plan, $get_response->get_data() );

		$this->assertEqualSets( $this->schema_properties, array_keys( $get_response->get_data() ) );

	}

	/**
	 * Test producing bad request error when creating a single access-plans
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_create_access_plan_bad_request() {
		wp_set_current_user( $this->user_allowed );

		$course      = $this->factory->course->create_and_get();
		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $course->get( 'id' ),
			)
		);

		// Creating an access plan passing an id produces a bad request.
		$sample_args['id'] = '123';

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		// Bad request.
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseMessageEquals( 'Cannot create existing Access Plans.', $response );

		unset( $sample_args['id'] );

		// Create an access plan without title.
		unset( $sample_args['title'] );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		// Bad request.
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseMessageEquals( 'Missing parameter(s): title', $response );

		$sample_args['title'] = $this->sample_access_plan_args['title'];

		// Create an access plan without price.
		unset( $sample_args['price'] );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		// Bad request.
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseMessageEquals( 'Missing parameter(s): price', $response );


		// Create an access plan without post_id.
		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$this->sample_access_plan_args
		);

		// Bad request.
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseMessageEquals( 'Missing parameter(s): post_id', $response );

	}

	/**
	 * Test access plan alteration is allowed to who can edit parent post
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_altering_access_plan_allowed_to_who_can_edit_parent_post() {

		$instructor = $this->factory->user->create(
			array( 'role' => 'instructor' )
		);
		$assistant  = $this->factory->user->create(
			array( 'role' => 'instructors_assistant' )
		);
		$course     = $this->factory->course->create_and_get();

		// Assign the instructors to the course.
		$course->set_instructors(
			array(
				array(
					'id' => $instructor
				),
				array(
					'id' => $assistant
				),
			)
		);

		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $course->get( 'id' ),
			)
		);

		// Instructors of the Course with post_id can manipulate.
		wp_set_current_user( $instructor );

		// Creation is allowed.
		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		$this->assertResponseStatusEquals( 201, $response );
		$new_plan_id = $response->get_data()['id'];

		// Update is allowed.
		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $new_plan_id,
			array(
				'title' => 'Title can change',
			)
		);

		// Update is allowed.
		$this->assertResponseStatusEquals( 200, $response );

		// Deletion is allowed.
		$response = $this->perform_mock_request(
			'DELETE',
			$this->route . '/' . $new_plan_id
		);
		$this->assertResponseStatusEquals( 204, $response );

		// Check the same happens with intructors assistants

		// Instructor's Assistant of the Course with post_id can manipulate.
		wp_set_current_user( $assistant );

		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $course->get( 'id' ),
			)
		);

		// Creation is allowed.
		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		$this->assertResponseStatusEquals( 201, $response );
		$new_plan_id = $response->get_data()['id'];

		// Update is allowed.
		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $new_plan_id,
			array(
				'title' => 'Title can change',
			)
		);

		// Update is allowed.
		$this->assertResponseStatusEquals( 200, $response );

		// Deletion is allowed.
		$response = $this->perform_mock_request(
			'DELETE',
			$this->route . '/' . $new_plan_id
		);
		$this->assertResponseStatusEquals( 204, $response );

	}

	/**
	 * Test deleting a non existent access plan
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_delete_non_existent_access_plan() {

		wp_set_current_user( $this->user_allowed );

		$response = $this->perform_mock_request(
			'DELETE',
			$this->route . '/12569'
		);

		$this->assertResponseStatusEquals( 204, $response );
		$this->assertEquals( '', $response->get_data() );

	}

	/**
	 * Test updating a non existent access plan
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_update_non_existent_access_plan() {

		wp_set_current_user( $this->user_allowed );

		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/12569',
			$this->sample_access_plan_args
		);

		// Not found.
		$this->assertResponseStatusEquals( 404, $response );

	}

	/**
	 * Test forbidden single access plan creation
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_create_access_plan_forbidden() {
		wp_set_current_user( $this->user_forbidden );

		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $this->factory->course->create(),
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		// Forbidden.
		$this->assertResponseStatusEquals( 403, $response );

		// Check that a generic instructor can't create an access plan.
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'instructor' ) ) );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

	}

	/**
	 * Test creating single access plan without permissions
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_create_access_plan_without_permissions() {
		wp_set_current_user( 0 );

		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $this->factory->course->create(),
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		// Unauthorized.
		$this->assertResponseStatusEquals( 401, $response );

	}

	/**
	 * Test create free access plan
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_create_free_access_plan() {

		wp_set_current_user( $this->user_allowed );

		$course      = $this->factory->course->create_and_get();
		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $course->get( 'id' ),
				'price'   => 0,
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		// Check that the access plan has the following properties values:
		$free_props = array(
			'is_free'     => 'yes',
			'price'       => 0,
			'frequency'   => 0,
			'on_sale'     => 'no',
			'trial_offer' => 'no',
		);

		$ap = new LLMS_Access_Plan( $response->get_data()['id'] );
		foreach ( $free_props as $prop => $value ) {
			$this->assertEquals( $value, $ap->get( $prop ), $prop );
		}

		// Check again, that even the passed properties are "reset".
		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id'       => $course->get( 'id' ),
				'price'         => 0,
				'frequency'     => 6,
				'sale_enabled'  => true,
				'trial_enabled' => true,
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		$ap = new LLMS_Access_Plan( $response->get_data()['id'] );
		foreach ( $free_props as $prop => $value ) {
			$this->assertEquals( $value, $ap->get( $prop ), $prop );
		}

	}

	/**
	 * Test that an access plan cannot be moved onto a product the current user cannot edit.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function test_update_cannot_move_plan_to_unauthorized_product() {

		// Instructor owns course A.
		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );
		wp_set_current_user( $instructor );
		$course_a = $this->factory->course->create_and_get();

		// Create an access plan on course A (allowed: the instructor can edit their own course).
		$create = $this->perform_mock_request(
			'POST',
			$this->route,
			array_merge(
				$this->sample_access_plan_args,
				array( 'post_id' => $course_a->get( 'id' ) )
			)
		);
		$this->assertEquals( 201, $create->get_status() );
		$plan_id = $create->get_data()['id'];

		// Admin owns victim course B.
		wp_set_current_user( $this->user_allowed );
		$course_b = $this->factory->course->create();

		// Back to the instructor: attempt to move the plan onto the victim course B.
		wp_set_current_user( $instructor );
		$update = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $plan_id,
			array_merge(
				$this->sample_access_plan_args,
				array( 'post_id' => $course_b )
			)
		);

		$this->assertEquals( 403, $update->get_status() );

		// The plan must still belong to course A.
		$plan = new LLMS_Access_Plan( $plan_id );
		$this->assertEquals( $course_a->get( 'id' ), $plan->get( 'product_id' ) );
	}

	/**
	 * Test update free access plan.
	 *
	 * @since 1.0.0-beta-24
	 *
	 * @return void
	 */
	public function test_update_free_access_plan() {
		// Create free access plan.
		wp_set_current_user( $this->user_allowed );

		$course      = $this->factory->course->create_and_get();
		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $course->get( 'id' ),
				'price'   => 0,
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		$access_plan_id = $response->get_data()['id'];

		// Update the title.
		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $access_plan_id,
			array(
				'title' => 'Updated Title',
			)
		);

		// Update is allowed.
		$this->assertResponseStatusEquals( 200, $response );
		$ap = new LLMS_Access_Plan( $access_plan_id );
		$this->assertEquals( $ap->get('title'), 'Updated Title' );

	}

	/**
	 * Test create free paid access plan
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_create_paid_access_plan() {
		wp_set_current_user( $this->user_allowed );

		$course      = $this->factory->course->create_and_get();
		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $course->get( 'id' ),
				'price'   => 10,
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		// Check that the access plan has the following properties values:
		$paid_props = array(
			'is_free'     => 'no',
		);

		$ap = new LLMS_Access_Plan( $response->get_data()['id'] );
		foreach ( $paid_props as $prop => $value ) {
			$this->assertEquals( $value, $ap->get( $prop ), $prop );
		}

		// Now test that if the frequency is 0 (default) and we enable the trial, the trial is still disabled.
		$sample_args['trial_enabled'] = true;

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		// Check that the access plan has the following properties values:
		$paid_props = array(
			'is_free'     => 'no',
			'trial_offer' => 'no',
			'frequency'    => 0,
		);

		$ap = new LLMS_Access_Plan( $response->get_data()['id'] );
		foreach ( $paid_props as $prop => $value ) {
			$this->assertEquals( $value, $ap->get( $prop ), $prop );
		}

		// Test that a frequency > 0 unlocks trials.
		$sample_args['frequency'] = 1;
		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);
		$this->assertTrue(
			llms_parse_bool(
				( new LLMS_Access_Plan( $response->get_data()['id'] ) )->get( 'trial_offer' )
			)
		);
	}

	/**
	 * Test availability_restrictions.
	 *
	 * @since 1.0.0-beta-24
	 *
	 * @return void
	 */
	public function test_availability_restrictions() {

		// Create an access plan.
		wp_set_current_user( $this->user_allowed );

		$course      = $this->factory->course->create_and_get();
		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $course->get( 'id' ),
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		$this->assertResponseStatusEquals( 201, $response );
		$this->assertEmpty( $response->get_data()['availability_restrictions'] );

		// Add availability_restrictions.
		// Create two memberships.
		$membership_ids = $this->factory->post->create_many( 2, array( 'post_type' => 'llms_membership' ) );
		$sample_args = array_merge(
			$this->sample_access_plan_args,

		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $response->get_data()['id'],
			array(
				'availability_restrictions' => $membership_ids,
			)
		);

		$this->assertResponseStatusEquals( 200, $response );
		$this->assertEqualSets( $membership_ids, $response->get_data()['availability_restrictions'] );
		$access_plan = new LLMS_Access_Plan( $response->get_data()['id'] );
		$this->assertEquals( 'members', $access_plan->get( 'availability' ) );

		// Turn the product post type to a membership.
		$membership_id = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $response->get_data()['id'],
			array(
				'post_id' => $membership_id,
			)
		);
		$this->assertEquals( 'open', $access_plan->get( 'availability' ) );
		$this->assertEquals( array(), $access_plan->get( 'availability_restrictions' ) );
		$this->assertFalse( array_key_exists( 'availability_restrictions', $response->get_data() ) );
		// Update the access plan related to membership: no issues:
		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $response->get_data()['id'],
			array(
				'title' => 'Update this title'
			)
		);
		$this->assertEquals( 'open', $access_plan->get( 'availability' ) );
		$this->assertEquals( array(), $access_plan->get( 'availability_restrictions' ) );
		$this->assertFalse( array_key_exists( 'availability_restrictions', $response->get_data() ) );

		// Turn back the product post type to a course and assign back the restrictions
		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $response->get_data()['id'],
			array(
				'post_id'                   => $course->get( 'id' ),
				'availability_restrictions' => $membership_ids,
			)
		);
		// Availability restricted again.
		$this->assertEquals( 'members', $access_plan->get( 'availability' ) );
		// Availability restrictions returned for memberships in edit context.
		$this->assertEqualSets( $membership_ids, $response->get_data()['availability_restrictions'] );

		// Flush the availability restrictions.
		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $response->get_data()['id'],
			array(
				'availability_restrictions' => array(),
			)
		);
		$this->assertEquals( 'open', $access_plan->get( 'availability' ) );
		$this->assertEmpty( $response->get_data()['availability_restrictions'] );

	}

	/**
	 * Test availability_restrictions validation.
	 *
	 * @since 1.0.0-beta-24
	 *
	 * @return void
	 */
	public function test_availability_restrictions_validation_error() {

		wp_set_current_user( $this->user_allowed );

		$course      = $this->factory->course->create_and_get();
		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id'                   => $course->get( 'id' ),
				'availability_restrictions' => 7, // Not valid.
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		$this->assertResponseStatusEquals( 400, $response );
		$this->assertEquals( 'Invalid parameter.', $response->get_data()['data']['params']['availability_restrictions'] );

		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id'                   => $course->get( 'id' ),
				'availability_restrictions' => array( 20 ), // Not valid.
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		$this->assertResponseStatusEquals( 400, $response );
		$this->assertEquals( 'Invalid parameter.', $response->get_data()['data']['params']['availability_restrictions'] );

	}

	/**
	 * Test frequency validation.
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_frequency_validation_error() {

		wp_set_current_user( $this->user_allowed );

		$course      = $this->factory->course->create_and_get();
		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id'   => $course->get( 'id' ),
				'frequency' => 7 // Not valid.
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		// Invalid.
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertEquals( 'Must be an integer in the range 0-6', $response->get_data()['data']['params']['frequency'] );

	}

	/**
	 * Test post id validation
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_post_id_validation_error() {

		wp_set_current_user( $this->user_allowed );
		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			array_merge(
				$this->sample_access_plan_args,
				array(
					'post_id' => $this->factory->course->create() + 10, // This post id doesn't exist at all.
				)
			)
		);

		// Invalid.
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertEquals( 'Must be a valid course or membership ID', $response->get_data()['data']['params']['post_id'] );

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			array_merge(
				$this->sample_access_plan_args,
				array(
					'post_id' => $this->factory->post->create(), // This post id is not of a course or membership.
				)
			)
		);
		// Invalid.
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertEquals( 'Must be a valid course or membership ID', $response->get_data()['data']['params']['post_id'] );

		// Test same thing when updating.
		$access_plan_id = $this->factory->post->create( array( 'post_type' => $this->post_type ) );
		update_post_meta( $access_plan_id, '_llms_product_id', $this->factory->course->create() );

		$response = $this->perform_mock_request(
			'POST',
			$this->route . '/' . $access_plan_id,
			array_merge(
				$this->sample_access_plan_args,
				array(
					'post_id' => $this->factory->post->create(), // This post id is not of a course or membership.
				)
			)
		);

		// Blocked.
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertEquals( 'Must be a valid course or membership ID', $response->get_data()['data']['params']['post_id'] );

	}

	/**
	 * Test access plan limit.
	 *
	 * @since 1.0.0-beta.18
	 * @since 1.0.0-beta-24 Check updating an access plan of a product with access plan limit reached.
	 *
	 * @return void
	 */
	public function test_access_plan_limit() {

		wp_set_current_user( $this->user_allowed );

		foreach ( array( 'course', 'llms_membership' ) as $pt ) {

			// Create 5 access plans, by default the limit is 6 per product.
			$access_plan_ids = $this->factory->post->create_many( 5, array( 'post_type' => $this->post_type ) );

			$product = $this->factory->post->create( array( 'post_type' => $pt ) );

			foreach ( $access_plan_ids as $access_plan_id ) {
				update_post_meta( $access_plan_id, '_llms_product_id', $product );
			}

			// Create an ap through api with same product id.
			$response = $this->perform_mock_request(
				'POST',
				$this->route,
				array_merge(
					$this->sample_access_plan_args,
					array(
						'post_id' => $product,
					)
				)
			);
			$sixth_ap = $response->get_data()['id'];
			// The 6th passes.
			$this->assertResponseStatusEquals( 201, $response, $pt );

			// Create the 7th ap.
			$response = $this->perform_mock_request(
				'POST',
				$this->route,
				array_merge(
					$this->sample_access_plan_args,
					array(
						'post_id' => $product,
					)
				)
			);

			// The 7th is blocked.
			$this->assertResponseStatusEquals( 400, $response, $pt );
			$this->assertResponseMessageEquals(
				sprintf(
					'Only %1$d %2$s allowed per %3$s',
					6,
					strtolower( get_post_type_object( $this->post_type )->labels->name ),
					strtolower( get_post_type_object( $pt )->labels->singular_name )
				),
				$response
			);

			// Update the 6th.
			$response = $this->perform_mock_request(
				'POST',
				$this->route . '/' . $sixth_ap,
				array(
					'title' => 'Updated',
				)
			);

			// Update passes.
			$this->assertResponseStatusEquals( 200, $response, $pt );

			// Create an ap linked to a different product.
			$access_plan = $this->factory->post->create( array( 'post_type' => $this->post_type ) );
			$new_product = $this->factory->post->create( array( 'post_type' => $pt ) );
			update_post_meta( $access_plan, '_llms_product_id', $new_product );

			// Update its post_id so that it becomes the 7th ap of the first product.
			$response = $this->perform_mock_request(
				'POST',
				$this->route  . '/' . $access_plan,
				array_merge(
					$this->sample_access_plan_args,
					array(
						'post_id' => $product,
					)
				)
			);

			// The 7th is blocked.
			$this->assertResponseStatusEquals( 400, $response, $pt );
			$this->assertResponseMessageEquals(
				sprintf(
					'Only %1$d %2$s allowed per %3$s',
					6,
					strtolower( get_post_type_object( $this->post_type )->labels->name ),
					strtolower( get_post_type_object( $pt )->labels->singular_name )
				),
				$response
			);
		}
	}

	/**
	 * Test creation defaults respected
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_creation_defaults_respected() {

		wp_set_current_user( $this->user_allowed );

		$course      = $this->factory->course->create_and_get();
		$sample_args = array_merge(
			$this->sample_access_plan_args,
			array(
				'post_id' => $course->get( 'id' ),
			)
		);

		$response = $this->perform_mock_request(
			'POST',
			$this->route,
			$sample_args
		);

		/**
		 * see LLMS_REST_Access_Plans_Controller::unset_subordinate_props()
		 */
		$deps = array(
			'access_length'  => 0, // This is not set if 'access_expiration' is not 'limited-period' (default is 'lifetime').
			'access_period'  => '', // This is not set if 'access_expiration' is not 'limited-period' (default is 'lifetime').
			'access_expires' => '', // This is not set if 'access_expiration' is not 'limited-period' (default is 'lifetime').

			'period' => '' , // This is not set if 'frequency' is 0 (default).

			'trial_length' => 0, // This is not set if 'trial_offer' is 'no' (default).
			'trial_period' => '', // This is not set if 'trial_offer' is 'no' (default).
		);

		foreach ( array_merge( $this->defaults, $deps ) as $prop => $val ) {
			$this->assertEquals( $val, $response->get_data()[$prop], $prop );
		}

	}

	/**
	 * Test filter collection by post_id
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_collection_filtering() {

		wp_set_current_user( $this->user_allowed );

		$access_plan_ids = $this->factory->post->create_many( 5, array( 'post_type' => $this->post_type ) );

		// Link the plans to two different courses.
		$course_one = $this->factory->course->create();
		$course_two = $this->factory->course->create();
		$i = 0;
		foreach ( $access_plan_ids as $access_plan_id ) {
			update_post_meta( $access_plan_id, '_llms_product_id', ${ 0 === ( ++$i % 2 ) ? 'course_one' : 'course_two' } );
		}

		// Filter by first course.
		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'post_id' => $course_one,
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );
		$res_data = $response->get_data();
		$this->assertEquals( array_fill( 0, 2, $course_one ), array_column( $res_data, 'post_id' ) );

		// Filter by second course.
		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'post_id' => $course_two,
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );
		$res_data = $response->get_data();
		$this->assertEquals( array_fill( 0, 3, $course_two ), array_column( $res_data, 'post_id' ) );

		// Filter by both.
		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'post_id' => array(
					$course_two,
					$course_one
				)
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );
		$res_data = $response->get_data();
		$array_of_five = array_fill( 0, 5, null );
		$this->assertEquals(
			array_map(
				function( $val, $i ) use ( $course_one, $course_two ){
					return ${ 0 === ( ++$i % 2 ) ? 'course_one' : 'course_two' };
				},
				$array_of_five,
				array_keys( $array_of_five )
			),
			array_column(
				$res_data, 'post_id'
			)
		);

		// Add another course.
		$access_plan_id = $this->factory->post->create( array( 'post_type' => $this->post_type ) );
		$course_three = $this->factory->course->create();
		update_post_meta( $access_plan_id, '_llms_product_id', $course_three );

		// Check again filtering by one and two.
		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'post_id' => array(
					$course_two,
					$course_one
				)
			)
		);

		// Success.
		$this->assertResponseStatusEquals( 200, $response );
		$res_data = $response->get_data();
		$array_of_five = array_fill( 0, 5, null );
		$this->assertEquals(
			array_map(
				function( $val, $i ) use ( $course_one, $course_two ){
					return ${ 0 === ( ++$i % 2 ) ? 'course_one' : 'course_two' };
				},
				$array_of_five,
				array_keys( $array_of_five )
			),
			array_column(
				$res_data, 'post_id'
			)
		);

	}

	/**
	 * Test links
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return void
	 */
	public function test_links() {

		wp_set_current_user( $this->user_allowed );

		$access_plan_id = $this->factory->post->create( array( 'post_type' => $this->post_type ) );
		$expected_link_rels = $this->expected_link_rels;

		// Link the plan to a course.
		$course = $this->factory->course->create();
		update_post_meta( $access_plan_id, '_llms_product_id', $course );

		// Limit it by membership.
		$membership = $this->factory->membership->create();
		update_post_meta( $access_plan_id, '_llms_availability_restrictions', array( $membership ) );
		update_post_meta( $access_plan_id, '_llms_availability', 'members' );

		$response = $this->perform_mock_request( 'GET', $this->route . '/' . $access_plan_id );

		$this->assertEquals( $expected_link_rels, array_keys( $response->get_links() ) );

		// Expect the related resource is the course.
		$this->assertStringEndsWith(
			sprintf(
				'rest_route=/%s/%s/%s',
				'llms/v1',
				'courses',
				$course
			),
			$response->get_links()['post'][0]['href']
		);

		// Remove availability restrictions.
		update_post_meta( $access_plan_id, '_llms_availability', '' );
		$response = $this->perform_mock_request( 'GET', $this->route . '/' . $access_plan_id );
		unset( $expected_link_rels[ array_search( 'restrictions', $expected_link_rels, true ) ] );

		$this->assertEquals( $expected_link_rels, array_keys( $response->get_links() ) );

		// Link the plan to a membership.
		$membership = $this->factory->post->create( array( 'post_type' => 'llms_membership' ) );
		update_post_meta( $access_plan_id, '_llms_product_id', $membership );

		$response = $this->perform_mock_request( 'GET', $this->route . '/' . $access_plan_id );

		$this->assertEquals( $expected_link_rels, array_keys( $response->get_links() ) );

		// Expect the related resource is the membership.
		$this->assertStringEndsWith(
			sprintf(
				'rest_route=/%s/%s/%s',
				'llms/v1',
				'memberships',
				$membership
			),
			$response->get_links()['post'][0]['href']
		);


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
				'price'   => 0,
				'post_id' => $course_id,
			)
		);

	}

}
