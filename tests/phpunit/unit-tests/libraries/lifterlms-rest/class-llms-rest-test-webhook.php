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
 */
class LLMS_REST_Test_Webhook extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * HTTP args from deliveries captured during a test.
	 *
	 * @var array[]
	 */
	public $delivery_http_requests = array();

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
	 * Record HTTP args from `llms_rest_webhook_delivery`.
	 *
	 * @since [version]
	 *
	 * @param array $http_args HTTP request args.
	 * @return void
	 */
	public function capture_delivery_http_args( $http_args ) {
		$this->delivery_http_requests[] = $http_args;
	}

	/**
	 * One retry delay, used to exhaust the ladder in a single extra attempt.
	 *
	 * @since [version]
	 *
	 * @return int[]
	 */
	public function filter_single_retry_rule() {
		return array( MINUTE_IN_SECONDS );
	}

	/**
	 * Setup the test case.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
	}

	/**
	 * Tear down the test case.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();

		add_filter( 'llms_rest_webhook_pre_ping', '__return_true' );
		remove_filter( 'pre_http_request', array( $this, 'mock_request' ), 10 );
		remove_filter( 'llms_rest_webhook_retry_rules', '__return_empty_array' );
		remove_filter( 'llms_rest_webhook_retry_rules', array( $this, 'filter_single_retry_rule' ) );
		remove_action( 'llms_rest_webhook_delivery', array( $this, 'capture_delivery_http_args' ) );
		$this->delivery_http_requests = array();

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
		// No retries, so each failed POST still increments the shared failure count.
		add_filter( 'llms_rest_webhook_retry_rules', '__return_empty_array' );

		$webhook->deliver( array( $course ) );

		$this->assertEquals( 1, $webhook->get( 'failure_count' ) );

		$webhook = $webhook->set( 'failure_count', 5 );

		$webhook->deliver( array( $course ) );

		$this->assertEquals( 6, $webhook->get( 'failure_count' ) );
		$this->assertEquals( 'disabled', $webhook->get( 'status' ) );

	}

	/**
	 * Test delivery success
	 *
	 * @since Unknown
	 * @since 1.0.0-beta.17 Remove checks on `pending_delivery` unused property.
	 * @since [version] Assert no retry is scheduled for the delivery.
	 *
	 * @return void
	 */
	public function test_delivery_success() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/200',
			'topic' => 'course.created',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		$webhook->set( 'failure_count', 3 );

		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );
		add_action( 'llms_rest_webhook_delivery', array( $this, 'capture_delivery_http_args' ) );

		$webhook->deliver( array( $course ) );

		$this->assertEquals( 0, $webhook->get( 'failure_count' ) );

		$retry_args = array(
			'webhook_id'  => $webhook->get( 'id' ),
			'args'        => array( $course ),
			'attempt'     => 2,
			'delivery_id' => $this->delivery_http_requests[0]['headers']['X-LLMS-Delivery-ID'],
		);
		$this->assertFalse( as_has_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $retry_args, 'llms-webhooks' ) );

	}

	/**
	 * A failed delivery waits before counting the failure, and retries reuse the delivery id.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_failed_delivery_schedules_retry() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/400',
			'topic' => 'course.created',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );
		add_action( 'llms_rest_webhook_delivery', array( $this, 'capture_delivery_http_args' ) );

		$this->assertSame(
			array(
				15 * MINUTE_IN_SECONDS,
				HOUR_IN_SECONDS,
				6 * HOUR_IN_SECONDS,
				12 * HOUR_IN_SECONDS,
			),
			LLMS_Unit_Test_Util::call_method( $webhook, 'get_retry_rules' )
		);

		$webhook->deliver( array( $course ) );

		$stored = LLMS_REST_API()->webhooks()->get( $webhook->get( 'id' ) );
		$this->assertEquals( 0, $stored->get( 'failure_count' ) );
		$this->assertEquals( 'active', $stored->get( 'status' ) );

		$delivery_id = $this->delivery_http_requests[0]['headers']['X-LLMS-Delivery-ID'];
		$this->assertSame( '1', $this->delivery_http_requests[0]['headers']['X-LLMS-Delivery-Attempt'] );

		$schedule_args = array(
			'webhook_id'  => $webhook->get( 'id' ),
			'args'        => array( $course ),
			'attempt'     => 2,
			'delivery_id' => $delivery_id,
		);

		$this->assertEqualsWithDelta(
			time() + ( 15 * MINUTE_IN_SECONDS ),
			as_next_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $schedule_args, 'llms-webhooks' ),
			5
		);

		llms_rest_deliver_webhook_async( $webhook->get( 'id' ), array( $course ), 2, $delivery_id );

		$this->assertSame( $delivery_id, $this->delivery_http_requests[1]['headers']['X-LLMS-Delivery-ID'] );
		$this->assertSame( '2', $this->delivery_http_requests[1]['headers']['X-LLMS-Delivery-Attempt'] );
		$this->assertEquals( 0, LLMS_REST_API()->webhooks()->get( $webhook->get( 'id' ) )->get( 'failure_count' ) );

		$third_attempt = $schedule_args;
		$third_attempt['attempt'] = 3;
		$this->assertEqualsWithDelta(
			time() + HOUR_IN_SECONDS,
			as_next_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $third_attempt, 'llms-webhooks' ),
			5
		);

	}

	/**
	 * The shared failure count increments only after the retry list is exhausted.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_failed_delivery_counts_failure_after_retries_exhausted() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/400',
			'topic' => 'course.created',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );
		add_filter( 'llms_rest_webhook_retry_rules', array( $this, 'filter_single_retry_rule' ) );
		add_action( 'llms_rest_webhook_delivery', array( $this, 'capture_delivery_http_args' ) );

		$webhook->deliver( array( $course ) );

		$delivery_id = $this->delivery_http_requests[0]['headers']['X-LLMS-Delivery-ID'];
		$schedule_args = array(
			'webhook_id'  => $webhook->get( 'id' ),
			'args'        => array( $course ),
			'attempt'     => 2,
			'delivery_id' => $delivery_id,
		);

		$this->assertEqualsWithDelta(
			time() + MINUTE_IN_SECONDS,
			as_next_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $schedule_args, 'llms-webhooks' ),
			5
		);
		$this->assertEquals( 0, LLMS_REST_API()->webhooks()->get( $webhook->get( 'id' ) )->get( 'failure_count' ) );

		$webhook->deliver( array( $course ), 2, $delivery_id );

		$stored = LLMS_REST_API()->webhooks()->get( $webhook->get( 'id' ) );
		$this->assertEquals( 1, $stored->get( 'failure_count' ) );
		$this->assertEquals( 'active', $stored->get( 'status' ) );
		$this->assertSame( $delivery_id, $this->delivery_http_requests[1]['headers']['X-LLMS-Delivery-ID'] );
		$this->assertSame( '2', $this->delivery_http_requests[1]['headers']['X-LLMS-Delivery-Attempt'] );

		$schedule_args['attempt'] = 3;
		$this->assertFalse( as_next_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $schedule_args, 'llms-webhooks' ) );

		$stored->set( 'failure_count', 5, true );
		$stored->deliver( array( $course ), 2, $delivery_id );

		$stored = LLMS_REST_API()->webhooks()->get( $webhook->get( 'id' ) );
		$this->assertEquals( 6, $stored->get( 'failure_count' ) );
		$this->assertEquals( 'disabled', $stored->get( 'status' ) );
		$this->assertTrue( $stored->is_disabled_by_delivery_failures() );
		$this->assertTrue( LLMS_Admin_Notices::has_notice( $stored->get_disabled_notice_id() ) );

		// Reactivating starts the failure count over and clears the notice.
		LLMS_REST_API()->webhooks()->update( array(
			'id'     => $stored->get( 'id' ),
			'status' => 'active',
		) );

		$stored = LLMS_REST_API()->webhooks()->get( $webhook->get( 'id' ) );
		$this->assertEquals( 0, $stored->get( 'failure_count' ) );
		$this->assertEquals( 'active', $stored->get( 'status' ) );
		$this->assertFalse( LLMS_Admin_Notices::has_notice( $stored->get_disabled_notice_id() ) );

	}

	/**
	 * A successful delivery clears the failure count and does not schedule a retry.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_successful_retry_resets_failure_count() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/200',
			'topic' => 'course.created',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		$webhook->set( 'failure_count', 3, true );

		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );

		$webhook->deliver( array( $course ), 2, 'delivery-ok' );

		$stored = LLMS_REST_API()->webhooks()->get( $webhook->get( 'id' ) );
		$this->assertEquals( 0, $stored->get( 'failure_count' ) );

		$retry_args = array(
			'webhook_id'  => $webhook->get( 'id' ),
			'args'        => array( $course ),
			'attempt'     => 3,
			'delivery_id' => 'delivery-ok',
		);
		$this->assertFalse( as_has_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $retry_args, 'llms-webhooks' ) );

	}

	/**
	 * Queued retries do not run after the webhook is disabled. The first attempt still can.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_retry_skipped_when_webhook_disabled() {

		$course = $this->factory->course->create( array( 'sections' => 0 ) );

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/400',
			'topic' => 'course.created',
			'status' => 'disabled',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );

		$before = did_action( 'llms_rest_webhook_delivery' );
		$webhook->deliver( array( $course ), 2, 'delivery-skipped' );
		$this->assertSame( $before, did_action( 'llms_rest_webhook_delivery' ) );

		$webhook->deliver( array( $course ) );
		$this->assertSame( $before + 1, did_action( 'llms_rest_webhook_delivery' ) );

	}

	/**
	 * Retry metadata stays out of the payload sent for action topics.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_retry_metadata_is_not_added_to_action_payload() {

		$hook_args = array( 42, 'kept' );

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/200',
			'topic' => 'action.mock_hook',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		add_filter( 'pre_http_request', array( $this, 'mock_request' ), 10, 3 );
		add_action( 'llms_rest_webhook_delivery', array( $this, 'capture_delivery_http_args' ) );

		$webhook->deliver( $hook_args, 3, 'stable-delivery' );

		$body = json_decode( $this->delivery_http_requests[0]['body'], true );

		$this->assertSame( $hook_args, $body['args'] );
		$this->assertArrayNotHasKey( 'attempt', $body );
		$this->assertArrayNotHasKey( 'delivery_id', $body );
		$this->assertArrayNotHasKey( 'retry_count', $body['args'] );
		$this->assertSame( '3', $this->delivery_http_requests[0]['headers']['X-LLMS-Delivery-Attempt'] );
		$this->assertSame( 'stable-delivery', $this->delivery_http_requests[0]['headers']['X-LLMS-Delivery-ID'] );

	}

	/**
	 * Test the webhook payload getter for post resources.
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return void
	 */
	public function test_get_payload_for_posts() {

		$posts = array(
			'course' => 'course',
			'section' => 'section',
			'lesson' => 'lesson',
			// 'membership' => 'llms_membership',
			// 'order' => 'llms_order',
			// 'access_plan' => 'llms_access_plan',
			// 'transaction' => 'llms_transaction',
		);
		foreach ( $posts as $post => $post_type ) {

			$post_id = $this->factory->post->create( array( 'post_type' => $post_type ) );

			// Created.
			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://mock.tld/200',
				'topic' => $post . '.created',
				'status' => 'active',
				'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			) );
			$payload = LLMS_Unit_Test_Util::call_method( $webhook, 'get_payload', array( array( $post_id, null, false ) ) );
			$this->assertEquals( $post_id, $payload['id'] );
			$this->assertArrayHasKey( 'title', $payload );

			// Updated.
			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://mock.tld/200',
				'topic' => $post . '.updated',
				'status' => 'active',
				'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			) );
			$payload = LLMS_Unit_Test_Util::call_method( $webhook, 'get_payload', array( array( $post_id, null ) ) );
			$this->assertEquals( $post_id, $payload['id'] );
			$this->assertArrayHasKey( 'title', $payload );

			// Deleted.
			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://mock.tld/200',
				'topic' => $post . '.deleted',
				'status' => 'active',
				'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			) );
			$payload = LLMS_Unit_Test_Util::call_method( $webhook, 'get_payload', array( array( $post_id ) ) );
			$this->assertEquals( array( 'id' => $post_id ), $payload );

		}

	}

	/**
	 * Test get_payload() method for user webhooks.
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return void
	 */
	public function test_get_payload_for_users() {

		$roles = array( 'student', 'instructor' );

		foreach ( $roles as $role ) {

			$user_id = $this->factory->user->create( array( 'role' => $role ) );

			// Created.
			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://mock.tld/200',
				'topic' => $role . '.created',
				'status' => 'active',
				'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			) );
			$payload = LLMS_Unit_Test_Util::call_method( $webhook, 'get_payload', array( array( $user_id ) ) );
			$this->assertEquals( $user_id, $payload['id'] );
			$this->assertArrayHasKey( 'name', $payload );

			// Updated.
			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://mock.tld/200',
				'topic' => $role . '.updated',
				'status' => 'active',
				'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			) );
			$payload = LLMS_Unit_Test_Util::call_method( $webhook, 'get_payload', array( array( $user_id ) ) );
			$this->assertEquals( $user_id, $payload['id'] );
			$this->assertArrayHasKey( 'name', $payload );

			// Deleted.
			$webhook = LLMS_REST_API()->webhooks()->create( array(
				'delivery_url' => 'https://mock.tld/200',
				'topic' => $role . '.deleted',
				'status' => 'active',
				'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
			) );
			$payload = LLMS_Unit_Test_Util::call_method( $webhook, 'get_payload', array( array( $user_id ) ) );
			$this->assertEquals( array( 'id' => $user_id ), $payload );

		}

	}

	/**
	 * test get_payload() for enrollment resources.
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return void
	 */
	public function test_get_payload_for_enrollments() {

		$user = $this->factory->student->create();
		$course = $this->factory->course->create();

		llms_enroll_student( $user, $course );

		// Created.
		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/200',
			'topic' => 'enrollment.created',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		$payload = LLMS_Unit_Test_Util::call_method( $webhook, 'get_payload', array( array( $user, $course ) ) );
		$this->assertEquals( $user, $payload['student_id'] );
		$this->assertEquals( $course, $payload['post_id'] );

		// Updated.
		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/200',
			'topic' => 'enrollment.updated',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		$payload = LLMS_Unit_Test_Util::call_method( $webhook, 'get_payload', array( array( $user, $course ) ) );
		$this->assertEquals( $user, $payload['student_id'] );
		$this->assertEquals( $course, $payload['post_id'] );

		// Deleted.
		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/200',
			'topic' => 'enrollment.deleted',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		$payload = LLMS_Unit_Test_Util::call_method( $webhook, 'get_payload', array( array( $user, $course ) ) );
		$this->assertEquals( $user, $payload['student_id'] );
		$this->assertEquals( $course, $payload['post_id'] );

	}

	/**
	 * test get_payload() for enrollment resources.
	 *
	 * @since 1.0.0-beta.6
	 *
	 * @return void
	 */
	public function test_get_payload_for_progress() {

		$user = $this->factory->student->create();
		$course = $this->factory->course->create();

		llms_enroll_student( $user, $course );

		// Updated.
		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld/200',
			'topic' => 'progress.updated',
			'status' => 'active',
			'user_id' => $this->factory->user->create( array( 'role' => 'administrator' ) ),
		) );

		$payload = LLMS_Unit_Test_Util::call_method( $webhook, 'get_payload', array( array( $user, $course ) ) );
		$this->assertEquals( $user, $payload['student_id'] );
		$this->assertEquals( $course, $payload['post_id'] );

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

	/**
	 * Test whether a resource is valid
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.11 Test updated to take into account the new way to discriminate between course creation/update
	 *
	 * @return void
	 */
	public function test_is_valid_resource() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://fake.tld',
			'topic' => 'course.created',
		) );

		$course = $this->factory->post->create_and_get( array( 'post_type' => 'course' ) );

		global $wp_current_filter;
		$wp_current_filter = array( 'save_post_course' );
		$this->assertTrue( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_resource', array( array( $course->ID, $course ) ) ) );

		// Alter the post creation date so to simulate an update: A resource is considered created when the hook is executed within 10 seconds of the post creation date.
		$course->post_date = date( 'Y-m-d H:i:s', strtotime('-11 seconds') );
		wp_update_post( $course );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_resource', array( array( $course->ID, $course ) ) ) );
		$wp_current_filter = array();

		// it's a draft.
		$course->post_status = 'auto-draft';
		wp_update_post( $course );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $webhook, 'is_valid_resource', array( array( $course->ID, $course ) ) ) );

	}

	/**
	 * Test is_valid_user_action() method
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
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

	/**
	 * Test scheduling student.created
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
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

	/**
	 * Test scheduling enrollment.created
	 *
	 * @since Unknown
	 *
	 * @return void
	 */
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
	 * Test scheduling a webhook via multiple hooks, ensuring only one is scheduled for delivery.
	 *
	 * @since 1.0.0
	 * @since [version] Count only the webhook's own scheduled actions.
	 *
	 * @return void
	 */
	public function test_multiple_hooks_for_single_webhook_only_schedules_once() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'membership.deleted',
			'status' => 'active',
		) );

		$webhook->enqueue();

		$membership_id = $this->factory->membership->create();

		wp_trash_post($membership_id);
		wp_delete_post($membership_id);

		// Count only this webhook's actions; earlier tests can leave other deliveries pending.
		$actions = as_get_scheduled_actions( array(
			'hook'     => 'lifterlms_rest_deliver_webhook_async',
			'group'    => 'llms-webhooks',
			'per_page' => -1,
		) );
		$actions = array_filter( $actions, function( $action ) use ( $webhook ) {
			return absint( $webhook->get( 'id' ) ) === absint( $action->get_args()['webhook_id'] );
		} );
		$this->assertCount( 1, $actions );
	}

	/**
	 * Test scheduling enrollment.created via a membership enrollment with multiple auto enroll courses.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function test_scheduling_enrollment_created_through_membership() {

		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.tld',
			'topic' => 'enrollment.created',
			'status' => 'active',
		) );

		$webhook->enqueue();

		$student_id       = $this->factory->student->create();
		$membership_id    = $this->factory->membership->create();
		$course_id        = $this->factory->course->create( array( 'sections' => 0 ) );
		$second_course_id = $this->factory->course->create( array( 'sections' => 0));

		$membership = new LLMS_Membership( $membership_id );
		$membership->add_auto_enroll_courses( array( $course_id, $second_course_id ), true );

		$schedule_args_first_course = array(
			'webhook_id' => $webhook->get( 'id' ),
			'args'       => array( $student_id, $course_id ),
		);
		$schedule_args_second_course = array(
			'webhook_id' => $webhook->get( 'id' ),
			'args'       => array( $student_id, $second_course_id ),
		);
		$schedule_args_membership = array(
			'webhook_id' => $webhook->get( 'id' ),
			'args'       => array( $student_id, $membership_id ),
		);

		llms_enroll_student( $student_id, $membership_id );

		$this->assertTrue( as_has_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $schedule_args_first_course, 'llms-webhooks' ) );
		$this->assertTrue( as_has_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $schedule_args_second_course, 'llms-webhooks' ) );
		$this->assertTrue( as_has_scheduled_action( 'lifterlms_rest_deliver_webhook_async', $schedule_args_membership, 'llms-webhooks' ) );
		$this->assertCount(1, as_get_scheduled_actions( array(
			'hook' => 'lifterlms_rest_deliver_webhook_async',
			'args' => $schedule_args_first_course,
			'per_page' => -1
		) ) );
		$this->assertCount(1, as_get_scheduled_actions( array(
			'hook' => 'lifterlms_rest_deliver_webhook_async',
			'args' => $schedule_args_second_course,
			'per_page' => -1
		) ) );
		$this->assertCount(1, as_get_scheduled_actions( array(
			'hook' => 'lifterlms_rest_deliver_webhook_async',
			'args' => $schedule_args_membership,
			'per_page' => -1
		) ) );
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

}
