<?php
/**
 * Test Admin form submissions.
 *
 * @package  LifterLMS_REST/Tests
 *
 * @group admin
 * @group admin_forms
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Test_Admin_Form_Controller extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * Set up the tests.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();

		// Ensure required classes are loaded.
		set_current_screen( 'index.php' );
		LLMS_REST_API()->includes();
		include_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

		$this->obj = new LLMS_REST_Admin_Form_Controller();

	}

	/**
	 * Clean up admin notices between tests
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();
		foreach( LLMS_Admin_Notices::get_notices() as $id ) {
			LLMS_Admin_Notices::delete_notice( $id );
		}

		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_webhooks" );

	}

	/**
	 * Test no events are run on regular admin screens.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_handle_events_no_submit() {

		$this->assertFalse( $this->obj->handle_events() );

	}

	/**
	 * Ensure required field errors are returned when creating a webhook.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_webhook_required_fields() {

		$data = array(
			'llms_rest_webhook_nonce' => wp_create_nonce( 'create-update-webhook' ),
		);

		// Missing topic required field.
		$this->mockPostRequest( $data );
		$ret = $this->obj->handle_events();
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_missing_topic', $ret );

		// Missing delivery url.
		$data['llms_rest_webhook_topic'] = 'course.created';
		$this->mockPostRequest( $data );
		$ret = $this->obj->handle_events();
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_missing_delivery_url', $ret );

		// Success.
		// redirect and exit back to webooks's edit page.
		$data['llms_rest_webhook_delivery_url'] = 'https://mock.tld';
		$this->mockPostRequest( $data );
		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( 'http://example.org/wp-admin/admin.php?page=llms-settings&tab=rest-api&section=webhooks&edit-webhook=1 [301] YES' );

		try {
			$this->obj->handle_events();
		} catch ( LLMS_Unit_Test_Exception_Redirect $exception ) {

			$hook = LLMS_REST_API()->webhooks()->get( 1 );

			$this->assertEquals( 'https://mock.tld', $hook->get( 'delivery_url' ) );
			$this->assertEquals( 'course.created', $hook->get( 'topic' ) );
			$this->assertEquals( 'disabled', $hook->get( 'status' ) );
			$this->assertEquals( 50, strlen( $hook->get( 'secret' ) ) );
			$this->assertEquals( 0, strpos( $hook->get( 'secret' ), 'Webhook created on ' ) );

			throw $exception;

		}

	}

	/**
	 * Ensure all submittable fields are added to the hook on creation.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_webhook_all_fields() {

		$data = array(
			'llms_rest_webhook_nonce' => wp_create_nonce( 'create-update-webhook' ),
			'llms_rest_webhook_name' => 'Webhook Name',
			'llms_rest_webhook_topic' => 'course.created',
			'llms_rest_webhook_secret' => 'myawesomesecret',
			'llms_rest_webhook_delivery_url' => 'https://mock.tld',
			'llms_rest_webhook_status' => 'active',
		);

		$this->mockPostRequest( $data );
		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( 'http://example.org/wp-admin/admin.php?page=llms-settings&tab=rest-api&section=webhooks&edit-webhook=1 [301] YES' );

		try {
			$this->obj->handle_events();
		} catch ( LLMS_Unit_Test_Exception_Redirect $exception ) {

			$hook = LLMS_REST_API()->webhooks()->get( 1 );

			$this->assertEquals( $data['llms_rest_webhook_name'], $hook->get( 'name' ) );
			$this->assertEquals( $data['llms_rest_webhook_topic'], $hook->get( 'topic' ) );
			$this->assertEquals( $data['llms_rest_webhook_secret'], $hook->get( 'secret' ) );
			$this->assertEquals( $data['llms_rest_webhook_delivery_url'], $hook->get( 'delivery_url' ) );
			$this->assertEquals( $data['llms_rest_webhook_status'], $hook->get( 'status' ) );

			throw $exception;

		}

	}

	/**
	 * Test creating a webhook with a custom action.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_webhook_custom_action() {

		$data = array(
			'llms_rest_webhook_nonce' => wp_create_nonce( 'create-update-webhook' ),
			'llms_rest_webhook_name' => 'Webhook Name',
			'llms_rest_webhook_topic' => 'action',
			'llms_rest_webhook_secret' => 'myawesomesecret',
			'llms_rest_webhook_delivery_url' => 'https://mock.tld',
			'llms_rest_webhook_status' => 'active',
		);

		$this->mockPostRequest( $data );
		$ret = $this->obj->handle_events();
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_invalid_topic', $ret );

		$data['llms_rest_webhook_action'] = 'mock';
		$this->mockPostRequest( $data );

		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( 'http://example.org/wp-admin/admin.php?page=llms-settings&tab=rest-api&section=webhooks&edit-webhook=1 [301] YES' );

		try {
			$this->obj->handle_events();
		} catch ( LLMS_Unit_Test_Exception_Redirect $exception ) {

			$hook = LLMS_REST_API()->webhooks()->get( 1 );

			$this->assertEquals( 'action.mock', $hook->get( 'topic' ) );

			throw $exception;

		}

	}

	/**
	 * Test upserting a webhook with weird or invalid ids.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_upsert_webhook_weird_ids() {

		$data = array(
			'llms_rest_webhook_nonce' => wp_create_nonce( 'create-update-webhook' ),
		);

		$tests = array(
			'llms_rest_api_webhook_not_found' => array( 999, '999', ), // Will preform a lookup for numeric values.
			'llms_rest_webhook_missing_topic' => array( 0, '0', 'string', false, null, '', ' ' ), // Will attempt to create.
		);
		foreach ( $tests as $code => $ids ) {

			foreach ( $ids as $id ) {

				$data['llms_rest_webhook_id'] = $id;
				$this->mockPostRequest( $data );

				$ret = $this->obj->handle_events();
				$this->assertIsWPError( $ret );
				$this->assertWPErrorCodeEquals( $code, $ret );

			}

		}

	}

	/**
	 * Test required fields for upserting a webhook.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_webhook_required_fields() {

		$hook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.com',
			'topic' => 'course.created',
		) );

		// Can't nullify delivery url.
		$this->mockPostRequest( array(
			'llms_rest_webhook_nonce' => wp_create_nonce( 'create-update-webhook' ),
			'llms_rest_webhook_id' => $hook->get( 'id' ),
			'llms_rest_webhook_delivery_url' => '',
		) );
		$ret = $this->obj->handle_events();
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_invalid_delivery_url', $ret );

	}


	/**
	 * Test updating an existing webhook.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_webhook() {

		$hook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.com',
			'topic' => 'course.created',
		) );

		$data = array(
			'llms_rest_webhook_nonce' => wp_create_nonce( 'create-update-webhook' ),
			'llms_rest_webhook_id' => $hook->get( 'id' ),

			'llms_rest_webhook_name' => 'Webhook Name',
			'llms_rest_webhook_topic' => 'course.created',
			'llms_rest_webhook_secret' => 'myawesomesecret',
			'llms_rest_webhook_delivery_url' => 'https://mock.tld',
			'llms_rest_webhook_status' => 'active',
		);

		$this->mockPostRequest( $data );

		$this->assertTrue( $this->obj->handle_events() );

		$hook = LLMS_REST_API()->webhooks()->get( $hook->get( 'id' ) );

		$this->assertEquals( $data['llms_rest_webhook_name'], $hook->get( 'name' ) );
		$this->assertEquals( $data['llms_rest_webhook_topic'], $hook->get( 'topic' ) );
		$this->assertEquals( $data['llms_rest_webhook_secret'], $hook->get( 'secret' ) );
		$this->assertEquals( $data['llms_rest_webhook_delivery_url'], $hook->get( 'delivery_url' ) );
		$this->assertEquals( $data['llms_rest_webhook_status'], $hook->get( 'status' ) );

	}

	/**
	 * Test updating a webhook with a custom action.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_webhook_custom_action() {

		$hook = LLMS_REST_API()->webhooks()->create( array(
			'delivery_url' => 'https://mock.com',
			'topic' => 'course.created',
		) );

		$data = array(
			'llms_rest_webhook_nonce' => wp_create_nonce( 'create-update-webhook' ),
			'llms_rest_webhook_id' => $hook->get( 'id' ),

			'llms_rest_webhook_topic' => 'action',
		);

		$this->mockPostRequest( $data );
		$ret = $this->obj->handle_events();
		$this->assertIsWPError( $ret );
		$this->assertWPErrorCodeEquals( 'llms_rest_webhook_invalid_topic', $ret );

		$data['llms_rest_webhook_action'] = 'mock';
		$this->mockPostRequest( $data );

		$this->obj->handle_events();

		$hook = LLMS_REST_API()->webhooks()->get( $hook->get( 'id' ) );

		$this->assertEquals( 'action.mock', $hook->get( 'topic' ) );

	}

	/**
	 * Test the "Revoke" nonce URL for deleting api keys.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.10 Store key id in a variable so we can look it up later.
	 *
	 * @return void
	 */
	public function test_revoke_key() {

		// Key id but no nonce.
		$this->mockGetRequest( array(
			'revoke-key' => 9324234,
		) );
		$this->assertFalse( $this->obj->handle_events() );

		// Nonce present but no key.
		$this->mockGetRequest( array(
			'key-revoke-nonce' => wp_create_nonce( 'revoke' ),
		) );
		$this->assertFalse( $this->obj->handle_events() );

		// Nonce & key but key is fake.
		$this->mockGetRequest( array(
			'revoke-key' => 9324234,
			'key-revoke-nonce' => wp_create_nonce( 'revoke' ),
		) );
		$this->assertFalse( $this->obj->handle_events() );

		// Nonce is fake.
		$this->mockGetRequest( array(
			'revoke-key' => 9324234,
			'key-revoke-nonce' => 'arstarstarst',
		) );
		$this->assertFalse( $this->obj->handle_events() );

		// Real key and real nonce.
		$key = LLMS_REST_API()->keys()->create( array(
			'description' => 'Test Key',
			'user_id' => $this->factory->user->create(),
		) );
		$key_id = $key->get( 'id' );
		$this->mockGetRequest( array(
			'revoke-key' => $key->get( 'id' ),
			'key-revoke-nonce' => wp_create_nonce( 'revoke' ),
		) );

		// redirect and exit back to the keys list.
		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( 'http://example.org/wp-admin/admin.php?page=llms-settings&tab=rest-api&section=keys [302] YES' );

		try {

			$this->obj->handle_events();

		} catch ( LLMS_Unit_Test_Exception_Redirect $exception ) {

			// Key will no longer exist.
			$this->assertFalse( LLMS_REST_API()->keys()->get( $key_id ) );

			// Should have an admin notice.
			$notices = LLMS_Admin_Notices::get_notices();
			$this->assertEquals( 1, count( $notices ) );
			$this->assertEquals( 'The API Key has been successfully deleted.', LLMS_Admin_Notices::get_notice( $notices[0] )['html'] );

			throw $exception;
		}

	}

	/**
	 * Test the delete nonce URL for deleting webhooks.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.10 Store webhook id in a variable so we can look it up later.
	 *
	 * @return void
	 */
	public function test_delete_webhook() {

		// Key id but no nonce.
		$this->mockGetRequest( array(
			'delete-webhook' => 9324234,
		) );
		$this->assertFalse( $this->obj->handle_events() );

		// Nonce present but no key.
		$this->mockGetRequest( array(
			'delete-webhook-nonce' => wp_create_nonce( 'delete' ),
		) );
		$this->assertFalse( $this->obj->handle_events() );

		// Nonce & key but key is fake.
		$this->mockGetRequest( array(
			'delete-webhook' => 9324234,
			'delete-webhook-nonce' => wp_create_nonce( 'delete' ),
		) );
		$this->assertFalse( $this->obj->handle_events() );

		// Nonce is fake.
		$this->mockGetRequest( array(
			'delete-webhook' => 9324234,
			'delete-webhook-nonce' => 'arstarstarst',
		) );
		$this->assertFalse( $this->obj->handle_events() );

		// Real webhook and real nonce.
		$webhook = LLMS_REST_API()->webhooks()->create( array(
			'topic' => 'course.created',
			'delivery_url' => 'https://mock.tld',
		) );
		$webhook_id = $webhook->get( 'id' );
		$this->mockGetRequest( array(
			'delete-webhook' => $webhook->get( 'id' ),
			'delete-webhook-nonce' => wp_create_nonce( 'delete' ),
		) );

		// redirect and exit back to the webhooks list.
		$this->expectException( LLMS_Unit_Test_Exception_Redirect::class );
		$this->expectExceptionMessage( 'http://example.org/wp-admin/admin.php?page=llms-settings&tab=rest-api&section=webhooks [302] YES' );

		try {

			$this->obj->handle_events();

		} catch ( LLMS_Unit_Test_Exception_Redirect $exception ) {

			// Key will no longer exist.
			$this->assertFalse( LLMS_REST_API()->webhooks()->get( $webhook_id ) );

			// Should have an admin notice.
			$notices = LLMS_Admin_Notices::get_notices();
			$this->assertEquals( 1, count( $notices ) );
			$this->assertEquals( 'The webhook has been successfully deleted.', LLMS_Admin_Notices::get_notice( $notices[0] )['html'] );

			throw $exception;
		}

	}

	/**
	 * Test prepare_key_download() method
	 *
	 * @since 1.0.0-beta.3
	 *
	 * @return void
	 */
	public function test_prepare_key_download() {

		// Missing key & id.
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'prepare_key_download' ) );

		// Missing CK.
		$this->mockGetRequest( array(
			'id' => 1,
		) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'prepare_key_download' ) );

		// Missing ID.
		$this->mockGetRequest( array(
			'ck' => 'arst',
		) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'prepare_key_download' ) );

		$key = $this->get_mock_api_key();

		// Fake ID.
		$this->mockGetRequest( array(
			'id' => $key->get( 'id' ) + 1,
			'ck' => 'arst',
		) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'prepare_key_download' ) );

		// Fake CK.
		$this->mockGetRequest( array(
			'id' => $key->get( 'id' ),
			'ck' => 'arst',
		) );
		$this->assertFalse( LLMS_Unit_Test_Util::call_method( $this->obj, 'prepare_key_download' ) );

		$this->mockGetRequest( array(
			'id' => $key->get( 'id' ),
			'ck' => base64_encode( $key->get( 'consumer_key_one_time' ) ),
		) );
		$this->assertEquals( array(
			'fn' => 'Test-Key.txt',
			'ck' => $key->get( 'consumer_key_one_time' ),
			'cs' => $key->get( 'consumer_secret' ),
		), LLMS_Unit_Test_Util::call_method( $this->obj, 'prepare_key_download' ) );

	}

}
