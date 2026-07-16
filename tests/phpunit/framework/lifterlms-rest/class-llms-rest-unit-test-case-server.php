<?php
/**
 * LifterLMS REST API Server Unit Test Case Bootstrap
 *
 * @package LifterLMS_REST_API/Tests
 *
 * @since 1.0.0-beta.1
 */

class LLMS_REST_Unit_Test_Case_Server extends LLMS_REST_Unit_Test_Case_Base {

	/**
	 * @var LLMS_REST_Controller
	 */
	protected $endpoint;

	/**
	 * Server object
	 *
	 * @var WP_REST_Server
	 */
	protected $server;

	/**
	 * Route.
	 *
	 * @var string
	 */
	protected $route;

	/**
	 * ID of user that is allowed to perform an action in the test.
	 *
	 * @var int
	 */
	protected $user_allowed;

	/**
	 * ID of user that is forbidden to an perform action in the test.
	 *
	 * @var int
	 */
	protected $user_forbidden;


	/**
	 * Default schema properties
	 *
	 * @return void
	 */
	protected $defaults;

	/**
	 * Original registered rest fields.
	 *
	 * @var string
	 */
	protected $original_rest_fields;

	/**
	 * Additional rest fields.
	 *
	 * @var array
	 */
	protected $rest_additional_fields;

	/**
	 * Object type.
	 *
	 * Used by meta and additional rest fields.
	 *
	 * @var string
	 */
	protected $object_type;

	/**
	 * Can the resource be created via REST.
	 *
	 * @var boolean
	 */
	protected $is_creatable_via_rest = true;

	/**
	 * Update method.
	 *
	 * @var string
	 */
	protected $update_method = 'POST';

	/**
	 * Setup our test server.
	 *
	 * @since 1.0.0-beta.1
	 */
	public function set_up() {

		parent::set_up();
		$this->server = rest_get_server();
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
	}

	/**
	 * Assert a WP_REST_Response code equals an expected code.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string $expected Expected response code.
	 * @param WP_REST_Response $response Response object.
	 * @return void
	 */
	protected function assertResponseCodeEquals( $expected, WP_REST_Response $response ) {

		$data = $response->get_data();
		$this->assertEquals( $expected, $data['code'] );

	}

	/**
	 * Assert a WP_REST_Response message equals an expected message.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $expected Expected response message.
	 * @param WP_REST_Response $response Response object.
	 * @return void
	 */
	protected function assertResponseMessageEquals( $expected, WP_REST_Response $response ) {

		$data = $response->get_data();
		$this->assertEquals( $expected, $data['message'] );

	}

	/**
	 * Assert a WP_REST_Response status code equals an expected status code.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.18 Added optional message on failure as third param.
	 *
	 * @param int              $expected Expected response http status code.
	 * @param WP_REST_Response $response Response object.
	 * @param string           $message  Optional. Message on failure. Default is empty string.
	 * @return void
	 */
	protected function assertResponseStatusEquals( $expected, WP_REST_Response $response, $msg = '' ) {

		$this->assertEquals( $expected, $response->get_status(), $msg );

	}

