<?php
/**
 * Tests for Courses API.
 *
 * @package LifterLMS_Rest/Tests/Controllers
 *
 * @group REST
 * @group rest_api_keys
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */
class LLMS_REST_Test_API_Keys_Controller extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Route.
	 *
	 * @var string
	 */
	private $route = '/llms/v1/api-keys';

	/**
	 * Setup test
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function setUp() {

		parent::setUp();
		$this->user_allowed = $this->factory->user->create( array( 'role' => 'administrator', ) );
		$this->user_forbidden = $this->factory->user->create( array( 'role' => 'subscriber', ) );
		$this->endpoint = new LLMS_REST_API_Keys_Controller();

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
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_api_keys" );

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

	}

	public function test_get_item_schema() {

		$schema = $this->endpoint->get_item_schema();
		$this->assertTrue( array_key_exists( '$schema', $schema ) );
		$this->assertTrue( array_key_exists( 'title', $schema ) );
		$this->assertTrue( array_key_exists( 'type', $schema ) );
		$this->assertTrue( array_key_exists( 'properties', $schema ) );
		$this->assertTrue( array_key_exists( 'description', $schema['properties'] ) );
		$this->assertTrue( array_key_exists( 'permissions', $schema['properties'] ) );
		$this->assertTrue( array_key_exists( 'user_id', $schema['properties'] ) );
		$this->assertTrue( array_key_exists( 'truncated_key', $schema['properties'] ) );
		$this->assertTrue( array_key_exists( 'last_access', $schema['properties'] ) );

	}

	/**
	 * Test error responses for creating a key
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_item_errors() {

		// Empty body.
		$response = $this->perform_mock_request( 'POST', $this->route );
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseCodeEquals( 'rest_missing_callback_param', $response );

		// Unauthorized.
		$args = array(
			'description' => 'Mock Description',
			'user_id' => $this->factory->user->create(),
			'permissions' => 'read',
		);
		$response = $this->perform_mock_request( 'POST', $this->route, $args );
		$this->assertResponseStatusEquals( 401, $response );
		$this->assertResponseCodeEquals( 'llms_rest_unauthorized_request', $response );

		// Forbidden.
		wp_set_current_user( $this->user_forbidden );
		$response = $this->perform_mock_request( 'POST', $this->route, $args );
		$this->assertResponseStatusEquals( 403, $response );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $response );

		// Invalid submitted user_id
		wp_set_current_user( $this->user_allowed );
		$args['user_id'] = 9032423402934;
		$response = $this->perform_mock_request( 'POST', $this->route, $args );
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseCodeEquals( 'rest_invalid_param', $response );

	}

	/**
	 * Test creation of a new key success.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_create_item_success() {

		wp_set_current_user( $this->user_allowed );
		$args = array(
			'description' => 'Mock Description',
			'user_id' => $this->factory->user->create(),
			'permissions' => 'read',
		);
		$response = $this->perform_mock_request( 'POST', $this->route, $args );

		$this->assertResponseStatusEquals( 201, $response );

		$res_data = $response->get_data();

		$this->assertEquals( $args['description'], $res_data['description'] );
		$this->assertEquals( $args['user_id'], $res_data['user_id'] );
		$this->assertEquals( $args['permissions'], $res_data['permissions'] );
		$this->assertTrue( array_key_exists( 'id', $res_data ) );
		$this->assertTrue( array_key_exists( 'consumer_key', $res_data ) );
		$this->assertTrue( array_key_exists( 'consumer_secret', $res_data ) );
		$this->assertTrue( array_key_exists( 'last_access', $res_data ) );
		$this->assertEquals( $res_data['truncated_key'], substr( $res_data['consumer_key'], -7 ) );

		$headers = $response->get_headers();
		$this->assertTrue( array_key_exists( 'Location', $headers ) );

		$links = $response->get_links();
		$this->assertTrue( array_key_exists( 'self', $links ) );
		$this->assertTrue( array_key_exists( 'collection', $links ) );
		$this->assertTrue( array_key_exists( 'user', $links ) );

	}

	/**
	 * Test the permissions check methods.
	 *
	 * @return void
	 */
	public function test_check_permissions() {

		$request = new WP_REST_Request( 'GET', $this->route );

		$methods = array(
			'create_item_permissions_check',
			'delete_item_permissions_check',
			'get_item_permissions_check',
			'get_items_permissions_check',
			'update_item_permissions_check',
		);

		// No user.
		wp_set_current_user( null );
		foreach ( $methods as $method ) {
			$res = $this->endpoint->{$method}( $request );
			$this->assertIsWPError( $res );
			$this->assertWPErrorCodeEquals( 'llms_rest_unauthorized_request', $res );
		}


		// Disallowed User.
		wp_set_current_user( $this->user_forbidden );
		foreach ( $methods as $method ) {
			$res = $this->endpoint->{$method}( $request );
			$this->assertIsWPError( $res );
			$this->assertWPErrorCodeEquals( 'llms_rest_forbidden_request', $res );
		}

		// Allowed User.
		wp_set_current_user( $this->user_allowed );
		foreach ( $methods as $method ) {
			$this->assertTrue( $this->endpoint->{$method}( $request ) );
		}

	}

	/**
	 * test the delete_item() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_delete_item() {

		wp_set_current_user( $this->user_allowed );

		$key = $this->get_mock_api_key( 'read_write', $this->user_allowed, false );
		$id = $key->get( 'id' );

		// Successful deletion.
		$response = $this->perform_mock_request( 'DELETE', sprintf( '%1$s/%2$d', $this->route, $id ) );
		$this->assertResponseStatusEquals( 204, $response );
		$this->assertFalse( LLMS_REST_API()->keys()->get( $id ) );

		// Responds 204 even if resource can't be found.
		$response = $this->perform_mock_request( 'DELETE', sprintf( '%1$s/%2$d', $this->route, $id ) );
		$this->assertResponseStatusEquals( 204, $response );

	}

	/**
	 * test the get_item() for an invalid resource.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_item_not_found() {

		wp_set_current_user( $this->user_allowed );

		$key = $this->get_mock_api_key( 'read_write', $this->user_allowed, false );
		$id = $key->get( 'id' ) + 1;

		$response = $this->perform_mock_request( 'GET', sprintf( '%1$s/%2$d', $this->route, $id ) );
		$this->assertResponseStatusEquals( 404, $response );
		$this->assertResponseCodeEquals( 'llms_rest_not_found', $response );

	}

	/**
	 * test the get_item() for an invalid resource.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_item_success() {

		wp_set_current_user( $this->user_allowed );

		$key = $this->get_mock_api_key( 'read_write', $this->user_allowed, false );
		$id = $key->get( 'id' );

		$response = $this->perform_mock_request( 'GET', sprintf( '%1$s/%2$d', $this->route, $id ) );
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();

		$this->assertEquals( $id, $res_data['id'] );
		$this->assertEquals( $key->get( 'description' ), $res_data['description'] );
		$this->assertEquals( $key->get( 'permissions' ), $res_data['permissions'] );
		$this->assertEquals( $key->get( 'user_id' ), $res_data['user_id'] );
		$this->assertEquals( $key->get( 'user_id' ), $res_data['user_id'] );
		$this->assertEquals( $key->get( 'truncated_key' ), $res_data['truncated_key'] );
		$this->assertTrue( array_key_exists( 'last_access', $res_data ) );

		$links = $response->get_links();
		$this->assertTrue( array_key_exists( 'self', $links ) );
		$this->assertTrue( array_key_exists( 'collection', $links ) );
		$this->assertTrue( array_key_exists( 'user', $links ) );

	}

	/**
	 * test the get_items() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_get_items_pagination() {

		wp_set_current_user( $this->user_allowed );

		// No results.
		$response = $this->perform_mock_request( 'GET', $this->route );
		$this->assertResponseStatusEquals( 200, $response );

		// Make keys for remaining tests.
		$keys = $this->create_many_api_keys( 25 );

		// Page 1.
		$response = $this->perform_mock_request( 'GET', $this->route );

		$body = $response->get_data();
		$headers = $response->get_headers();

		$links = $this->parse_link_headers( $response );

		$this->assertResponseStatusEquals( 200, $response );
		$this->assertEquals( 25, $headers['X-WP-Total'] );
		$this->assertEquals( 3, $headers['X-WP-TotalPages'] );
		$this->assertEquals( array( 'first', 'next', 'last' ), array_keys( $links ) );

		$this->assertEquals( range( 1, 10 ), wp_list_pluck( $body, 'id' ) );

		// Page 2.
		$response = $this->perform_mock_request( 'GET', $this->route, array(), array( 'page' => 2 ) );

		$body = $response->get_data();
		$headers = $response->get_headers();

		$links = $this->parse_link_headers( $response );

		$this->assertResponseStatusEquals( 200, $response );
		$this->assertEquals( 25, $headers['X-WP-Total'] );
		$this->assertEquals( 3, $headers['X-WP-TotalPages'] );
		$this->assertEquals( array( 'first', 'prev', 'next', 'last' ), array_keys( $links ) );

		$this->assertEquals( range( 11, 20 ), wp_list_pluck( $body, 'id' ) );

		// Page 3.
		$response = $this->perform_mock_request( 'GET', $this->route, array(), array( 'page' => 3 ) );

		$body = $response->get_data();
		$headers = $response->get_headers();

		$links = $this->parse_link_headers( $response );

		$this->assertResponseStatusEquals( 200, $response );
		$this->assertEquals( 25, $headers['X-WP-Total'] );
		$this->assertEquals( 3, $headers['X-WP-TotalPages'] );
		$this->assertEquals( array( 'first', 'prev', 'last' ), array_keys( $links ) );

		$this->assertEquals( range( 21, 25 ), wp_list_pluck( $body, 'id' ) );

		// Out of bounds.
		$response = $this->perform_mock_request( 'GET', $this->route, array(), array( 'page' => 4 ) );

		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseCodeEquals( 'llms_rest_bad_request', $response );

	}

	/**
	 * test the update_item() method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_update_item() {

		wp_set_current_user( $this->user_allowed );

		$key = $this->get_mock_api_key( 'read_write', $this->user_allowed, false );
		$id = $key->get( 'id' );

		$updates = array(
			'description' => 'New Description',
			'user_id' => $this->factory->user->create(),
			'permissions' => 'read',
		);

		$response = $this->perform_mock_request( 'POST', sprintf( '%1$s/%2$d', $this->route, $id ), $updates );
		$this->assertResponseStatusEquals( 200, $response );

		$res_data = $response->get_data();

		$this->assertEquals( $id, $res_data['id'] );
		$this->assertEquals( $updates['description'], $res_data['description'] );
		$this->assertEquals( $updates['permissions'], $res_data['permissions'] );
		$this->assertEquals( $updates['user_id'], $res_data['user_id'] );
		$this->assertEquals( $key->get( 'truncated_key' ), $res_data['truncated_key'] );

		$links = $response->get_links();
		$this->assertTrue( array_key_exists( 'self', $links ) );
		$this->assertTrue( array_key_exists( 'collection', $links ) );
		$this->assertTrue( array_key_exists( 'user', $links ) );

	}

	/**
	 * Test the prepare collection query args method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return [type]
	 */
	public function test_prepare_collection_query_args() {

		$route = $this->route;

		// Defaults (no args passed).
		$request = new WP_REST_Request( 'GET', $route );
		$args = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'prepare_collection_query_args', array( $request ) );
		$this->assertEquals( array(), $args );

		// Pass order and use default orderby.
		$request = new WP_REST_Request( 'GET', $route );
		$request->set_query_params( array( 'order' => 'desc' ) );
		$args = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'prepare_collection_query_args', array( $request ) );
		$this->assertEquals( array(
			'sort' => array(
				'id' => 'desc',
			),
		), $args );

		// Pass orderby and use default order.
		$request = new WP_REST_Request( 'GET', $route );
		$request->set_query_params( array( 'orderby' => 'last_access' ) );
		$args = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'prepare_collection_query_args', array( $request ) );
		$this->assertEquals( array(
			'sort' => array(
				'last_access' => 'asc',
			),
		), $args );

		// Pass orderby and order.
		$request = new WP_REST_Request( 'GET', $route );
		$request->set_query_params( array(
			'orderby' => 'last_access',
			'order' => 'desc'
		) );
		$args = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'prepare_collection_query_args', array( $request ) );
		$this->assertEquals( array(
			'sort' => array(
				'last_access' => 'desc',
			),
		), $args );

		// Set other args.
		// Pass orderby and order.
		$request = new WP_REST_Request( 'GET', $route );
		$request->set_query_params( array(
			'include' => '1,2,3,4,5',
			'exclude' => '83',
			'user' => '1',
			'user_not_in' => '25,26',
			'permissions' => 'read',
		) );
		$args = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'prepare_collection_query_args', array( $request ) );
		$this->assertEquals( array(
			'include' => range( 1, 5 ),
			'exclude' => array( 83 ),
			'user' => array( 1 ),
			'user_not_in' => array( 25, 26 ),
			'permissions' => 'read',
		), $args );

	}

	/**
	 * Test the validate_user_exists callback method.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function test_validate_user_exists() {

		$this->assertTrue( $this->endpoint->validate_user_exists( $this->user_allowed ) );
		$this->assertFalse( $this->endpoint->validate_user_exists( $this->factory->user->create() + 1 ) );

	}

}
