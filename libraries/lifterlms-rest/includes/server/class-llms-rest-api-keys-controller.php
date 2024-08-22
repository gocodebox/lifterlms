<?php
/**
 * REST Controller for API Keys
 *
 * @package LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.27
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_API_Keys_Controller class.
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.7 Added: `get_objects_from_query()`, `get_objects_query()`, `get_pagination_data_from_query()`, `prepare_collection_items_for_response()` methods overrides.
 *                    `get_items()` method abstracted and moved in LLMS_REST_Controller.
 * @since 1.0.0-beta.14 Update `prepare_links()` to accept a second parameter, `WP_REST_Request`.
 */
class LLMS_REST_API_Keys_Controller extends LLMS_REST_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'api-keys';

	/**
	 * Schema properties available for ordering the collection.
	 *
	 * @var string[]
	 */
	protected $orderby_properties = array(
		'id',
		'description',
		'last_access',
	);

	/**
	 * Check if the authenticated user can perform the request action.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return boolean
	 */
	protected function check_permissions() {
		return current_user_can( 'manage_lifterlms_api_keys' ) ? true : llms_rest_authorization_required_error();
	}

	/**
	 * Check if a given request has access to create an item.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Check if a given request has access to delete an item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$params = parent::get_collection_params();

		$params['permissions'] = array(
			'description' => __( 'Include only API keys matching a specific permission.', 'lifterlms' ),
			'type'        => 'string',
			'enum'        => array_keys( LLMS_REST_API()->keys()->get_permissions() ),
		);

		$params['user'] = array(
			'description' => __( 'Include only keys for the specified user(s). Accepts a single id or a comma separated list of ids.', 'lifterlms' ),
			'type'        => 'string',
		);

		$params['user_not_in'] = array(
			'description' => __( 'Exclude keys for the specified user(s). Accepts a single id or a comma separated list of ids.', 'lifterlms' ),
			'type'        => 'string',
		);

		return $params;

	}

	/**
	 * Get the API Key's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return array
	 */
	protected function get_item_schema_base() {

		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'api_key',
			'type'       => 'object',
			'properties' => array(
				'description'   => array(
					'description' => __( 'Friendly, human-readable name or description.', 'lifterlms' ),
					'type'        => 'string',
					'required'    => true,
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'permissions'   => array(
					'description' => __( 'Determines the capabilities and permissions of the key.', 'lifterlms' ),
					'type'        => 'string',
					'required'    => true,
					'context'     => array( 'view', 'edit' ),
					'enum'        => array_keys( LLMS_REST_API()->keys()->get_permissions() ),
				),
				'user_id'       => array(
					'description' => __( 'The WordPress User ID of the key owner.', 'lifterlms' ),
					'type'        => 'integer',
					'required'    => true,
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'absint',
						'validate_callback' => array( $this, 'validate_user_exists' ),
					),
				),
				'truncated_key' => array(
					'description' => __( 'The last 7 characters of the Consumer Key.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'last_access'   => array(
					'description' => __( 'The date the key was last used. Format: Y-m-d H:i:s.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

	}

	/**
	 * Check if a given request has access to read an item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Retrieve An API Key object by ID.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int  $id API Key ID.
	 * @param bool $hydrate If true, pulls all key data from the database on instantiation.
	 * @return WP_Error|LLMS_REST_API_Key
	 */
	protected function get_object( $id, $hydrate = true ) {

		$key = LLMS_REST_API()->keys()->get( $id, $hydrate );
		return $key ? $key : llms_rest_not_found_error();

	}

	/**
	 * Create an API Key.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.27 Handle custom rest fields registered via `register_rest_field()`.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		$prepared = $this->prepare_item_for_database( $request );
		$key      = LLMS_REST_API()->keys()->create( $prepared );
		if ( is_wp_error( $request ) ) {
			$request->add_data( array( 'status' => 400 ) );
			return $request;
		}

		// Fields registered via `register_rest_field()`.
		$fields_update = $this->update_additional_fields_for_object( $key, $request );
		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $key, $request );
		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $key->get( 'id' ) ) ) );

		return $response;

	}

	/**
	 * Delete API Key
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {

		$key = $this->get_object( $request['id'], false );
		if ( ! is_wp_error( $key ) ) {
			$key->delete();
		}

		$response = rest_ensure_response( null );
		$response->set_status( 204 );

		return $response;

	}

	/**
	 * Retrieve a query object based on arguments from a `get_items()` (collection) request.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @param  array           $prepared Array of collection arguments.
	 * @param  WP_REST_Request $request  Full details about the request.
	 * @return LLMS_REST_API_Keys_Query
	 */
	protected function get_objects_query( $prepared, $request ) {

		return new LLMS_REST_API_Keys_Query( $prepared );

	}

	/**
	 * Retrieve an array of objects from the result of $this->get_objects_query().
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @param WP_Query $query Query result.
	 * @return obj[]
	 */
	protected function get_objects_from_query( $query ) {

		return $query->get_keys();

	}

	/**
	 * Retrieve pagination information from an objects query.
	 *
	 * @since 1.0.0-beta.7
	 * @since 1.0.0-beta-24 Fixed access of protected LLMS_Abstract_Query properties.
	 *
	 * @param LLMS_REST_API_Keys_Query $query    Objects query result returned by {@see LLMS_REST_API_Keys_Controller::get_objects_query()}.
	 * @param array                    $prepared Array of collection arguments.
	 * @param WP_REST_Request          $request  Request object.
	 * @return array {
	 *     Array of pagination information.
	 *
	 *     @type int $current_page  Current page number.
	 *     @type int $total_results Total number of results.
	 *     @type int $total_pages   Total number of results pages.
	 * }
	 */
	protected function get_pagination_data_from_query( $query, $prepared, $request ) {

		$total_results = (int) $query->get_found_results();
		$current_page  = isset( $prepared['page'] ) ? (int) $prepared['page'] : 1;
		$total_pages   = (int) $query->get_max_pages();

		return compact( 'current_page', 'total_results', 'total_pages' );

	}

	/**
	 * Prepare collection items for response.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @param array           $objects Array of objects to be prepared for response.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_collection_items_for_response( $objects, $request ) {

		$items = array();

		foreach ( $objects as $object ) {
			$item = $this->prepare_item_for_response( $object, $request );
			if ( ! is_wp_error( $item ) ) {
				$items[] = $this->prepare_response_for_collection( $item );
			}
		}

		return $items;
	}

	/**
	 * Update an API Key
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.27 Handle custom rest fields registered via `register_rest_field()`.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {

		$prepared = $this->prepare_item_for_database( $request );
		$key      = LLMS_REST_API()->keys()->update( $prepared );
		if ( is_wp_error( $request ) ) {
			$request->add_data( array( 'status' => 400 ) );
			return $request;
		}

		// Fields registered via `register_rest_field()`.
		$fields_update = $this->update_additional_fields_for_object( $key, $request );
		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $key, $request );

		return $response;

	}

	/**
	 * Format query arguments from a collection GET request to be passed to a LLMS_REST_API_Keys_Query
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	protected function prepare_collection_query_args( $request ) {

		$args   = array();
		$params = $this->get_collection_params();

		foreach ( array_keys( $params ) as $param ) {

			if ( ! isset( $request[ $param ] ) || in_array( $param, array( 'order', 'orderby' ), true ) ) {
				continue;
			}

			$args[ $param ] = $request[ $param ];

			if ( in_array( $param, array( 'include', 'exclude', 'user', 'user_not_in' ), true ) ) {
				$args[ $param ] = array_map( 'absint', explode( ',', $args[ $param ] ) );
			}
		}

		if ( isset( $request['orderby'] ) || isset( $request['order'] ) ) {
			$orderby      = isset( $request['orderby'] ) ? $request['orderby'] : $params['orderby']['default'];
			$order        = isset( $request['order'] ) ? $request['order'] : $params['order']['default'];
			$args['sort'] = array( $orderby => $order );
		}

		return $args;

	}

	/**
	 * Prepare API Key for insert/update
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|array
	 */
	protected function prepare_item_for_database( $request ) {

		$prepared = array();

		if ( isset( $request['id'] ) ) {
			$existing = $this->get_object( $request['id'] );
			if ( is_wp_error( $existing ) ) {
				return $existing;
			}
			$prepared['id'] = $existing->get( 'id' );
		}

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['description'] ) && isset( $request['description'] ) ) {
			$prepared['description'] = $request['description'];
		}

		if ( ! empty( $schema['properties']['user_id'] ) && isset( $request['user_id'] ) ) {
			$prepared['user_id'] = (int) $request['user_id'];
		}

		if ( ! empty( $schema['properties']['permissions'] ) && isset( $request['permissions'] ) ) {
			$prepared['permissions'] = $request['permissions'];
		}

		return $prepared;

	}
	/**
	 * Prepare an object for response.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @param LLMS_REST_API_Key $object  API Key object.
	 * @param WP_REST_Request   $request Request object.
	 * @return array
	 */
	protected function prepare_object_for_response( $object, $request ) {

		$data       = parent::prepare_object_for_response( $object, $request );
		$data['id'] = $object->get( 'id' );

		// Is a creation request, return consumer key & secret.
		if ( 'POST' === $request->get_method() && sprintf( '/%1$s/%2$s', $this->namespace, $this->rest_base ) === $request->get_route() ) {
			$data['consumer_key']    = $object->get( 'consumer_key_one_time' );
			$data['consumer_secret'] = $object->get( 'consumer_secret' );
		}

		return $data;

	}

	/**
	 * Prepare a `_links` object for an API Key.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.14 Added $request parameter.
	 *
	 * @param LLMS_REST_API_Key $item    API Key object.
	 * @param WP_REST_Request   $request Request object.
	 * @return array
	 */
	protected function prepare_links( $item, $request ) {

		$links         = parent::prepare_links( $item, $request );
		$links['user'] = array(
			'href' => rest_url( sprintf( 'wp/v2/users/%d', $item->get( 'user_id' ) ) ),
		);

		return $links;

	}

	/**
	 * Check if a given request has access to update an item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {
		return $this->check_permissions();
	}

	/**
	 * Validate submitted user IDs are real user ids.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $value User-submitted value.
	 * @return boolean
	 */
	public function validate_user_exists( $value ) {

		$user = get_user_by( 'id', $value );
		return $user ? true : false;

	}

}