	/**
	 * Test collection params contain 'search'.
	 *
	 * @since 1.0.0-beta.21
	 *
	 * @return void
	 */
	public function test_collection_params_contain_search() {

		if ( ! isset( $this->endpoint ) ) {
			$this->markTestSkipped(
				sprintf(
					'No endpoint set, cannot check its collection params. (%1$s)',
					get_class( $this )
				)
			);
		}

		if ( LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'is_searchable' ) ) {
			$this->assertContains(
				'search',
				array_keys( $this->endpoint->get_collection_params() )
			);
		} else {
			$this->assertNotContains(
				'search',
				array_keys( $this->endpoint->get_collection_params() )
			);
		}

	}

	/**
	 * Test allowing 'relevance' orderby
	 *
	 * @since 1.0.0-beta.21
	 *
	 * @return void
	 */
	public function test_allow_relevance_orderby() {

		if ( ! isset( $this->endpoint ) ) {
			$this->markTestSkipped(
				sprintf(
					'No endpoint set, cannot check its collection params. (%1$s)',
					get_class( $this )
				)
			);
		}

		if ( ! LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'is_searchable' ) ) {
			$this->markTestSkipped(
				sprintf(
					'The %1$s endpoint is not searchable',
					LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
				)
			);
		}

		if ( ! in_array( 'relevance', (array) LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'orderby_properties' ), true ) ) {
			$this->markTestSkipped(
				sprintf(
					'The %1$s endpoint\'s orderby_properties property doesn\'t contain "relevance"',
					LLMS_Unit_Test_Util::get_private_property_value( $this->endpoint, 'rest_base' )
				)
			);
		}

		// No search term defined.
		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'orderby' => 'relevance'
			)
		);

		// Bad request.
		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseMessageEquals( 'You need to define a search term to order by relevance.', $response );

		// Search term defined.
		$response = $this->perform_mock_request(
			'GET',
			$this->route,
			array(),
			array(
				'orderby' => 'relevance',
				'search'  => 'a',
			)
		);

		// Fine.
		$this->assertResponseStatusEquals( 200, $response );

	}

	/**
	 * Test schema adding additional fields.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	public function test_schema_with_additional_fields() {

		if ( empty( $this->object_type ) ) {
			$this->markTestSkipped( 'No rest fields to test' );
			return;
		}

		if ( ! method_exists( $this, 'create_resource' ) ) {
			$this->markTestSkipped( 'Cannot run this test, please implement `create_resource()` method.' );
			return;
		}

		wp_set_current_user( $this->user_allowed );
		$this->save_original_rest_additional_fields();

		// Create a resource first.
		$resource_id = $this->create_resource();
		$resource_id = is_array( $resource_id ) ? $resource_id : array( $resource_id );

		// Register a rest field, for this resource.
		$allowed_field = $field = uniqid();
		$this->register_rest_field( $field );

		$response = $this->perform_mock_request( 'GET', $this->get_route( ...$resource_id ) );
		$this->assertArrayHasKey(
			$field,
			$response->get_data(),
			$this->object_type
		);

		// Register a field not for this resource.
		register_rest_field(
			$this->object_type . uniqid(),
			$field . '-unrelated',
			array(
				'get_callback'    => function ( $object ) use ( $field ) {
					return '';
				},
				'update_callback' => function ( $value, $object ) use ( $field ) {
				},
				'schema'          => array(
					'type' => 'string'
				),
			)
		);

		$response = $this->perform_mock_request( 'GET', $this->get_route( ...$resource_id ) );

		$this->assertArrayNotHasKey(
			$field . '-unrelated',
			$response->get_data(),
			$this->object_type
		);

		// Register fields which are not allowed because potentially covered by the schema.
		$schema_properties = LLMS_Unit_Test_Util::call_method( $this->endpoint, 'get_item_schema_base' )['properties'];

		foreach ( $schema_properties as $property => $schema ) {
			$this->register_rest_field( $property );
		}

	}

	/**
	 * Test setting a registered field.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	public function test_set_registered_field_on_creation() {

		$this->markTestSkipped('Fails randomly.');

		if ( empty( $this->object_type ) ) {
			$this->markTestSkipped( 'No rest fields to test' );
			return;
		}

		if ( ! $this->is_creatable_via_rest ) {
			$this->markTestSkipped( 'This resource doesn\'t have a creation endpoint' );
			return;
		}

		wp_set_current_user( $this->user_allowed );
		$this->save_original_rest_additional_fields();

		// Register a rest field, for this resource.
		$field = uniqid();
		$this->register_rest_field( $field );

		$route = $this->get_route();
		$response = $this->perform_mock_request(
			'POST',
			$route,
			array_merge(
				$this->get_creation_args(),
				array(
					$field => "custom_{$field}_value",
				)
			)
		);

		// Check the value.
		$this->assertEquals(
			"custom_{$field}_value",
			$response->get_data()[$field]
		);

	}

	/**
	 * Test setting a registered field.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	public function test_set_registered_field_on_update() {

		$this->markTestSkipped('Fails randomly.');

		if ( empty( $this->object_type ) ) {
			$this->markTestSkipped( 'No rest fields to test' );
			return;
		}

		wp_set_current_user( $this->user_allowed );
		$this->save_original_rest_additional_fields();

		// Register a rest field, for this resource.
		$field = uniqid();
		$this->register_rest_field( $field );

		// Create a resource first.
		$resource_id = $this->create_resource();
		$resource_id = is_array( $resource_id ) ? $resource_id : array( $resource_id );

		$response = $this->perform_mock_request(
			'GET',
			$this->get_route( ...$resource_id )
		);

		// Resource exists, we can update.
		$this->assertResponseStatusEquals( 200, $response );

		$response = $this->perform_mock_request(
			$this->update_method,
			$this->get_route( ...$resource_id ),
			array_merge(
				$this->get_update_args(),
				array(
					$field => "custom_{$field}_value_updated",
				)
			)
		);

		$this->assertEquals(
			"custom_{$field}_value_updated",
			$response->get_data()[$field]
		);

	}

	/**
	 * Get route.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @param mixed $resource_id.
	 * @return string
	 */
	protected function get_route( $resource_id = null ) {
		$route = $resource_id ? $this->route . '/' . $resource_id : $this->route;
		return $route;
	}

	/**
	 * Register rest field.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	protected function register_rest_field( $field ) {

		$object_type = $this->object_type;
		register_rest_field(
			$object_type,
			$field,
			array(
				'get_callback'    => function ( $object ) use ( $field, $object_type ) {
					return $this->get_registered_rest_field_value( $object, $field, $object_type );
				},
				'update_callback' => function ( $value, $object ) use ( $field, $object_type ) {
					return $this->set_registered_rest_field_value( $value, $object, $field, $object_type );
				},
				'schema'          => array(
					'type'        => 'string',
					'arg_options' => array(
						'sanitize_callback' => function ( $value ) {
							// Make the value safe for storage.
							return sanitize_text_field( $value );
						},
					),
				),
			)
		);

	}

	/**
	 * Set rest field value.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @param mixed  $value       The field value.
	 * @param mixed  $object      The prepared object data.
	 * @param string $field       The field name.
	 * @param string $object_type The resource object type.
	 * @return void
	 */
	protected function set_registered_rest_field_value( $value, $object, $field, $object_type ) {
		$this->rest_additional_fields[ $object_type ][ $field ] = $value;
	}

	/**
	 * Get rest field value.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @param mixed  $object      The prepared object data.
	 * @param string $field       The field name.
	 * @param string $object_type The resource object type.
	 * @return mixed
	 */
	protected function get_registered_rest_field_value( $object, $field, $object_type ) {
		return $this->rest_additional_fields[ $object_type ][$field ] ?? "{$field}_default_value";
	}

	/**
	 * Save original rest additional fields.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	protected function save_original_rest_additional_fields() {

		global $wp_rest_additional_fields;
		$this->original_rest_additional_fields = $wp_rest_additional_fields;

	}

	/**
	 * Unregister custom rest fields.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	protected function unregister_rest_additional_fields() {

		if ( ! isset( $this->original_rest_additional_fields ) ) {
			return;
		}

		global $wp_rest_additional_fields;
		$wp_rest_additional_fields = $this->original_rest_additional_fields;
		unset( $this->original_rest_additional_fields );
		unset( $this->rest_additional_fields[ $this->object_type ] );

	}

	/**
	 * Parse the `Link` header to pull all links into an associative array of rel => uri
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Response $response Response object.
	 * @return array
	 */
	protected function parse_link_headers( WP_REST_Response $response ) {

		$headers = $response->get_headers();
		$links = isset( $headers['Link'] ) ? $headers['Link'] : '';

		$parsed = array();
		if ( $links ) {

			foreach ( explode( ',', $links ) as $link ) {
				preg_match( '/<(.*)>; rel="(.*)"/i', trim( $link, ',' ), $match );
				if ( 3 === count( $match ) ) {
					$parsed[ $match[2] ] = $match[1];
				}
			}

		}

		return $parsed;

	}

	/**
	 * Utility to perform pagination test
	 *
	 * @since 1.0.0-beta.7
	 * @since 1.0.0-beta.11 Post revisions are now taken into account when comparing list of resource ids.
	 * @since 1.0.0-beta.17 Better accounting of the automatic creation of post revisions.
	 *
	 * @param string   $route    Optional. Request route, eg: '/llms/v1/courses'. Default empty string, will fall back on this->route.
	 * @param int      $start_id Optional. The id of the first item. Default `1`.
	 * @param int      $per_page Optional. The number of items per page. Default `10`.
	 * @param string   $id_field Optional. The field name for the id item that should be present in the response. Set to empty to not perform any check. Default `'id'`.
	 * @param int      $total    Optional. Total expected items. Default `25`
	 * @param int|null $ids_step Optional. Ids difference between two subsequent resources. Default `null`.
	 * @return void
	 */
	protected function pagination_test( $route = '', $start_id = 1, $per_page = 10, $id_field = 'id', $total = 25, $ids_step = null ) {

		$route       = empty( $route ) ? $this->route : $route;
		$total_pages = (int) ceil( $total / $per_page );
		$initial_id  = $start_id;
		if ( is_null( $ids_step ) ) {
			$ids_step = isset( $this->post_type ) && post_type_supports( $this->post_type, 'revisions' ) && ! empty( $this->generates_revision_on_creation ) ? 2 : 1;
		}

		for ( $i = 1; $i <= $total_pages; $i++ ) {

			$args = array( 'per_page' => $per_page );
			if ( $i > 1 ) {
				$args[ 'page' ] = $i;
			}

			// Page $i.
			$response = $this->perform_mock_request( 'GET', $route, array(), $args );

			$body = $response->get_data();
			$headers = $response->get_headers();

			$links = $this->parse_link_headers( $response );

			$this->assertResponseStatusEquals( 200, $response );
			$this->assertEquals( $total, $headers['X-WP-Total'] );
			$this->assertEquals( $total_pages, $headers['X-WP-TotalPages'] );

			// Pagination links check.
			if ( 1 !== $total_pages ) {
				switch ( $i ) :
					// First page we expect only 'next' and 'last' links.
					case 1 : $links_array = array( 'next', 'last' ); break;
					// Last page we expect only 'first' and 'prev' links.
					case $total_pages: $links_array = array( 'first', 'prev' ); break;
					default : $links_array = array( 'first', 'prev', 'next', 'last' );
				endswitch;

				$this->assertEquals( $links_array, array_keys( $links ) );
			}

			if ( $id_field) {
				$stop_id = ( $i !== $total_pages ) ? ( $start_id + ( $per_page * $ids_step ) - $ids_step ) : ( $initial_id + ( $total * $ids_step ) - $ids_step );
				$this->assertEquals( range( $start_id, $stop_id, $ids_step ), wp_list_pluck( $body, $id_field ) );
				$start_id += $per_page * $ids_step;
			}

		}

		// Big per page.
		$response = $this->perform_mock_request( 'GET', $route, array(), array( 'per_page' => $total + 10 ) );

		// Check Pagination headers.
		$headers = $response->get_headers();
		$this->assertEquals( $total, $headers['X-WP-Total'] );
		$this->assertEquals( 1, $headers['X-WP-TotalPages'] );

		// No links because this is the only page.
		$links = $this->parse_link_headers( $response );
		$this->assertEquals( array(), array_keys( $links ) );

		// Out of bounds.
		$response = $this->perform_mock_request( 'GET', $route, array(), array( 'per_page' => $per_page, 'page' => $total_pages + 1 ) );

		$this->assertResponseStatusEquals( 400, $response );
		$this->assertResponseCodeEquals( 'llms_rest_bad_request', $response );
	}

	/**
	 * Preform a mock WP_REST_Request
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string $method Request method.
	 * @param string $route  Request route, eg: '/llms/v1/courses'.
	 * @param array  $body   Optional request body.
	 * @param array  $query  Optional query arguments.
	 * @return WP_REST_Response
	 */
	protected function perform_mock_request( $method, $route, $body = array(), $query = array() ) {

		$request = new WP_REST_Request( $method, $route );
		if ( $body ) {
			$request->set_body_params( $body );
		}
		if( $query ) {
			$request->set_query_params( $query );
		}
		return $this->server->dispatch( $request );

	}

	/**
	 * Retrieve default properties from the endpoint schema
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return array
	 */
	public function get_defaults() {

		$props    = $this->endpoint->get_item_schema()['properties'];
		$defaults = array();

		foreach ( $props as $prop => $options ) {
			if ( isset( $options['default'] ) ) {
				$defaults[ $prop ] = $options['default'];
			}
		}

		return $defaults;
	}

	/**
	 * Set default properties from the endpoint schema
	 *
	 * @since 1.0.0-beta.18
	 *
	 * @return array
	 */
	public function set_defaults() {

		$this->defaults = $this->get_defaults();

	}

	/**
	 * Unset the server.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.27 Unregister custom rest fields.
	 *
	 * @return void
	 */
	public function tear_down() {

		parent::tear_down();

		global $wp_rest_server;
		unset( $this->server );

		$wp_rest_server = null;

		$this->unregister_rest_additional_fields();

	}

	/**
	 * Get resource update args.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return array
	 */
	protected function get_update_args() {
		return array();
	}

}
