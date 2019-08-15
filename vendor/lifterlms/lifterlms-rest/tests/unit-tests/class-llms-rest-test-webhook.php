<?php
/**
 * Test the webhook model class
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group models
 * @group webhooks
 * @group webhook_model
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_Webhook extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Mock `wp_remote_request` via the `pre_http_request`
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @see {Reference}
	 * @link https://developer.wordpress.org/reference/hooks/pre_http_request/
	 *
	 * @param false|array|WP_Error $ret Whether to preempt the response.
	 * @param array $args HTTP Request args.
	 * @param string $url Request url.
	 * @return false|array|WP_Error
	 */
	public function mock_request( $ret, $args, $url ) {

		if ( 'https://mock.tld/400' === $url ) {

			return array(
				'response' => array(
					'code' => 400,
					'message' => 'Bad Request',
				),
			);

		} elseif ( 'https://mock.tld/200' === $url )  {

			return array(
				'response' => array(
					'code' => 200,
					'message' => 'Success',
				),
			);

		}

		return $ret;

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
	}

	/**
	 * Tear down the test case.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();

		add_filter( 'llms_rest_webhook_pre_ping', '__return_true' );
		remove_filter( 'pre_http_request', array( $this, 'mock_request' ), 10 );

	}

	public function test_delivery_errors() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/400',
			'topic' => 'course.created',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );

		$webhook->deliver( array( $course ) );

		$this->assertEquals( 1, $webhook->get( 'failure_count' ) );

		$webhook = $webhook->set( 'failure_count', 5 );

		$webhook->deliver( array( $course ) );

		$this->assertEquals( 6, $webhook->get( 'failure_count' ) );
		$this->assertEquals( 'disabled', $webhook->get( 'status' ) );

	}


	public function test_delivery_success() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/200',
			'topic' => 'course.created',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		$webhook->set( 'pending_delivery', 1 );
		$webhook->set( 'failure_count', 3 );

		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );

		$webhook->deliver( array( $course ) );

		$this->assertEquals( 0, $webhook->get( 'pending_delivery' ) );
		$this->assertEquals( 0, $webhook->get( 'failure_count' ) );

	}

	/**
	 * Test enqueue for an action with a single hook.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_enqueue_single_hook() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'course.created',
		) );

		$this->assertFalse( has_action( 'save_post_course', array( $webhook, 'process_hook' ) ) );
		$webhook->enqueue();
		$this->assertEquals( 10, has_action( 'save_post_course', array( $webhook, 'process_hook' ) ) );

	}

	/**
	 * Test enqueue for an action with a multiple hooks.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_enqueue_multi_hooks() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'enrollment.created',
		) );

		$this->assertFalse( has_action( 'llms_user_course_enrollment_created', array( $webhook, 'process_hook' ) ) );
		$this->assertFalse( has_action( 'llms_user_membership_enrollment_created', array( $webhook, 'process_hook' ) ) );
		$webhook->enqueue();
		$this->assertEquals( 10, has_action( 'llms_user_course_enrollment_created', array( $webhook, 'process_hook' ) ) );
		$this->assertEquals( 10, has_action( 'llms_user_membership_enrollment_created', array( $webhook, 'process_hook' ) ) );

	}

	/**
	 * Test enqueue for a custom action
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_enqueue_custom_action() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'action.mock_hook',
		) );

		$this->assertFalse( has_action( 'mock_hook', array( $webhook, 'process_hook' ) ) );
		$webhook->enqueue();
		$this->assertEquals( 10, has_action( 'mock_hook', array( $webhook, 'process_hook' ) ) );

	}

	/**
	 * Test the get_edit_link() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_edit_link() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'course.created',
		) );

		$this->assertEquals(
			admin_url( 'admin.php?page=llms-settings&tab=rest-api&section=webhooks&edit-webhook=' . $webhook->get( 'id' ) ),
			$webhook->get_edit_link()
		);

	}

	/**
	 * Test the get_delete_link() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_delete_link() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'course.created',
		) );

		$link = $webhook->get_delete_link();

		$this->assertEquals( 0, strpos( admin_url( 'admin.php?page=llms-settings&tab=rest-api&section=webhooks&revoke-webhook=' . $webhook->get( 'id' ) ), $webhook->get_delete_link() ) );
		parse_str( wp_parse_url( $link, PHP_URL_QUERY ), $parts );
		$this->assertTrue( array_key_exists( 'delete-webhook-nonce', $parts ) );

	}

	/**
	 * Test signature generation.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_delivery_signature() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'course.created',
		) );

		$expected_ts = time();
		llms_tests_mock_current_time( time() );
		$expected_payload = wp_json_encode( array( 'mock' => 'test' ) );

		$sig = $webhook->get_delivery_signature( $expected_payload );
		llms_tests_reset_current_time();

		// Make sure the string looks right.
		$this->assertEquals( 0, strpos( $sig, 't=' ) );
		$this->assertEquals( 12, strpos( $sig, ',v1=' ) );

		// Parse the string and run some checks.
		$parsed = array();
		$items  = explode( ',', $sig );
		foreach ( $items as $item ) {
			$item_parts = explode( '=', $item );
			$parsed[ $item_parts[0] ] = $item_parts[1];
		}

		$this->assertEquals( $expected_ts, $parsed['t'] );
		$this->assertArrayHasKey( 'v1', $parsed );

		// recreate the signature and compare.
		$hash = hash_hmac( 'sha256', $expected_ts . '.' . $expected_payload, $webhook->get( 'secret' ) );
		$this->assertEquals( $hash, $parsed['v1'] );

	}

	/**
	 * Test event getter.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_event() {

		$tests = array(
			'course.created' => 'created',
			'course.updated' => 'updated',
			'progress.updated' => 'updated',
			'student.deleted' => 'deleted',
			'action.mock' => 'mock',
			'action.fake' => 'fake',
		);

		foreach ( $tests as $topic => $event ) {

			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://mock.tld',
				'topic' => $topic,
			) );

			$this->assertEquals( $event, $webhook->get_event() );

		}

	}


	/**
	 * test hook getter.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_hooks() {

		foreach ( LLMS_REST_API()->webhooks()->get_hooks() as $topic => $hooks ) {

			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://mock.tld',
				'topic' => $topic,
			) );

			$this->assertEquals( $hooks, $webhook->get_hooks() );

		}

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'action.mock',
		) );

		$this->assertEquals( array( 'mock' => 1 ), $webhook->get_hooks() );

	}

	/**
	 * Test resource getter.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_resource() {

		$tests = array(
			'course.created' => 'course',
			'access_plan.updated' => 'access_plan',
			'progress.updated' => 'progress',
			'student.deleted' => 'student',
			'action.mock' => 'action',
			'action.fake' => 'action',
		);

		foreach ( $tests as $topic => $resource ) {

			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://mock.tld',
				'topic' => $topic,
			) );

			$this->assertEquals( $resource, $webhook->get_resource() );

		}

	}

	/**
	 * Test is pending checker method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_is_pending() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://fake.tld',
			'topic' => 'course.created',
		) );

		$this->assertFalse( $webhook->is_pending() );

		$webhook->set( 'pending_delivery', 1 );

		$this->assertTrue( $webhook->is_pending() );
	}

	/**
	 * Test validity of post actions.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_is_valid_post_action() {

		$post_types = array(
			'course' => false,
			'section' => false,
			'lesson' => false,
			'llms_membership' => false,
			'llms_access_plan' => false,
			'llms_order' => false,
			'llms_transaction' => false,
			'post' => false,
			'page' => false,
		);

		$tests = array(
			'course.deleted' => array_merge( $post_types, array( 'course' => true ) ),
			'section.deleted' => array_merge( $post_types, array( 'section' => true ) ),
			'lesson.deleted' => array_merge( $post_types, array( 'lesson' => true ) ),
			'membership.deleted' => array_merge( $post_types, array( 'llms_membership' => true ) ),
			'access_plan.deleted' => array_merge( $post_types, array( 'llms_access_plan' => true ) ),
			'order.deleted' => array_merge( $post_types, array( 'llms_order' => true ) ),
			'transaction.deleted' => array_merge( $post_types, array( 'llms_transaction' => true ) ),
		);

		foreach ( $tests as $topic => $post_types ) {

			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://fake.tld',
				'topic' => $topic,
			) );

			foreach ( $post_types as $type => $expect ) {

				$post_id = $this->factory->post->create( array( 'post_type' => $type ) );
				$this->assertEquals( $expect, LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_post_action', array( $post_id ) ) );

			}

		}

	}

	public function test_is_valid_resource() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://fake.tld',
			'topic' => 'course.created',
		) );

		$course = $this->factory->post->create_and_get( array( 'post_type' => 'course' ) );

		global $wp_current_filter;
		$wp_current_filter = array( 'save_post_course' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_resource', array( array( $course->ID, $course, false ) ) ) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_resource', array( array( $course->ID, $course, true ) ) ) );
		$wp_current_filter = array();

		// it's a draft.
		$course->post_status = 'auto-draft';
		wp_update_post( $course );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_resource', array( array( $course->ID, $course, false ) ) ) );

	}

	public function test_is_valid_user_action() {

		$student    = $this->factory->student->create();
		$admin      = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$instructor = $this->factory->user->create( array( 'role' => 'instructor' ) );
		$subscriber = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		$fake       = $subscriber + 1;

		// Student topics.
		$topics = array(
			'student.created',
			'student.updated',
			'student.deleted',
		);

		foreach ( $topics as $topic ) {

			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://fake.tld',
				'topic' => $topic,
			) );

			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_user_action', array( $student ) ) );
			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_user_action', array( $admin ) ) );
			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_user_action', array( $instructor ) ) );
			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_user_action', array( $subscriber ) ) );
			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_user_action', array( $fake ) ) );

		}

		// Instructor topics.
		$topics = array(
			'instructor.created',
			'instructor.updated',
			'instructor.deleted',
		);

		foreach ( $topics as $topic ) {

			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://fake.tld',
				'topic' => $topic,
			) );

			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_user_action', array( $student ) ) );
			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_user_action', array( $admin ) ) );
			$this->assertTrue( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_user_action', array( $instructor ) ) );
			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_user_action', array( $subscriber ) ) );
			$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_user_action', array( $fake ) ) );

		}

	}

	public function test_scheduling_student_created() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'student.created',
			'status' => 'active',
		) );

		$webhook->enqueue();

		$schedule_args = array(
			'webhook_id' => $webhook->get( 'id' ),
			'args'       => array( $this->factory->student->create() ),
		);

		$this->assertTrue( false !== as_next_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $schedule_args, 'llms-webhooks' ) );

	}

	public function test_scheduling_enrollment_created() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'enrollment.created',
			'status' => 'active',
		) );

		$webhook->enqueue();

		$student = $this->factory->student->create();
		$course  = $this->factory->course->create( array( 'sections' => 0 ) );

		$schedule_args = array(
			'webhook_id' => $webhook->get( 'id' ),
			'args'       => array( $student, $course ),
		);

		llms_enroll_student( $student, $course );

		$this->assertTrue( false !== as_next_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $schedule_args, 'llms-webhooks' ) );

	}

	/**
	 * Test ping() on unresolveable urls.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_ping_unreachable() {


		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://fake.tld',
			'topic' => 'course.created',
		) );

		remove_filter( 'llms_rest_webhook_pre_ping', '__return_true' );

		$ret = $webhook->ping();
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_ping_unreachable', $ret );

	}

	/**
	 * Test ping() on non 200 responses.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_ping_non_200_status() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/400',
			'topic' => 'course.created',
		) );

		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );
		remove_filter( 'llms_rest_webhook_pre_ping', '__return_true' );

		$ret = $webhook->ping();
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_ping_not_200', $ret );

	}

	/**
	 * Test ping() success.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_ping_success() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/200',
			'topic' => 'course.created',
		) );

		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );
		remove_filter( 'llms_rest_webhook_pre_ping', '__return_true' );

		$this->assertTrue( $webhook->ping() );

	}

	/**
	 * Test the delivery failure setter.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_set_delivery_failure() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'student.created',
			'status' => 'active',
		) );

		$i = 1;
		while ( $i <= 6 ) {

			$webhook = LLMS_Unit_Test_Util::call_method( $webhook, 'set_delivery_failure' );

			$this->assertEquals( $i, $webhook->get( 'failure_count' ) );
			$this->assertEquals( 6 === $i ? 'disabled' : 'active', $webhook->get( 'status' ) );
			$i++;

		}

	}

	/**
	 * Test the status condition of the should_deliver() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_should_deliver_status() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'student.created',
		) );

		// Inactive.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'should_deliver', array( array( $this->factory->student->create() ) ) ) );

		// Active.
		$webhook->set( 'status', 'active' )->save();
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $webhook, 'should_deliver', array( array( $this->factory->student->create() ) ) ) );

	}

	/**
	 * Test the status condition of the should_deliver() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_should_deliver_pending_delivery() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'student.created',
			'status' => 'active',
		) );

		// Not Pending.
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $webhook, 'should_deliver', array( array( $this->factory->student->create() ) ) ) );

		// Pending.
		$webhook->set( 'pending_delivery', 1 )->save();
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'should_deliver', array( array( $this->factory->student->create() ) ) ) );


	}

}
