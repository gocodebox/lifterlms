<?php
/**
 * Test API Keys methods.
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group webhooks
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_Webhooks extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Setup the tests.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return  void
	 */
	public function setUp() {

		parent::setUp();
		$this->webhooks = LLMS_REST_API()->webhooks();

	}

	/**
	 * Can't create a webhook with an ID.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_with_id() {

		$ret = $this->webhooks->create( array( 'id' => 1 ) );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_exists', $ret );

	}

	/**
	 * Can't create a webhook with a bad url.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_bad_url() {

		remove_filter( 'llms_rest_webhook_pre_ping', '__return_true' );

		$ret = $this->webhooks->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		) );

		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_ping_unreachable', $ret );

		add_filter( 'llms_rest_webhook_pre_ping', '__return_true' );

	}

	/**
	 * Creation attempts missing required columns should fail.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_required_cols() {

		$to_test = array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		);
		$data = array();

		foreach ( $to_test as $key => $val ) {

			$ret = $this->webhooks->create( $data );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_webhook_missing_' . $key, $ret );
			$data[ $key ] = $val;

		}

	}

	/**
	 * Fill required data with default data during creation.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_with_defaults() {

		$data = array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		);

		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		$ret = $this->webhooks->create( $data );

		$this->assertEquals( 'disabled', $ret->get( 'status' ) );
		$this->assertEquals( 0, $ret->get( 'failure_count' ) );
		$this->assertEquals( 0, $ret->get( 'pending_delivery' ) );
		$this->assertEquals( $user_id, $ret->get( 'user_id' ) );

		$this->assertEquals( 50, strlen( $ret->get( 'secret' ) ) );

		$this->assertEquals( 0, strpos( $ret->get( 'name' ), 'Webhook created on ' ) );

	}

	/**
	 * Create with entirely custom values.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_all_vals() {

		$data = array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
			'name' => 'Mock Webhook Name',
			'status' => 'active',
			'secret' => 'DontTellAnyonePlease',
 		);

		$ret = $this->webhooks->create( $data );

		foreach ( $data as $key => $val ) {
			$this->assertEquals( $val, $ret->get( $key ) );
		}

	}

	/**
	 * Ensure created/updated dates are automatically added during creation.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_dates() {

		$time = current_time( 'timestamp' );
		llms_tests_mock_current_time( $time );

		$hook = $this->webhooks->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		) );

		$this->assertEquals( $time, strtotime( $hook->get( 'created' ) ) );
		$this->assertEquals( $time, strtotime( $hook->get( 'updated' ) ) );

		llms_tests_reset_current_time( $time );

	}

	/**
	 * Test deleting a webhook.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_delete() {

		$hook = $this->webhooks->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		) );

		// Can't delete a non-existant webhook.
		$this->assertFalse( $this->webhooks->delete( $hook->get( 'id' ) + 1 ) );

		// Deleted.
		$this->assertTrue( $this->webhooks->delete( $hook->get( 'id' ) ) );

		// Can't be found.
		$this->assertFalse( $this->webhooks->get( $hook->get( 'id' ) ) );

		// Can't be deleted again.
		$this->assertFalse( $this->webhooks->delete( $hook->get( 'id' ) ) );

	}

	/**
	 * Test hook getter.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get() {

		$hook = $this->webhooks->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		) );

		// Get the hook.
		$get = $this->webhooks->get( $hook->get( 'id' ) );
		$this->assertEquals( $hook->get( 'id' ), $get->get( 'id' ) );

		// Non-existant hook.
		$this->assertFalse( $this->webhooks->get( $hook->get( 'id' ) + 1 ) );

	}

	/**
	 * Can't create with invalid status
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_is_data_valid_status() {

		$data = array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		);

		$tests = array( 'mock', '', false, true );
		foreach ( $tests as $val ) {

			$data['status'] = $val;
			$ret = LLMS_Unit_Test_Util::call_method( $this->webhooks, 'is_data_valid', array( $data ) );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_webhook_invalid_status', $ret );

		}

	}

	/**
	 * Can't create with invalid topic
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_is_data_valid_topic() {

		$data = array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
			'status' => 'disabled',
		);

		$tests = array( 'mock', '', false, true, 'course.fake' );
		foreach ( $tests as $val ) {

			$data['topic'] = $val;
			$ret = LLMS_Unit_Test_Util::call_method( $this->webhooks, 'is_data_valid', array( $data ) );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_webhook_invalid_topic', $ret );

		}

	}

	/**
	 * Can't create/update with an empty description.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_is_data_valid_description() {

		$data = array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
			'status' => 'disabled',
		);

		$tests = array( false, 0, '' );
		foreach ( $tests as $val ) {

			$data['name'] = $val;
			$ret = LLMS_Unit_Test_Util::call_method( $this->webhooks, 'is_data_valid', array( $data ) );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_webhook_invalid_name', $ret );

		}

	}

	/**
	 * Validate delivery url (can't be empty)
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_is_data_valid_delivery_url() {

		$data = array(
			'topic' => 'course.created',
			'status' => 'disabled',
		);

		$tests = array( false, 0, '' );
		foreach ( $tests as $val ) {

			$data['delivery_url'] = $val;
			$ret = LLMS_Unit_Test_Util::call_method( $this->webhooks, 'is_data_valid', array( $data ) );
			$this->assertIsWPError( $ret );
			$this->assertWPErrorCodeEquals( 'llms_rest_webhook_invalid_delivery_url', $ret );

		}

	}

	/**
	 * Test default column values getter
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_default_column_values() {

		$vals = $this->webhooks->get_default_column_values();

		$this->assertEquals( 50, strlen( $vals['secret'] ) );
		$this->assertEquals( 'disabled', $vals['status'] );
		$this->assertEquals( 0, $vals['failure_count'] );
		$this->assertEquals( 0, $vals['pending_delivery'] );

	}

	/**
	 * Test the is_topic_valid() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_is_topic_valid() {

		$tests = array_fill_keys( array_keys( $this->webhooks->get_topics() ), true );

		$tests['action.mock'] = true;
		$tests['course.fake'] = false;
		$tests['courses.created'] = false;
		$tests['action'] = false;
		$tests['action.'] = false;

 		foreach ( $tests as $topic => $expected ) {

 			$this->assertEquals( $expected, LLMS_Unit_Test_Util::call_method( $this->webhooks, 'is_topic_valid', array( $topic ) ), $topic );

 		}

	}

	/**
	 * Can't update without supplying an ID
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_no_id() {

		$hook = $this->webhooks->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		) );

		$ret = $this->webhooks->update( array() );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_missing_id', $ret );

	}

	/**
	 * Can't update something that doesn't exist
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_non_existant() {

		$hook = $this->webhooks->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		) );

		$ret = $this->webhooks->update( array( 'id' => $hook->get( 'id' ) + 1 ) );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_invalid_webhook', $ret );

	}

	/**
	 * Can't supply an empty url during an update
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_blank_url() {

		$hook = $this->webhooks->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		) );

		$ret = $this->webhooks->update( array(
			'id' => $hook->get( 'id' ),
			'delivery_url' => '',
		) );

		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_invalid_delivery_url', $ret );

	}

	/**
	 * test updating
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update() {

		$hook = $this->webhooks->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		) );

		$args = array(
			'id' => $hook->get( 'id' ),
			'topic' => 'action.mock',
			'delivery_url' => 'http://mock.com',
			'pending_delivery' => 1,
			'failure_count' => 5,
			'name' => 'Changed it',
			'secret' => 'new secret',
		);

		$ret = $this->webhooks->update( $args );

		foreach ( $args as $key => $expected ) {

			$this->assertEquals( $expected, $ret->get( $key ) );

		}

	}

	/**
	 * Test updating delivery_url to a bad url.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @return [type]
	 */
	public function test_update_bad_url() {

		$hook = $this->webhooks->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		) );

		remove_filter( 'llms_rest_webhook_pre_ping', '__return_true' );

		$ret = $this->webhooks->update( array(
			'id' => $hook->get( 'id' ),
			'delivery_url' => 'https://mock.tld'
		) );
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_ping_unreachable', $ret );

		add_filter( 'llms_rest_webhook_pre_ping', '__return_true' );

	}

	/**
	 * Test that the updated date is automatically updated during an update.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_updated_date() {

		llms_tests_mock_current_time( strtotime( '-10 minutes' ) );
		$hook = $this->webhooks->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.com',
		) );
		llms_tests_reset_current_time();

		$ret = $this->webhooks->update( array(
			'id' => $hook->get( 'id' ),
			'topic' => 'action.mock',
		) );

		$this->assertTrue( $ret->get( 'updated' ) > $ret->get( 'created' ) );

	}

}
