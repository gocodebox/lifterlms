<?php
/**
 * REST LLMS Posts Controller Class
 *
 * @package LifterLMS_REST/Abstracts
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Posts_Controller
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.2 Filter taxonomies by `public` property instead of `show_in_rest`.
 * @since 1.0.0-beta.3 Filter taxonomies by `show_in_llms_rest` property instead of `public`.
 * @since 1.0.0-beta.7 Added: `check_read_object_permissions()`, `get_objects_from_query()`, `get_objects_query()`, `get_pagination_data_from_query()`, `prepare_collection_items_for_response()` methods overrides.
 *                     `get_items()` method removed, now abstracted in LLMS_REST_Controller.
 *                     `prepare_objects_query()` renamed to `prepare_collection_query_args()`.
 *                     On `update_item`, don't execute `$object->set_bulk()` when there's no data to update.
 *                     Fix wp:featured_media link, we don't expose any embeddable field.
 *                     Also `self` and `collection` links prepared in the parent class.
 *                     Added `"llms_rest_insert_{$this->post_type}"` and `"llms_rest_insert_{$this->post_type}"` action hooks:
 *                     fired after inserting/updating an llms post into the database.
 * @since 1.0.0-beta.8 Return links to those taxonomies which have an accessible rest route.
 *                     Initialize `$prepared_item` array before adding values to it.
 * @since 1.0.0-beta.9 Implemented a generic way to create and get an llms post object instance given a `post_type`.
 *                     In `get_objects_from_query()` avoid performing an additional query, just return the already retrieved posts.
 *                     Removed `"llms_rest_{$this->post_type}_filters_removed_for_response"` filter hooks,
 *                     `"llms_rest_{$this->post_type}_filters_removed_for_response"` added.
 * @since 1.0.0-beta.11 Fixed `"llms_rest_insert_{$this->post_type}"` and `"llms_rest_insert_{$this->post_type}"` action hooks fourth param:
 *                     must be false when updating.
 * @since 1.0.0-beta.12 Moved parameters to query args mapping from `$this->prepare_collection_params()` to `$this->map_params_to_query_args()`.
 * @since 1.0.0-beta.14 Update `prepare_links()` to accept a second parameter, `WP_REST_Request`.
 * @since 1.0.0-beta.21 Enable search.
 */
abstract class LLMS_REST_Posts_Controller extends LLMS_REST_Controller {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $collection_route_base_for_pagination;

	/**
	 * Schema properties available for ordering the collection.
	 *
	 * @var string[]
	 */
	protected $orderby_properties = array(
		'id',
		'title',
		'date_created',
		'date_updated',
		'menu_order',
		'relevance',
	);

	/**
	 * Whether search is allowed
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;

	/**
	 * LLMS post class name.
	 *
	 * @since 1.0.0-beta.9
	 *
	 * @var string
	 */
	protected $llms_post_class;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return void
	 */
	public function __construct() {
		$this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
	}

	/**
	 * Retrieves an array of arguments for the delete endpoint.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array Delete endpoint arguments.
	 */
	public function get_delete_item_args() {

		return array(
			'force' => array(
				'description' => __( 'Bypass the trash and force course deletion.', 'lifterlms' ),
				'type'        => 'boolean',
				'default'     => false,
			),
		);

	}

	/**
	 * Retrieves the query params for retrieving a single resource.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_get_item_params() {

		$params = parent::get_get_item_params();
		$schema = $this->get_item_schema();

		if ( isset( $schema['properties']['password'] ) ) {
			$params['password'] = array(
				'description' => __( 'Post password. Required if the post is password protected.', 'lifterlms' ),
				'type'        => 'string',
			);
		}

		return $params;

	}

	/**
	 * Determine if the current user can view the object.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @param object $object Object.
	 * @return bool
	 */
	protected function check_read_object_permissions( $object ) {
		return $this->check_read_permission( $object );
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

		// Everybody can list llms posts (in read mode).
		if ( 'edit' === $request['context'] && ! $this->check_update_permission() ) {
			return llms_rest_authorization_required_error();
		}

		return true;

	}

	/**
	 * Retrieve pagination information from an objects query.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @param WP_Query        $query    Objects query result returned by {@see LLMS_REST_Posts_Controller::get_objects_query()}.
	 * @param array           $prepared Array of collection arguments.
	 * @param WP_REST_Request $request  Request object.
	 * @return array {
	 *     Array of pagination information.
	 *
	 *     @type int $current_page  Current page number.
	 *     @type int $total_results Total number of results.
	 *     @type int $total_pages   Total number of results pages.
	 * }
	 */
	protected function get_pagination_data_from_query( $query, $prepared, $request ) {

		$total_results = (int) $query->found_posts;
		$current_page  = isset( $prepared['paged'] ) ? (int) $prepared['paged'] : 1;
		$total_pages   = (int) ceil( $total_results / (int) $query->get( 'posts_per_page' ) );

		return compact( 'current_page', 'total_results', 'total_pages' );

	}

	/**
	 * Check if a given request has access to create an item.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.18 Use plural post type name.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {

		$post_type_object = get_post_type_object( $this->post_type );
		$post_type_name   = $post_type_object->labels->name;

		if ( ! empty( $request['id'] ) ) {
			// Translators: %s = The post type name.
			return llms_rest_bad_request_error( sprintf( __( 'Cannot create existing %s.', 'lifterlms' ), $post_type_name ) );
		}

		if ( ! $this->check_create_permission() ) {
			// Translators: %s = The post type name.
			return llms_rest_authorization_required_error( sprintf( __( 'Sorry, you are not allowed to create %s as this user.', 'lifterlms' ), $post_type_name ) );
		}

		if ( ! $this->check_assign_terms_permission( $request ) ) {
			return llms_rest_authorization_required_error( __( 'Sorry, you are not allowed to assign the provided terms.', 'lifterlms' ) );
		}

		return true;
	}


	/**
	 * Creates a single LLMS post.
	 *
	 * Extending classes can add additional object fields by overriding the method `update_additional_object_fields()`.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.7 Added `"llms_rest_insert_{$this->post_type}"` and `"llms_rest_insert_{$this->post_type}"` action hooks:
	 *                     fired after inserting/updating an llms post into the database.
	 * @since 1.0.0-beta.25 Allow updating meta with the same value as the stored one.
	 * @since 1.0.0-beta.27 Handle custom meta registered via `register_meta()` and custom rest fields registered via `register_rest_field()`.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {

		$schema = $this->get_item_schema();

		$prepared_item = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $prepared_item ) ) {
			return $prepared_item;
		}

		$prepared_item = array_diff_key( $prepared_item, $this->get_additional_fields() );
		$object        = $this->create_llms_post( $prepared_item );
		if ( is_wp_error( $object ) ) {

			if ( 'db_insert_error' === $object->get_error_code() ) {
				$object->add_data( array( 'status' => 500 ) );
			} else {
				$object->add_data( array( 'status' => 400 ) );
			}

			return $object;
		}

		/**
		 * Fires after a single llms post is created or updated via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 1.0.0-beta.7
		 *
		 * @param LLMS_Post       $object   Inserted or updated llms object.
		 * @param WP_REST_Request $request  Request object.
		 * @param array           $schema   The item schema.
		 * @param bool            $creating True when creating a post, false when updating.
		 */
		do_action( "llms_rest_insert_{$this->post_type}", $object, $request, $schema, true );

