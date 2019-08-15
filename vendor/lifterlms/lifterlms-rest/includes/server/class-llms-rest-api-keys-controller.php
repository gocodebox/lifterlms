<?php
/**
 * REST Controller for API Keys.
 *
 * @package  LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_API_Keys_Controller class.
 *
 * @since 1.0.0-beta.1
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
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_item_schema() {

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
	 * Create an API Key
	 *
	 * @since 1.0.0-beta.1
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
	 * Get API Key List
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$args  = $this->prepare_collection_query_args( $request );
		$query = new LLMS_REST_API_Keys_Query( $args );

		$page      = (int) $args['page'];
		$max_pages = (int) $query->max_pages;
		$total     = (int) $query->found_results;

		if ( $total < 1 ) {

			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $args['page'] );
			$count_query = new LLMS_REST_API_Keys_Query( $args );
			$total       = (int) $count_query->found_results;
			$max_pages   = (int) $query->max_pages;

		}

		if ( $page > $max_pages && $total > 0 ) {
			return llms_rest_bad_request_error( __( 'The page number requested is larger than the number of pages available.', 'lifterlms' ) );
		}

		$results = array();
		foreach ( $query->get_keys() as $key ) {
			$response_object = $this->prepare_item_for_response( $key, $request );
			if ( ! is_wp_error( $response_object ) ) {
				$results[] = $this->prepare_response_for_collection( $response_object );
			}
		}

		$response = rest_ensure_response( $results );

		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', $max_pages );

		$request_params = $request->get_query_params();
		$base           = add_query_arg(
			urlencode_deep( $request_params ),
			rest_url( $request->get_route() )
		);

		// Add first page.
		$first_link = add_query_arg( 'page', 1, $base );
		$response->link_header( 'first', $first_link );

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}
		// Add last page.
		$last_link = add_query_arg( 'page', $max_pages, $base );
		$response->link_header( 'last', $last_link );

		return $response;

	}

	/**
	 * Update an API Key
	 *
	 * @since 1.0.0-beta.1
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
	 * Prepare an API Key for a REST response.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_REST_API_Key $item API Key object.
	 * @param WP_REST_Request   $request Request object.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {

		$data = array(
			'id' => $item->get( 'id' ),
		);

		// Add all readable properties.
		foreach ( $this->get_fields_for_response( $request ) as $field ) {
			$data[ $field ] = $item->get( $field );
		}

		// Is a creation request, return consumer key & secret.
		if ( 'POST' === $request->get_method() && sprintf( '/%1$s/%2$s', $this->namespace, $this->rest_base ) === $request->get_route() ) {
			$data['consumer_key']    = $item->get( 'consumer_key_one_time' );
			$data['consumer_secret'] = $item->get( 'consumer_secret' );
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		// Add links.
		$links = $this->prepare_links( $item );
		$response->add_links( $links );

		return $response;

	}

	/**
	 * Prepare a `_links` object for an API Key.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_REST_API_Key $item API Key object.
	 * @return array
	 */
	protected function prepare_links( $item ) {

		$links         = parent::prepare_links( $item );
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
