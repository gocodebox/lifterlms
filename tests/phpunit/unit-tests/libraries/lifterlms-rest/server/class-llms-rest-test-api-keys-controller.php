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
 */
class LLMS_REST_Test_API_Keys_Controller extends LLMS_REST_Unit_Test_Case_Server {

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route = '/llms/v1/api-keys';

	/**
	 * Object type.
	 *
	 * @var string
	 */
	protected $object_type = 'api_key';

	/**
	 * Setup test
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.27 Users creation moved in the `parent::set_up()`.
	 *
	 * @return void
	 */
	public function set_up() {

		parent::set_up();
		$this->endpoint = new LLMS_REST_API_Keys_Controller();

	}

	/**
	 * Teardown test
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

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
	 * Test an LMS Manager cannot create a key owned by an Administrator.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function test_create_item_forbidden_owner() {

		$manager = $this->factory->user->create( array( 'role' => 'lms_manager' ) );
		$admin   = $this->factory->user->create( array( 'role' => 'administrator' ) );

		wp_set_current_user( $manager );

		$args = array(
			'description' => 'admin key',
			'user_id'     => $admin,
			'permissions' => 'read_write',
		);

		$response = $this->perform_mock_request( 'POST', $this->route, $args );
		$this->assertResponseStatusEquals( 403, $response );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $response );

	}

	/**
	 * Test an LMS Manager can create a key owned by themselves.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function test_create_item_self_owner_allowed() {

		$manager = $this->factory->user->create( array( 'role' => 'lms_manager' ) );

		wp_set_current_user( $manager );

		$args = array(
			'description' => 'self key',
			'user_id'     => $manager,
			'permissions' => 'read_write',
		);

		$response = $this->perform_mock_request( 'POST', $this->route, $args );
		$this->assertResponseStatusEquals( 201, $response );
		$this->assertEquals( $manager, $response->get_data()['user_id'] );

	}

	/**
	 * Test an LMS Manager can create a key owned by a user they're allowed to manage.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function test_create_item_manageable_owner_allowed() {

		$manager = $this->factory->user->create( array( 'role' => 'lms_manager' ) );
		$student = $this->factory->user->create( array( 'role' => 'student' ) );

		wp_set_current_user( $manager );

		$args = array(
			'description' => 'student key',
			'user_id'     => $student,
			'permissions' => 'read_write',
		);

		$response = $this->perform_mock_request( 'POST', $this->route, $args );
		$this->assertResponseStatusEquals( 201, $response );
		$this->assertEquals( $student, $response->get_data()['user_id'] );

	}

	/**
	 * Test an LMS Manager cannot change an existing key's owner to an Administrator.
	 *
	 * @since 1.0.6
	 *
	 * @return void
	 */
	public function test_update_item_forbidden_owner_change() {

		$manager = $this->factory->user->create( array( 'role' => 'lms_manager' ) );
		$admin   = $this->factory->user->create( array( 'role' => 'administrator' ) );

		wp_set_current_user( $manager );

		$key = $this->get_mock_api_key( 'read_write', $manager, false );
		$id  = $key->get( 'id' );

		$response = $this->perform_mock_request(
			'POST',
			sprintf( '%1$s/%2$d', $this->route, $id ),
			array( 'user_id' => $admin )
		);

		$this->assertResponseStatusEquals( 403, $response );
		$this->assertResponseCodeEquals( 'llms_rest_forbidden_request', $response );

		// Owner unchanged.
		$this->assertEquals( $manager, LLMS_REST_API()->keys()->get( $id )->get( 'user_id' ) );

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

		$this->pagination_test();

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

	/**
	 * Create resource.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return mixed The resource identifier.
	 */
	protected function create_resource() {
		$args = $this->get_creation_args();
		$key = $this->get_mock_api_key( $args['permissions'], $args['user_id'], false );
		return $key->get( 'id' );
	}

	/**
	 * Get resource creation args.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return array
	 */
	public function get_creation_args() {
		return array(
			'user_id'      => $this->user_allowed,
			'permissions'  => 'read_write',
			'description'  => 'My API Key',
		);
	}

}