		// Set all the other properties.
		// TODO: maybe we want to filter the post properties that have already been inserted before.
		$set_bulk_result = $object->set_bulk( $prepared_item, true, true );
		if ( is_wp_error( $set_bulk_result ) ) {

			if ( 'db_update_error' === $set_bulk_result->get_error_code() ) {
				$set_bulk_result->add_data( array( 'status' => 500 ) );
			} else {
				$set_bulk_result->add_data( array( 'status' => 400 ) );
			}

			return $set_bulk_result;
		}

		$object_id = $object->get( 'id' );

		$additional_fields = $this->update_additional_object_fields( $object, $request, $schema, $prepared_item );
		if ( is_wp_error( $additional_fields ) ) {
			return $additional_fields;
		}

		if ( ! empty( $schema['properties']['featured_media'] ) && isset( $request['featured_media'] ) ) {
			$this->handle_featured_media( $request['featured_media'], $object_id );
		}

		$terms_update = $this->handle_terms( $object_id, $request );
		if ( is_wp_error( $terms_update ) ) {
			return $terms_update;
		}

		$meta_update = $this->update_meta( $object, $request, $schema );
		if ( is_wp_error( $meta_update ) ) {
			return $meta_update;
		}

		// Fields registered via `register_rest_field()`.
		$fields_update = $this->update_additional_fields_for_object( $object, $request );
		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		/**
		 * Fires after a single llms post is completely created or updated via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 1.0.0-beta.7
		 *
		 * @param LLMS_Post       $object   Inserted or updated llms object.
		 * @param WP_REST_Request $request  Request object.
		 * @param array           $schema   The item schema.
		 * @param bool            $creating True when creating a post, false when updating.
		 */
		do_action( "llms_rest_after_insert_{$this->post_type}", $object, $request, $schema, true );

		$response = $this->prepare_item_for_response( $object, $request );

		$response->set_status( 201 );

