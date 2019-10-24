<?php
/**
 * Base REST Controller Class.
 *
 * @package  LifterLMS_REST/Abstracts
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Controller class..
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.3 Fix an issue displaying a last page for lists with 0 possible results & handle error conditions early in responses.
 */
abstract class LLMS_REST_Controller extends LLMS_REST_Controller_Stubs {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'llms/v1';

	/**
	 * Schema properties available for ordering the collection.
	 *
	 * @var string[]
	 */
	protected $orderby_properties = array(
		'id',
	);

	/**
	 * Create an item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {

		if ( ! empty( $request['id'] ) ) {
			return llms_rest_bad_request_error( __( 'Cannot create an existing resource.', 'lifterlms' ) );
		}

		$item   = $this->prepare_item_for_database( $request );
		$object = $this->create_object( $item, $request );

		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$fields_update = $this->update_additional_fields_for_object( $item, $request );
		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $object, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $this->get_object_id( $object ) ) ) );

		return $response;

	}

	/**
	 * Delete the item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {

		$object = $this->get_object( $request['id'], false );

		// We don't return 404s for items that are not found.
		if ( ! is_wp_error( $object ) ) {

			// If there was an error deleting the object return the error. If the error is that the object doesn't exist return 204 below!
			$del = $this->delete_object( $object, $request );
			if ( is_wp_error( $del ) ) {
				return $del;
			}
		}

		$response = rest_ensure_response( null );
		$response->set_status( 204 );

		return $response;

	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		// We're not currently implementing searching.
		unset( $query_params['search'] );

		// page and per_page params are already specified in WP_Rest_Controller->get_collection_params().

		$query_params['order'] = array(
			'description'       => __( 'Order sort attribute ascending or descending.', 'lifterlms' ),
			'type'              => 'string',
			'default'           => 'asc',
			'enum'              => array( 'asc', 'desc' ),
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['orderby'] = array(
			'description'       => __( 'Sort collection by object attribute.', 'lifterlms' ),
			'type'              => 'string',
			'default'           => $this->orderby_properties[0],
			'enum'              => $this->orderby_properties,
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['include'] = array(
			'description'       => __( 'Limit results to a list of ids. Accepts a single id or a comma separated list of ids.', 'lifterlms' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['exclude'] = array(
			'description'       => __( 'Exclude a list of ids from results. Accepts a single id or a comma separated list of ids.', 'lifterlms' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $query_params;
	}

	/**
	 * Get a single item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$object = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$response = $this->prepare_item_for_response( $object, $request );

		return rest_ensure_response( $response );

	}

	/**
	 * Retrieves all users.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.3 Fix an issue displaying a last page for lists with 0 possible results.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

		// Prepare all set args.
		$params   = array_keys( $this->get_collection_params() );
		$prepared = array();

		foreach ( $params as $key ) {
			if ( isset( $request[ $key ] ) ) {
				$prepared[ $key ] = $request[ $key ];
			}
		}

		$query   = $this->get_objects_query( $prepared, $request );
		$objects = $this->get_objects_from_query( $query );
		$items   = array();

		foreach ( $objects as $object ) {

			$object = $this->get_object( $object );
			if ( ! $this->check_read_item_permissions( $this->get_object_id( $object ) ) ) {
				continue;
			}

			$item    = $this->prepare_item_for_response( $object, $request );
			$items[] = $this->prepare_response_for_collection( $item );
		}

		$response   = rest_ensure_response( $items );
		$pagination = $this->get_pagination_data_from_query( $query, $prepared, $request );

		// Out-of-bounds, run the query again on page one to get a proper total count.
		if ( $pagination['total_results'] < 1 ) {

			$prepared['page'] = 1;
			$count_query      = $this->get_objects_query( $prepared, $request );
			$count_results    = $this->get_pagination_data_from_query( $count_query, $prepared, $request );

			$pagination['total_results'] = $count_results['total_results'];
		}

		$response->header( 'X-WP-Total', $pagination['total_results'] );
		$response->header( 'X-WP-TotalPages', $pagination['total_pages'] );

		$base = add_query_arg( urlencode_deep( $request->get_query_params() ), rest_url( $request->get_route() ) );

		// First page link.
		if ( 1 !== $pagination['current_page'] ) {
			$first_link = add_query_arg( 'page', 1, $base );
			$response->link_header( 'first', $first_link );
		}

		// Previous page link.
		if ( $pagination['current_page'] > 1 ) {
			$prev_page = $pagination['current_page'] - 1;
			if ( $prev_page > $pagination['total_pages'] ) {
				$prev_page = $pagination['total_pages'];
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}

		// Next page link.
		if ( $pagination['total_pages'] > $pagination['current_page'] ) {
			$next_link = add_query_arg( 'page', $pagination['current_page'] + 1, $base );
			$response->link_header( 'next', $next_link );
		}

		// Last page link.
		if ( $pagination['total_pages'] && $pagination['total_pages'] !== $pagination['current_page'] ) {
			$last_link = add_query_arg( 'page', $pagination['total_pages'], $base );
			$response->link_header( 'last', $last_link );
		}

		return $response;
	}

	/**
	 * Retrieves the query params for retrieving a single resource.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_get_item_params() {

		return array(
			'context' => $this->get_context_param(
				array(
					'default' => 'view',
				)
			),
		);

	}

	/**
	 * Retrieve arguments for deleting a resource.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_delete_item_args() {
		return array();
	}

	/**
	 * Map request keys to database keys for insertion.
	 *
	 * Array keys are the request fields (as defined in the schema) and
	 * array values are the database fields.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	protected function map_schema_to_database() {

		$schema = $this->get_item_schema();
		$keys   = array_keys( $schema['properties'] );
		return array_combine( $keys, $keys );

	}

	/**
	 * Prepare request arguments for a database insert/update.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_Rest_Request $request Request object.
	 * @return array
	 */
	protected function prepare_item_for_database( $request ) {

		$prepared = array();
		$map      = $this->map_schema_to_database();
		$schema   = $this->get_item_schema();

		foreach ( $map as $req_key => $db_key ) {
			if ( ! empty( $request[ $req_key ] ) ) {
				$prepared[ $db_key ] = $request[ $req_key ];
			}
		}

		return $prepared;

	}

	/**
	 * Prepares a single object for response.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.3 Return early with a WP_Error if `$object` is a WP_Error
	 *
	 * @param obj             $object Raw object from database.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $object, $request ) {

		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$data = $this->prepare_object_for_response( $object, $request );

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		// Add links.
		$response->add_links( $this->prepare_links( $object ) );

		return $response;

	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param obj $object Item object.
	 * @return array
	 */
	protected function prepare_links( $object ) {

		$base = rest_url( sprintf( '/%1$s/%2$s', $this->namespace, $this->rest_base ) );

		$links = array(
			'self'       => array(
				'href' => sprintf( '%1$s/%2$d', $base, $this->get_object_id( $object ) ),
			),
			'collection' => array(
				'href' => $base,
			),
		);

		return $links;

	}

	/**
	 * Register routes.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the resource.', 'lifterlms' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_get_item_params(),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ), // see class-wp-rest-controller.php.
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => $this->get_delete_item_args(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

	}

	/**
	 * Update item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object or WP_Error on failure.
	 */
	public function update_item( $request ) {

		$object = $this->get_object( $request['id'] );
		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$item   = $this->prepare_item_for_database( $request );
		$object = $this->update_object( $item, $request );

		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$fields_update = $this->update_additional_fields_for_object( $item, $request );
		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $object, $request );
		$response = rest_ensure_response( $response );

		return $response;

	}

}
