<?php
/**
 * Base REST Controller
 *
 * @package  LifterLMS_REST/Abstracts
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.14
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Controller class
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.3 Fix an issue displaying a last page for lists with 0 possible results & handle error conditions early in responses.
 * @since 1.0.0-beta.7 Break `get_items()` method into `prepare_collection_query_args()`, `prepare_args_for_total_count_query()`,
 *                  `prepare_collection_items_for_response()` and `add_header_pagination()` methods so to improve abstraction.
 *                  `prepare_objects_query()` renamed to `prepare_collection_query_args()`.
 * @since 1.0.0-beta.12 Added logic to perform a collection search.
 *                      Added `object_inserted()` and `object_completely_inserted()` methods called after an object is
 *                      respectively inserted in the DB and all its additional fields have been updated as well (completely inserted).
 * @since 1.0.0-beta.14 Update `prepare_links()` to accept a second parameter, `WP_REST_Request`.
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
	 * Whether search is allowed
	 *
	 * @var boolean
	 */
	protected $is_searchable = false;

	/**
	 * Create an item.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.12 Call `object_inserted` and `object_completely_inserted` after an object is
	 *                      respectively inserted in the DB and all its additional fields have been
	 *                      updated as well (completely inserted).
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
		$schema = $this->get_item_schema();

		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$this->object_inserted( $object, $request, $schema, true );

		$fields_update = $this->update_additional_fields_for_object( $item, $request );
		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$this->object_completely_inserted( $object, $request, $schema, true );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $object, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $this->get_object_id( $object ) ) ) );

		return $response;

	}

	/**
	 * Called right after a resource is inserted (created/updated).
	 *
	 * @since 1.0.0-beta.12
	 *
	 * @param object          $object   Inserted or updated object.
	 * @param WP_REST_Request $request  Request object.
	 * @param array           $schema   The item schema.
	 * @param bool            $creating True when creating a post, false when updating.
	 */
	protected function object_inserted( $object, $request, $schema, $creating ) {

		$type = $this->get_object_type();
		/**
		 * Fires after a single llms resource is created or updated via the REST API.
		 *
		 * The dynamic portion of the hook name, `$type`, refers to the object type this controller is responsible for managing.
		 *
		 * @since 1.0.0-beta.12
		 *
		 * @param object          $object   Inserted or updated object.
		 * @param WP_REST_Request $request  Request object.
		 * @param array           $schema   The item schema.
		 * @param bool            $creating True when creating a post, false when updating.
		 */
		do_action( "llms_rest_insert_{$type}", $object, $request, $schema, $creating );
	}

	/**
	 * Called right after a resource is completely inserted (created/updated).
	 *
	 * @since 1.0.0-beta.12
	 *
	 * @param LLMS_Post       $object   Inserted or updated object.
	 * @param WP_REST_Request $request  Request object.
	 * @param array           $schema   The item schema.
	 * @param bool            $creating True when creating a post, false when updating.
	 */
	protected function object_completely_inserted( $object, $request, $schema, $creating ) {

		$type = $this->get_object_type();
		/**
		 * Fires after a single llms resource is completely created or updated via the REST API.
		 *
		 * The dynamic portion of the hook name, `$type`, refers to the object type this controller is responsible for managing.
		 *
		 * @since 1.0.0-beta.12
		 *
		 * @param object          $object   Inserted or updated object.
		 * @param WP_REST_Request $request  Request object.
		 * @param array           $schema   The item schema.
		 * @param bool            $creating True when creating a post, false when updating.
		 */
		do_action( "llms_rest_after_insert_{$type}", $object, $request, $schema, $creating );
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
	 * @since 1.0.0-beta.12 Added `search_columns` collection param for searchable resources.
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['context']['default'] = 'view';

		// We're not currently implementing searching for all of our controllers.
		if ( empty( $this->is_searchable ) ) {
			unset( $query_params['search'] );
		} elseif ( ! empty( $this->search_columns_mapping ) ) {

			$search_columns = array_keys( $this->search_columns_mapping );

			$query_params['search_columns'] = array(
				'description' => __( 'Column names to be searched. Accepts a single column or a comma separated list of columns.', 'lifterlms' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'string',
					'enum' => $search_columns,
				),
				'default'     => $search_columns,
			);
		}

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
	 * Retrieves all items
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.3 Fix an issue displaying a last page for lists with 0 possible results.
	 * @since 1.0.0-beta.7 Broken into several methods so to improve abstraction.
	 * @since 1.0.0-beta.12 Return early if `prepare_collection_query_args()` is a `WP_Error`.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

		$prepared = $this->prepare_collection_query_args( $request );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$query      = $this->get_objects_query( $prepared, $request );
		$pagination = $this->get_pagination_data_from_query( $query, $prepared, $request );

		// Out-of-bounds, run the query again on page one to get a proper total count.
		if ( $pagination['total_results'] < 1 ) {

			$prepared_for_total_count = $this->prepare_args_for_total_count_query( $prepared, $request );
			$count_query              = $this->get_objects_query( $prepared_for_total_count, $request );
			$count_results            = $this->get_pagination_data_from_query( $count_query, $prepared_for_total_count, $request );

			$pagination['total_results'] = $count_results['total_results'];
		}

		if ( $pagination['current_page'] > $pagination['total_pages'] && $pagination['total_results'] > 0 ) {
			return llms_rest_bad_request_error( __( 'The page number requested is larger than the number of pages available.', 'lifterlms' ) );
		}

		$objects = $this->get_objects_from_query( $query );
		$items   = $this->prepare_collection_items_for_response( $objects, $request );

		$response = rest_ensure_response( $items );
		$response = $this->add_header_pagination( $response, $pagination, $request );

		return $response;

	}

	/**
	 * Format query arguments to retrieve a collection of objects.
	 *
	 * @since 1.0.0-beta.7
	 * @since 1.0.0-beta.12 Prepare args for search and call collection params to query args map method.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error
	 */
	protected function prepare_collection_query_args( $request ) {

		// Prepare all set args.
		$registered = $this->get_collection_params();
		$prepared   = array();

		foreach ( $registered as $key => $value ) {
			if ( isset( $request[ $key ] ) ) {
				$prepared[ $key ] = $request[ $key ];
			}
		}

		$prepared = $this->prepare_collection_query_search_args( $prepared, $request );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		$prepared = $this->map_params_to_query_args( $prepared, $registered, $request );

		return $prepared;

	}

	/**
	 * Map schema to query arguments to retrieve a collection of objects.
	 *
	 * @since 1.0.0-beta.12
	 *
	 * @param array           $prepared   Array of collection arguments.
	 * @param array           $registered Registered collection params.
	 * @param WP_REST_Request $request    Full details about the request.
	 * @return array|WP_Error
	 */
	protected function map_params_to_query_args( $prepared, $registered, $request ) {
		return $prepared;
	}

	/**
	 * Format search query arguments to retrieve a collection of objects.
	 *
	 * @since 1.0.0-beta.12
	 *
	 * @param array           $prepared Array of collection arguments.
	 * @param WP_REST_Request $request  Request object.
	 * @return array|WP_Error
	 */
	protected function prepare_collection_query_search_args( $prepared, $request ) {

		// Search?
		if ( ! empty( $prepared['search'] ) ) {

			if ( ! empty( $this->search_columns_mapping ) ) {

				if ( empty( $prepared['search_columns'] ) ) {
					return llms_rest_bad_request_error( __( 'You must provide a valid set of columns to search into.', 'lifterlms' ) );
				}

				// Filter search columns by context.
				$search_columns = array_keys( $this->filter_response_by_context( array_flip( $prepared['search_columns'] ), $request['context'] ) );

				// Check if one of more unallowed search columns have been provided as request query params (not merged with defaults).
				if ( ! empty( $request->get_query_params()['search_columns'] ) ) {

					$forbidden_columns = array_diff( $prepared['search_columns'], $search_columns );

					if ( ! empty( $forbidden_columns ) ) {
						return llms_rest_authorization_required_error(
							sprintf(
								// Translators: %1$s comma separated list of search columns.
								__( 'You are not allowed to search into the provided column(s): %1$s', 'lifterlms' ),
								implode( ',', $forbidden_columns )
							)
						);
					}
				}

				$prepared['search_columns'] = array();

				// Map our search columns into query compatible ones.
				foreach ( $search_columns as $search_column ) {
					if ( isset( $this->search_columns_mapping[ $search_column ] ) ) {
						$prepared['search_columns'][] = $this->search_columns_mapping[ $search_column ];
					}
				}

				if ( empty( $prepared['search_columns'] ) ) {
					return llms_rest_bad_request_error( __( 'You must provide a valid set of columns to search into.', 'lifterlms' ) );
				}
			}

			$prepared['search'] = '*' . $prepared['search'] . '*';
		}

		return $prepared;
	}

	/**
	 * Prepare query args for total count query.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @param  array           $args Array of query args.
	 * @param  WP_REST_Request $request  Full details about the request.
	 * @return array
	 */
	protected function prepare_args_for_total_count_query( $args, $request ) {
		// Run the query again without pagination to get a proper total count.
		unset( $args['paged'], $args['page'] );
		return $args;
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
			$object = $this->get_object( $object, false );

			if ( ! $this->check_read_object_permissions( $object ) ) {
				continue;
			}

			$item = $this->prepare_item_for_response( $object, $request );
			if ( ! is_wp_error( $item ) ) {
				$items[] = $this->prepare_response_for_collection( $item );
			}
		}

		return $items;
	}

	/**
	 * Add pagination info and links to the response header.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @param WP_REST_Response $response   Current response being served.
	 * @param array            $pagination Pagination array.
	 * @param WP_REST_Request  $request    Full details about the request.
	 * @return WP_REST_Response
	 */
	protected function add_header_pagination( $response, $pagination, $request ) {

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
	 * @since 1.0.0-beta.3 Return early with a WP_Error if `$object` is a WP_Error.
	 * @since 1.0.0-beta.14 Pass the `$request` parameter to `prepare_links()`.
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
		$response->add_links( $this->prepare_links( $object, $request ) );

		return $response;

	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.14 Added $request parameter.
	 *
	 * @param obj             $object  Item object.
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	protected function prepare_links( $object, $request ) {

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
	 * @since 1.0.0-beta.12 Call `object_inserted` and `object_completely_inserted` after an object is
	 *                      respectively inserted in the DB and all its additional fields have been
	 *                      updated as well (completely inserted).
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
		$schema = $this->get_item_schema();

		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$this->object_inserted( $object, $request, $schema, false );

		$fields_update = $this->update_additional_fields_for_object( $item, $request );
		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$this->object_completely_inserted( $object, $request, $schema, false );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $object, $request );
		$response = rest_ensure_response( $response );

		return $response;

	}

}