		$response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $object_id ) ) );

		return $response;
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

		$object = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $object ) ) {
			return $object;
		}

		if ( 'edit' === $request['context'] && ! $this->check_update_permission( $object ) ) {
			return llms_rest_authorization_required_error();
		}

		if ( ! empty( $request['password'] ) ) {
			// Check post password, and return error if invalid.
			if ( ! hash_equals( $object->get( 'password' ), $request['password'] ) ) {
				return llms_rest_authorization_required_error( __( 'Incorrect password.', 'lifterlms' ) );
			}
		}

		// Allow access to all password protected posts if the context is edit.
		if ( 'edit' === $request['context'] ) {
			add_filter( 'post_password_required', '__return_false' );
		}

		if ( ! $this->check_read_permission( $object ) ) {
			return llms_rest_authorization_required_error();
		}

		return true;
	}

	/**
	 * Retrieves the query params for the objects collection
	 *
	 * @since 1.0.0-beta.19
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();
		$schema       = $this->get_item_schema();

		if ( isset( $schema['properties']['status'] ) ) {
			$query_params['status'] = array(
				'default'           => 'publish',
				'description'       => __( 'Limit result set to posts assigned one or more statuses.', 'lifterlms' ),
				'type'              => 'array',
				'items'             => array(
					'enum' => array_merge(
						array_keys(
							get_post_stati()
						),
						array(
							'any',
						)
					),
					'type' => 'string',
				),
				'sanitize_callback' => array( $this, 'sanitize_post_statuses' ),
			);
		}

		return $query_params;

	}

	/**
	 * Format query arguments to retrieve a collection of objects.
	 *
	 * @since 1.0.0-beta.7
	 * @since 1.0.0-beta.12 Moved parameters to query args mapping into a different method.
	 * @since 1.0.0-beta.18 Correctly return errors.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error
	 */
	protected function prepare_collection_query_args( $request ) {

		$prepared = parent::prepare_collection_query_args( $request );
		if ( is_wp_error( $prepared ) ) {
			return $prepared;
		}

		// Force the post_type argument, since it's not a user input variable.
		$prepared['post_type'] = $this->post_type;

		$query_args = $this->prepare_items_query( $prepared, $request );

		return $query_args;

	}

	/**
	 * Map schema to query arguments to retrieve a collection of objects.
	 *
	 * @since 1.0.0-beta.12
	 * @since 1.0.0-beta.19 Map 'status' collection param to to 'post_status' query arg.
	 *
	 * @param array           $prepared   Array of collection arguments.
	 * @param array           $registered Registered collection params.
	 * @param WP_REST_Request $request    Full details about the request.
	 * @return array|WP_Error
	 */
	protected function map_params_to_query_args( $prepared, $registered, $request ) {

		$args = array();

		/*
		* This array defines mappings between public API query parameters whose
		* values are accepted as-passed, and their internal WP_Query parameter
		* name equivalents (some are the same). Only values which are also
		* present in $registered will be set.
		*/
		$parameter_mappings = array(
			'order'   => 'order',
			'orderby' => 'orderby',
			'page'    => 'paged',
			'exclude' => 'post__not_in',
			'include' => 'post__in',
			'search'  => 's',
			'status'  => 'post_status',
		);

		/*
		* For each known parameter which is both registered and present in the request,
		* set the parameter's value on the query $args.
		*/
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$args[ $wp_param ] = $request[ $api_param ];
			}
		}

		// Ensure our per_page parameter overrides any provided posts_per_page filter.
		if ( isset( $registered['per_page'] ) ) {
			$args['posts_per_page'] = $request['per_page'];
		}

		return $args;
	}

	/**
	 * Check if a given request has access to update an item.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.18 Use plural post type name.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {

		$object = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$post_type_object = get_post_type_object( $this->post_type );
		$post_type_name   = $post_type_object->labels->name;

		if ( ! $this->check_update_permission( $object ) ) {
			// Translators: %s = The post type name.
			return llms_rest_authorization_required_error( sprintf( __( 'Sorry, you are not allowed to update %s as this user.', 'lifterlms' ), $post_type_name ) );
		}

		if ( ! $this->check_assign_terms_permission( $request ) ) {
			return llms_rest_authorization_required_error( __( 'Sorry, you are not allowed to assign the provided terms.', 'lifterlms' ) );
		}

		return true;
	}

	/**
	 * Updates a single llms post.
	 *
	 * Extending classes can add additional object fields by overriding the method `update_additional_object_fields()`.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.7 Don't execute `$object->set_bulk()` when there's no data to update:
	 *                     this fixes an issue when updating only properties which are not handled in `prepare_item_for_database()`.
	 *                     Added `"llms_rest_insert_{$this->post_type}"` and `"llms_rest_insert_{$this->post_type}"` action hooks:
	 *                     fired after inserting/updating an llms post into the database.
	 * @since 1.0.0-beta.11 Fixed `"llms_rest_insert_{$this->post_type}"` and `"llms_rest_insert_{$this->post_type}"` action hooks fourth param:
	 *                      must be false when updating.
	 * @since 1.0.0-beta.25 Allow updating meta with the same value as the stored one.
	 * @since 1.0.0-beta.27 Handle custom meta registered via `register_meta()` and custom rest fields registered via `register_rest_field()`.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {

		$object = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$schema        = $this->get_item_schema();
		$prepared_item = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $prepared_item ) ) {
			return $prepared_item;
		}
		$prepared_item = array_diff_key( $prepared_item, $this->get_additional_fields() );
		$update_result = empty( array_diff_key( $prepared_item, array_flip( array( 'id' ) ) ) ) ? false : $object->set_bulk( $prepared_item, true, true );
		if ( is_wp_error( $update_result ) ) {

			if ( 'db_update_error' === $update_result->get_error_code() ) {
				$update_result->add_data( array( 'status' => 500 ) );
			} else {
				$update_result->add_data( array( 'status' => 400 ) );
			}

			return $update_result;
		}

		/**
		 * Fires after a single llms post is created or updated via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 1.0.0-beta.7
		 *
		 * @param LLMS_Post       $object   Inserted or updated llms object.
		 * @param WP_REST_Request $request  Request object.
		 * @param array           $schema   The item schema.
		 * @param bool            $creating True when creating a post, false when updating.
		 */
		do_action( "llms_rest_insert_{$this->post_type}", $object, $request, $schema, false );

		$additional_fields = $this->update_additional_object_fields( $object, $request, $schema, $prepared_item, false );
		if ( is_wp_error( $additional_fields ) ) {
			return $additional_fields;
		}

		$object_id = $object->get( 'id' );

		if ( ! empty( $schema['properties']['featured_media'] ) && isset( $request['featured_media'] ) ) {
			$this->handle_featured_media( $request['featured_media'], $object_id );
		}

		$terms_update = $this->handle_terms( $object_id, $request );
		if ( is_wp_error( $terms_update ) ) {
			return $terms_update;
		}

		$meta_update = $this->update_meta( $object, $request, $schema );
		if ( is_wp_error( $meta_update ) ) {
			return $meta_update;
		}

		// Fields registered via `register_rest_field()`.
		$fields_update = $this->update_additional_fields_for_object( $object, $request );
		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		/**
		 * Fires after a single llms post is completely created or updated via the REST API.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 1.0.0-beta.7
		 *
		 * @param LLMS_Post       $object   Inserted or updated llms object.
		 * @param WP_REST_Request $request  Request object.
		 * @param array           $schema   The item schema.
		 * @param bool            $creating True when creating a post, false when updating.
		 */
		do_action( "llms_rest_after_insert_{$this->post_type}", $object, $request, $schema, false );

		return $this->prepare_item_for_response( $object, $request );

	}

	/**
	 * Updates a single llms post.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.7 return description updated.
	 *
	 * @param LLMS_Post_Model $object        LMMS_Post_Model instance.
	 * @param array           $prepared_item Array.
	 * @param WP_REST_Request $request       Full details about the request.
	 * @param array           $schema        The item schema.
	 * @return bool|WP_Error True on success or false if nothing to update, WP_Error object if something went wrong during the update.
	 */
	protected function update_additional_object_fields( $object, $prepared_item, $request, $schema ) {
		return true;
	}

	/**
	 * Check if a given request has access to delete an item.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.18 Provide a more significant error message when trying to delete an item without permissions.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {

		$object = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $object ) ) {
			// LLMS_Post not found, we don't return a 404.
			if ( in_array( 'llms_rest_not_found', $object->get_error_codes(), true ) ) {
				return true;
			}

			return $object;
		}

		if ( ! $this->check_delete_permission( $object ) ) {
			return llms_rest_authorization_required_error(
				sprintf(
					// Translators: %s = The post type name.
					__( 'Sorry, you are not allowed to delete %s as this user.', 'lifterlms' ),
					get_post_type_object( $this->post_type )->labels->name
				)
			);
		}

		return true;

	}

	/**
	 * Deletes a single llms post.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {

		$object   = $this->get_object( (int) $request['id'] );
		$response = new WP_REST_Response();
		$response->set_status( 204 );

		if ( is_wp_error( $object ) ) {
			// Course not found, we don't return a 404.
			if ( in_array( 'llms_rest_not_found', $object->get_error_codes(), true ) ) {
				return $response;
			}

			return $object;
		}

		$post_type_object = get_post_type_object( $this->post_type );
		$post_type_name   = $post_type_object->labels->singular_name;

		$id    = $object->get( 'id' );
		$force = $this->is_delete_forced( $request );

		// If we're forcing, then delete permanently.
		if ( $force ) {
			$result = wp_delete_post( $id, true );
		} else {

			$supports_trash = $this->is_trash_supported();

			// If we don't support trashing for this type, error out.
			if ( ! $supports_trash ) {
				return new WP_Error(
					'llms_rest_trash_not_supported',
					/* translators: %1$s: post type name, %2$s: force=true */
					sprintf( __( 'The %1$s does not support trashing. Set \'%2$s\' to delete.', 'lifterlms' ), $post_type_name, 'force=true' ),
					array( 'status' => 501 )
				);
			}

			// Otherwise, only trash if we haven't already.
			if ( 'trash' !== $object->get( 'status' ) ) {
				// (Note that internally this falls through to `wp_delete_post` if
				// the trash is disabled.)
				$result = wp_trash_post( $id );
			} else {
				$result = true;
			}

			$request->set_param( 'context', 'edit' );
			$object   = $this->get_object( $id );
			$response = $this->prepare_item_for_response( $object, $request );

		}

		if ( ! $result ) {
			return new WP_Error(
				'llms_rest_cannot_delete',
				/* translators: %s: post type name */
				sprintf( __( 'The %s cannot be deleted.', 'lifterlms' ), $post_type_name ),
				array( 'status' => 500 )
			);
		}

		return $response;

	}

	/**
	 * Whether the delete should be forced.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the delete should be forced, false otherwise.
	 */
	protected function is_delete_forced( $request ) {
		return isset( $request['force'] ) && (bool) $request['force'];
	}

	/**
	 * Whether the trash is supported.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return bool True if the trash is supported, false otherwise.
	 */
	protected function is_trash_supported() {
		return ( EMPTY_TRASH_DAYS > 0 );
	}


	/**
	 * Retrieve a query object based on arguments from a `get_items()` (collection) request.
	 *
	 * @since 1.0.0-beta.7
	 *
	 * @param  array           $prepared Array of collection arguments.
	 * @param  WP_REST_Request $request  Full details about the request.
	 * @return WP_Query
	 */
	protected function get_objects_query( $prepared, $request ) {

		return new WP_Query( $prepared );

	}

	/**
	 * Retrieve an array of objects from the result of `$this->get_objects_query()`.
	 *
	 * @since 1.0.0-beta.7
	 * @since 1.0.0-beta.9 Avoid performing an additional query, just return the already retrieved posts.
	 *
	 * @param WP_Query $query WP_Query query result.
	 * @return WP_Post[]
	 */
	protected function get_objects_from_query( $query ) {

		return $query->posts;

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

		// Allow access to all password protected posts if the context is edit.
		if ( 'edit' === $request['context'] ) {
			add_filter( 'post_password_required', '__return_false' );
		}

		$items = parent::prepare_collection_items_for_response( $objects, $request );

		// Reset filter.
		if ( 'edit' === $request['context'] ) {
			remove_filter( 'post_password_required', '__return_false' );
		}

		return $items;

	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Post_Model $object  object object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $object, $request ) {

		$object_id         = $object->get( 'id' );
		$password_required = post_password_required( $object_id );
		$password          = $object->get( 'password' );

		$data = array(
			'id'               => $object->get( 'id' ),
			'date_created'     => $object->get_date( 'date', 'Y-m-d H:i:s' ),
			'date_created_gmt' => $object->get_date( 'date_gmt', 'Y-m-d H:i:s' ),
			'date_updated'     => $object->get_date( 'modified', 'Y-m-d H:i:s' ),
			'date_updated_gmt' => $object->get_date( 'modified_gmt', 'Y-m-d H:i:s' ),
			'menu_order'       => $object->get( 'menu_order' ),
			'title'            => array(
				'raw'      => $object->get( 'title', true ),
				'rendered' => $object->get( 'title' ),
			),
			'password'         => $password,
			'slug'             => $object->get( 'name' ),
			'post_type'        => $this->post_type,
			'permalink'        => get_permalink( $object_id ),
			'status'           => $object->get( 'status' ),
			'featured_media'   => (int) get_post_thumbnail_id( $object_id ),
			'comment_status'   => $object->get( 'comment_status' ),
			'ping_status'      => $object->get( 'ping_status' ),
			'content'          => array(
				'raw'       => $object->get( 'content', true ),
				'rendered'  => $password_required ? '' : apply_filters( 'the_content', $object->get( 'content', true ) ),
				'protected' => (bool) $password,
			),
			'excerpt'          => array(
				'raw'       => $object->get( 'excerpt', true ),
				'rendered'  => $password_required ? '' : apply_filters( 'the_excerpt', $object->get( 'excerpt' ) ),
				'protected' => (bool) $password,
			),
		);

		return $data;

	}

	/**
	 * Prepares data of a single object for response.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @param obj             $object  Raw object from database.
	 * @param WP_REST_Request $request Request object.
	 * @return array
	 */
	protected function prepare_object_data_for_response( $object, $request ) {

		// Need to set the global $post because of references to the global $post when e.g. filtering the content, or processing blocks/shortcodes.
		global $post;
		$temp = $post;
		$post = $object->get( 'post' ); // phpcs:ignore
		setup_postdata( $post );

		$removed_filters_for_response = $this->maybe_remove_filters_for_response( $object );

		$has_password_filter = false;

		if ( $this->can_access_password_content( $object, $request ) ) {
			// Allow access to the post, permissions already checked before.
			add_filter( 'post_password_required', '__return_false' );
			$has_password_filter = true;
		}

		$data = parent::prepare_object_data_for_response( $object, $request );

		// Filter data including only schema props.
		$data = array_intersect_key( $data, array_flip( $this->get_fields_for_response( $request ) ) );

		if ( $has_password_filter ) {
			// Reset filter.
			remove_filter( 'post_password_required', '__return_false' );
		}

		$this->maybe_add_removed_filters_for_response( $removed_filters_for_response );
		$post = $temp; // phpcs:ignore
		wp_reset_postdata();

		return $data;

	}

	/**
	 * Determines the allowed query_vars for a get_items() response and prepares
	 * them for WP_Query.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array           $prepared_args Optional. Prepared WP_Query arguments. Default empty array.
	 * @param WP_REST_Request $request       Optional. Full details about the request.
	 * @return array Items query arguments.
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {

		$query_args = array();

		foreach ( $prepared_args as $key => $value ) {
			$query_args[ $key ] = $value;
		}

		$query_args = $this->prepare_items_query_orderby_mappings( $query_args, $request );

		// Turn exclude and include params into proper arrays.
		foreach ( array( 'post__in', 'post__not_in' ) as $arg ) {
			if ( isset( $query_args[ $arg ] ) && ! is_array( $query_args[ $arg ] ) ) {
				$query_args[ $arg ] = array_map( 'absint', explode( ',', $query_args[ $arg ] ) );
			}
		}

		return $query_args;

	}

	/**
	 * Map to proper WP_Query orderby param.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array           $query_args WP_Query arguments.
	 * @param WP_REST_Request $request    Full details about the request.
	 * @return array Query arguments.
	 */
	protected function prepare_items_query_orderby_mappings( $query_args, $request ) {

		// Map to proper WP_Query orderby param.
		if ( isset( $query_args['orderby'] ) && isset( $request['orderby'] ) ) {
			$orderby_mappings = array(
				'id'           => 'ID',
				'title'        => 'title',
				'data_created' => 'post_date',
				'date_updated' => 'post_modified',
			);

			if ( isset( $orderby_mappings[ $request['orderby'] ] ) ) {
				$query_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
			}
		}

		return $query_args;

	}

	/**
	 * Prepares a single post for create or update.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.8 Initialize `$prepared_item` array before adding values to it.
	 *
	 * @param WP_REST_Request $request  Request object.
	 * @return array|WP_Error Array of llms post args or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {

		$prepared_item = array();

		// LLMS Post ID.
		if ( isset( $request['id'] ) ) {
			$existing_object = $this->get_object( absint( $request['id'] ) );
			if ( is_wp_error( $existing_object ) ) {
				return $existing_object;
			}

			$prepared_item['id'] = absint( $request['id'] );
		}

		$schema = $this->get_item_schema();

		// LLMS Post title.
		if ( ! empty( $schema['properties']['title'] ) && isset( $request['title'] ) ) {
			if ( is_string( $request['title'] ) ) {
				$prepared_item['post_title'] = $request['title'];
			} elseif ( ! empty( $request['title']['raw'] ) ) {
				$prepared_item['post_title'] = $request['title']['raw'];
			}
		}

		// LLMS Post content.
		if ( ! empty( $schema['properties']['content'] ) && isset( $request['content'] ) ) {
			if ( is_string( $request['content'] ) ) {
				$prepared_item['post_content'] = $request['content'];
			} elseif ( isset( $request['content']['raw'] ) ) {
				$prepared_item['post_content'] = $request['content']['raw'];
			}
		}

		// LLMS Post excerpt.
		if ( ! empty( $schema['properties']['excerpt'] ) && isset( $request['excerpt'] ) ) {
			if ( is_string( $request['excerpt'] ) ) {
				$prepared_item['post_excerpt'] = $request['excerpt'];
			} elseif ( isset( $request['excerpt']['raw'] ) ) {
				$prepared_item['post_excerpt'] = $request['excerpt']['raw'];
			}
		}

		// LLMS Post status.
		if ( ! empty( $schema['properties']['status'] ) && isset( $request['status'] ) ) {
			$status = $this->handle_status_param( $request['status'] );
			if ( is_wp_error( $status ) ) {
				return $status;
			}

			$prepared_item['post_status'] = $status;
		}

		// LLMS Post date.
		if ( ! empty( $schema['properties']['date_created'] ) && ! empty( $request['date_created'] ) ) {
			$date_data = rest_get_date_with_gmt( $request['date_created'] );

			if ( ! empty( $date_data ) ) {
				list( $prepared_item['post_date'], $prepared_item['post_date_gmt'] ) = $date_data;
				$prepared_item['edit_date'] = true;
			}
		} elseif ( ! empty( $schema['properties']['date_gmt'] ) && ! empty( $request['date_gmt'] ) ) {
			$date_data = rest_get_date_with_gmt( $request['date_created_gmt'], true );

			if ( ! empty( $date_data ) ) {
				list( $prepared_item['post_date'], $prepared_item['post_date_gmt'] ) = $date_data;
				$prepared_item['edit_date'] = true;
			}
		}

		// LLMS Post slug.
		if ( ! empty( $schema['properties']['slug'] ) && isset( $request['slug'] ) ) {
			$prepared_item['post_name'] = $request['slug'];
		}

		// LLMS Post password.
		if ( ! empty( $schema['properties']['password'] ) && isset( $request['password'] ) ) {
			$prepared_item['post_password'] = $request['password'];
		}

		// LLMS Post Menu order.
		if ( ! empty( $schema['properties']['menu_order'] ) && isset( $request['menu_order'] ) ) {
			$prepared_item['menu_order'] = (int) $request['menu_order'];
		}

		// LLMS Post Comment status.
		if ( ! empty( $schema['properties']['comment_status'] ) && ! empty( $request['comment_status'] ) ) {
			$prepared_item['comment_status'] = $request['comment_status'];
		}

		// LLMS Post Ping status.
		if ( ! empty( $schema['properties']['ping_status'] ) && ! empty( $request['ping_status'] ) ) {
			$prepared_item['ping_status'] = $request['ping_status'];
		}

		return $prepared_item;

	}

	/**
	 * Get the LLMS Posts's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @return array
	 */
	protected function get_item_schema_base() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => $this->post_type,
			'type'       => 'object',
			'properties' => array(
				'id'               => array(
					'description' => __( 'Unique Identifier. The WordPress Post ID.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_created'     => array(
					'description' => __( 'Creation date. Format: Y-m-d H:i:s', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'date_created_gmt' => array(
					'description' => __( 'Creation date (in GMT). Format: Y-m-d H:i:s', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
				),
				'date_updated'     => array(
					'description' => __( 'Date last modified. Format: Y-m-d H:i:s', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date_updated_gmt' => array(
					'description' => __( 'Date last modified (in GMT). Format: Y-m-d H:i:s', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'menu_order'       => array(
					'description' => __( 'Creation date (in GMT). Format: Y-m-d H:i:s', 'lifterlms' ),
					'type'        => 'integer',
					'default'     => 0,
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'absint',
					),
				),
				'title'            => array(
					'description' => __( 'Post title.', 'lifterlms' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
						'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
					),
					'required'    => true,
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Raw title. Useful when displaying title in the WP Block Editor. Only returned in edit context.', 'lifterlms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'Rendered title.', 'lifterlms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'content'          => array(
					'type'        => 'object',
					'description' => __( 'The HTML content of the post.', 'lifterlms' ),
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
						'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
					),
					'required'    => true,
					'properties'  => array(
						'rendered'  => array(
							'description' => __( 'Rendered HTML content.', 'lifterlms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'raw'       => array(
							'description' => __( 'Raw HTML content. Useful when displaying title in the WP Block Editor. Only returned in edit context.', 'lifterlms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'protected' => array(
							'description' => __( 'Whether the content is protected with a password.', 'lifterlms' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'excerpt'          => array(
					'type'        => 'object',
					'description' => __( 'The HTML excerpt of the post.', 'lifterlms' ),
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => null, // Note: sanitization implemented in self::prepare_item_for_database().
						'validate_callback' => null, // Note: validation implemented in self::prepare_item_for_database().
					),
					'properties'  => array(
						'rendered'  => array(
							'description' => __( 'Rendered HTML excerpt.', 'lifterlms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'raw'       => array(
							'description' => __( 'Raw HTML excerpt. Useful when displaying title in the WP Block Editor. Only returned in edit context.', 'lifterlms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'protected' => array(
							'description' => __( 'Whether the excerpt is protected with a password.', 'lifterlms' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'permalink'        => array(
					'description' => __( 'Post URL.', 'lifterlms' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'slug'             => array(
					'description' => __( 'Post URL slug.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => array( $this, 'sanitize_slug' ),
					),
				),
				'post_type'        => array(
					'description' => __( 'LifterLMS custom post type', 'lifterlms' ),
					'type'        => 'string',
					'readonly'    => true,
					'context'     => array( 'view', 'edit' ),
				),
				'status'           => array(
					'description' => __( 'The publication status of the post.', 'lifterlms' ),
					'type'        => 'string',
					'default'     => 'publish',
					'enum'        => array_keys(
						get_post_stati(
							array(
								'_builtin' => true,
								'internal' => false,
							)
						)
					),
					'context'     => array( 'view', 'edit' ),
				),
				'password'         => array(
					'description' => __( 'Password used to protect access to the content.', 'lifterlms' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
				),
				'featured_media'   => array(
					'description' => __( 'Featured image ID.', 'lifterlms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'comment_status'   => array(
					'description' => __( 'Post comment status. Default comment status dependent upon general WordPress post discussion settings.', 'lifterlms' ),
					'type'        => 'string',
					'default'     => 'open',
					'enum'        => array( 'open', 'closed' ),
					'context'     => array( 'view', 'edit' ),
				),
				'ping_status'      => array(
					'description' => __( 'Post ping status. Default ping status dependent upon general WordPress post discussion settings.', 'lifterlms' ),
					'type'        => 'string',
					'default'     => 'open',
					'enum'        => array( 'open', 'closed' ),
					'context'     => array( 'view', 'edit' ),
				),
			),
		);

		return $schema;

	}

	/**
	 * Add custom fields registered via `register_meta`.
	 *
	 * @since 1.0.0-beta.27
	 *
	 * @param array $schema The resource item schema.
	 * @return array
	 */
	protected function add_meta_fields_schema( $schema ) {
		return post_type_supports( $this->post_type, 'custom-fields' ) ? parent::add_meta_fields_schema( $schema ) : $schema;
	}

	/**
	 * Get object.
	 *
	 * @since 1.0.0-beta.9
	 *
	 * @param int $id Object ID.
	 * @return LLMS_Course|WP_Error
	 */
	protected function get_object( $id ) {

		$class = $this->llms_post_class_from_post_type();

		if ( ! $class ) {
			return new WP_Error(
				'llms_rest_cannot_get_object',
				/* translators: %s: post type */
				sprintf( __( 'The %s cannot be retrieved.', 'lifterlms' ), $this->post_type ),
				array( 'status' => 500 )
			);
		}

		$object = llms_get_post( $id );
		return $object && is_a( $object, $class ) ? $object : llms_rest_not_found_error();
	}

	/**
	 * Create an LLMS_Post_Model
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Implement generic llms post creation.
	 *
	 * @param array $object_args Object args.
	 * @return LLMS_Post_Model|WP_Error
	 */
	protected function create_llms_post( $object_args ) {

		$class = $this->llms_post_class_from_post_type();

		if ( ! $class ) {
			return new WP_Error(
				'llms_rest_cannot_create_object',
				/* translators: %s: post type */
				sprintf( __( 'The %s cannot be created.', 'lifterlms' ), $this->post_type ),
				array( 'status' => 500 )
			);
		}

		$object = new $class( 'new', $object_args );
		return $object && is_a( $object, $class ) ? $object : llms_rest_not_found_error();
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.2 Filter taxonomies by `public` property instead of `show_in_rest`.
	 * @since 1.0.0-beta.3 Filter taxonomies by `show_in_llms_rest` property instead of `public`.
	 * @since 1.0.0-beta.7 `self` and `collection` links prepared in the parent class.
	 *                     Fix wp:featured_media link, we don't expose any embeddable field.
	 * @since 1.0.0-beta.8 Return links to those taxonomies which have an accessible rest route.
	 * @since 1.0.0-beta.14 Added $request parameter.
	 *
	 * @param LLMS_Post_Model $object  Object data.
	 * @param WP_REST_Request $request Request object.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $object, $request ) {

		$links = parent::prepare_links( $object, $request );

		$object_id = $object->get( 'id' );

		// Content.
		$links['content'] = array(
			'href' => rest_url( sprintf( '/%s/%s/%d/%s', $this->namespace, $this->rest_base, $object_id, 'content' ) ),
		);

		// If we have a featured media, add that.
		$featured_media = get_post_thumbnail_id( $object_id );
		if ( $featured_media ) {
			$image_url = rest_url( 'wp/v2/media/' . $featured_media );

			$links['https://api.w.org/featuredmedia'] = array(
				'href' => $image_url,
			);
		}

		$taxonomies = get_object_taxonomies( $this->post_type );

		if ( ! empty( $taxonomies ) ) {
			$links['https://api.w.org/term'] = array();

			foreach ( $taxonomies as $tax ) {
				$taxonomy_obj = get_taxonomy( $tax );

				// Skip taxonomies that are not set to be shown in REST and LLMS REST.
				if ( empty( $taxonomy_obj->show_in_rest ) || empty( $taxonomy_obj->show_in_llms_rest ) ) {
					continue;
				}

				$tax_base = ! empty( $taxonomy_obj->rest_base ) ? $taxonomy_obj->rest_base : $tax;

				$terms_url = add_query_arg(
					'post',
					$object_id,
					rest_url( 'wp/v2/' . $tax_base )
				);

				$links['https://api.w.org/term'][] = array(
					'href'     => $terms_url,
					'taxonomy' => $tax,
				);
			}
		}

		return $links;

	}

	/**
	 * Re-add filters previously removed
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Post_Model $object Object.
	 * @return array Array of filters removed for response.
	 */
	protected function maybe_remove_filters_for_response( $object ) {

		$filters_to_be_removed = $this->get_filters_to_be_removed_for_response( $object );
		$filters_removed       = array();

		// Need to remove some filters.
		foreach ( $filters_to_be_removed as $hook => $filters ) {
			foreach ( $filters as $filter_data ) {
				$has_filter = has_filter( $hook, $filter_data['callback'] );

				if ( false !== $has_filter && $filter_data['priority'] === $has_filter ) {
					remove_filter( $hook, $filter_data['callback'], $filter_data['priority'] );
					if ( ! isset( $filters_removed[ $hook ] ) ) {
						$filters_removed[ $hook ] = array();
					}
					$filters_removed[ $hook ][] = $filter_data;

				}
			}
		}

		return $filters_removed;

	}

	/**
	 * Re-add filters previously removed
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $filters_removed Array of filters removed to be re-added.
	 * @return void
	 */
	protected function maybe_add_removed_filters_for_response( $filters_removed ) {

		if ( ! empty( $filters_removed ) ) {
			foreach ( $filters_removed as $hook => $filters ) {
				foreach ( $filters as $filter_data ) {
					add_filter(
						$hook,
						$filter_data['callback'],
						$filter_data['priority'],
						isset( $filter_data['accepted_args'] ) ? $filter_data['accepted_args'] : 1
					);
				}
			}
		}
	}

	/**
	 * Get action/filters to be removed before preparing the item for response.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.9 Removed `"llms_rest_{$this->post_type}_filters_removed_for_reponse"` filter hooks,
	 *                     `"llms_rest_{$this->post_type}_filters_removed_for_response"` added.
	 *
	 * @param LLMS_Post_Model $object LLMS_Post_Model object.
	 * @return array Array of action/filters to be removed for response.
	 */
	protected function get_filters_to_be_removed_for_response( $object ) {

		/**
		 * Modify the array of filters to be removed before building the response.
		 *
		 * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 1.0.0-beta.9
		 *
		 * @param array           $filters Array of filters to be removed.
		 * @param LLMS_Post_Model $object  LLMS_Post_Model object.
		 */
		return apply_filters( "llms_rest_{$this->post_type}_filters_removed_for_response", array(), $object );

	}

	/**
	 * Determines validity and normalizes the given status parameter.
	 * Heavily based on WP_REST_Posts_Controller::handle_status_param().
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.18 Use plural post type name.
	 *
	 * @param string $status Status.
	 * @return string|WP_Error Status or WP_Error if lacking the proper permission.
	 */
	protected function handle_status_param( $status ) {

		$post_type_object = get_post_type_object( $this->post_type );
		$post_type_name   = $post_type_object->labels->name;

		switch ( $status ) {
			case 'draft':
			case 'pending':
				break;
			case 'private':
				if ( ! current_user_can( $post_type_object->cap->publish_posts ) ) {
					// Translators: %s = The post type name.
					return llms_rest_authorization_required_error( sprintf( __( 'Sorry, you are not allowed to create private %s.', 'lifterlms' ), $post_type_name ) );
				}
				break;
			case 'publish':
			case 'future':
				if ( ! current_user_can( $post_type_object->cap->publish_posts ) ) {
					// Translators: $s = The post type name.
					return llms_rest_authorization_required_error( sprintf( __( 'Sorry, you are not allowed to publish %s.', 'lifterlms' ), $post_type_name ) );
				}
				break;
			default:
				if ( ! get_post_status_object( $status ) ) {
					$status = 'draft';
				}
				break;
		}

		return $status;
	}

	/**
	 * Determines the featured media based on a request param
	 *
	 * Heavily based on WP_REST_Posts_Controller::handle_featured_media().
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.18 Fixed call to undefined function `llms_bad_request_error()`, must be `llms_rest_bad_request_error()`.
	 *
	 * @param int $featured_media Featured Media ID.
	 * @param int $object_id      LLMS object ID.
	 * @return bool|WP_Error Whether the post thumbnail was successfully deleted, otherwise WP_Error.
	 */
	protected function handle_featured_media( $featured_media, $object_id ) {

		$featured_media = (int) $featured_media;
		if ( $featured_media ) {
			$result = set_post_thumbnail( $object_id, $featured_media );
			if ( $result ) {
				return true;
			} else {
				return llms_rest_bad_request_error( __( 'Invalid featured media ID.', 'lifterlms' ) );
			}
		} else {
			return delete_post_thumbnail( $object_id );
		}

	}

	/**
	 * Updates the post's terms from a REST request.
	 *
	 * Heavily based on WP_REST_Posts_Controller::handle_terms().
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.2 Filter taxonomies by `public` property instead of `show_in_rest`.
	 * @since 1.0.0-beta.3 Filter taxonomies by `show_in_llms_rest` property instead of `public`.
	 *
	 * @param int             $object_id The post ID to update the terms form.
	 * @param WP_REST_Request $request   The request object with post and terms data.
	 * @return null|WP_Error  WP_Error on an error assigning any of the terms, otherwise null.
	 */
	protected function handle_terms( $object_id, $request ) {

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_llms_rest' => true ) );

		foreach ( $taxonomies as $taxonomy ) {
			$base = $this->get_taxonomy_rest_base( $taxonomy );

			if ( ! isset( $request[ $base ] ) ) {
				continue;
			}

			// We could use LLMS_Post_Model::set_terms() but it doesn't return a WP_Error which can be useful here.
			$result = wp_set_object_terms( $object_id, $request[ $base ], $taxonomy->name );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}
	}

	/**
	 * Checks whether current user can assign all terms sent with the current request.
	 *
	 * Heavily based on WP_REST_Posts_Controller::check_assign_terms_permission().
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.3 Filter taxonomies by `show_in_llms_rest` property instead of `public`.
	 *
	 * @param WP_REST_Request $request The request object with post and terms data.
	 * @return bool Whether the current user can assign the provided terms.
	 */
	protected function check_assign_terms_permission( $request ) {
		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_llms_rest' => true ) );
		foreach ( $taxonomies as $taxonomy ) {
			$base = $this->get_taxonomy_rest_base( $taxonomy );

			if ( ! isset( $request[ $base ] ) ) {
				continue;
			}

			foreach ( $request[ $base ] as $term_id ) {
				// Invalid terms will be rejected later.
				if ( ! get_term( $term_id, $taxonomy->name ) ) {
					continue;
				}

				if ( ! current_user_can( 'assign_term', (int) $term_id ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Maps a taxonomy name to the relative rest base
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param object $taxonomy The taxonomy object.
	 * @return string The taxonomy rest base.
	 */
	protected function get_taxonomy_rest_base( $taxonomy ) {

		return ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;

	}

	/**
	 * Checks if a post can be edited.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return bool Whether the post can be created
	 */
	protected function check_create_permission() {

		$post_type = get_post_type_object( $this->post_type );
		return current_user_can( $post_type->cap->publish_posts );

	}

	/**
	 * Checks if an llms post can be edited.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Post_Model $object Optional. The LLMS_Post_model object. Default null.
	 * @return bool Whether the post can be edited.
	 */
	protected function check_update_permission( $object = null ) {

		$post_type = get_post_type_object( $this->post_type );
		return is_null( $object ) ? current_user_can( $post_type->cap->edit_posts ) : current_user_can( $post_type->cap->edit_post, $object->get( 'id' ) );

	}

	/**
	 * Checks if an llms post can be deleted.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Post_Model $object The LLMS_Post_model object.
	 * @return bool Whether the post can be deleted.
	 */
	protected function check_delete_permission( $object ) {

		$post_type = get_post_type_object( $this->post_type );
		return current_user_can( $post_type->cap->delete_post, $object->get( 'id' ) );

	}

	/**
	 * Checks if an llms post can be read.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0 Fix fatals when searching for llms post type based resources
	 *                  but the query post type parameter is forced to be something else.
	 *
	 * @param LLMS_Post_Model $object The LLMS_Post_model object.
	 * @return bool Whether the post can be read.
	 */
	protected function check_read_permission( $object ) {

		if ( is_wp_error( $object ) ) {
			return false;
		}

		$post_type = get_post_type_object( $this->post_type );
		$status    = $object->get( 'status' );
		$id        = $object->get( 'id' );
		$wp_post   = $object->get( 'post' );

		// Is the post readable?
		if ( 'publish' === $status || current_user_can( $post_type->cap->read_post, $id ) ) {
			return true;
		}

		$post_status_obj = get_post_status_object( $status );
		if ( $post_status_obj && $post_status_obj->public ) {
			return true;
		}

		// Can we read the parent if we're inheriting?
		if ( 'inherit' === $status && $wp_post->post_parent > 0 ) {
			$parent = get_post( $wp_post->post_parent );
			if ( $parent ) {
				return $this->check_read_permission( $parent );
			}
		}

		/*
		 * If there isn't a parent, but the status is set to inherit, assume
		 * it's published (as per get_post_status()).
		 */
		if ( 'inherit' === $status ) {
			return true;
		}

		return false;

	}


	/**
	 * Checks if the user can access password-protected content.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Post_Model $object  The LLMS_Post_model object.
	 * @param WP_REST_Request $request Request data to check.
	 * @return bool True if the user can access password-protected content, otherwise false.
	 */
	public function can_access_password_content( $object, $request ) {

		if ( empty( $object->get( 'password' ) ) ) {
			// No filter required.
			return false;
		}

		// Edit context always gets access to password-protected posts.
		if ( 'edit' === $request['context'] ) {
			return true;
		}

		// No password, no auth.
		if ( empty( $request['password'] ) ) {
			return false;
		}

		// Double-check the request password.
		return hash_equals( $object->get( 'password' ), $request['password'] );
	}

	/**
	 * Get the llms post model class from the controller post type.
	 *
	 * @since 1.0.0-beta.9
	 *
	 * @return string|bool The llms post model class name if it exists or FALSE if it doesn't.
	 */
	protected function llms_post_class_from_post_type() {

		if ( isset( $this->llms_post_class ) ) {
			return $this->llms_post_class;
		}

		$post_type = explode( '_', str_replace( 'llms_', '', $this->post_type ) );
		$class     = 'LLMS';

		foreach ( $post_type as $part ) {
			$class .= '_' . ucfirst( $part );
		}

		if ( class_exists( $class ) ) {
			$this->llms_post_class = $class;
		} else {
			$this->llms_post_class = false;
		}

		return $this->llms_post_class;
	}

	/**
	 * Sanitizes and validates the list of post statuses, including whether the user can query private statuses
	 *
	 * Heavily based on the WordPress  WP_REST_Posts_Controller::sanitize_post_statuses().
	 *
	 * @since 1.0.0-beta.19
	 *
	 * @param string|array    $statuses  One or more post statuses.
	 * @param WP_REST_Request $request   Full details about the request.
	 * @param string          $parameter Additional parameter to pass to validation.
	 * @return array|WP_Error A list of valid statuses, otherwise WP_Error object.
	 */
	public function sanitize_post_statuses( $statuses, $request, $parameter ) {
		$statuses = wp_parse_slug_list( $statuses );

		$attributes     = $request->get_attributes();
		$default_status = $attributes['args']['status']['default'];

		foreach ( $statuses as $status ) {
			if ( $status === $default_status ) {
				continue;
			}

			$post_type_obj = get_post_type_object( $this->post_type );

			if ( current_user_can( $post_type_obj->cap->edit_posts ) || 'private' === $status && current_user_can( $post_type_obj->cap->read_private_posts ) ) {
				$result = rest_validate_request_arg( $status, $request, $parameter );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
			} else {
				return llms_rest_authorization_required_error( __( 'Status is forbidden.', 'lifterlms' ) );
			}
		}

		return $statuses;
	}

}
