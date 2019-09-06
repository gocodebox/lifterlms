<?php
/**
 * REST LLMS Posts Controller Class
 *
 * @package LifterLMS_REST/Abstracts
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Posts_Controller
 *
 * @since 1.0.0-beta.1
 * @since 1.0.0-beta.2 Filter taxonomies by `public` property instead of `show_in_rest`.
 * @since 1.0.0-beta.3 Filter taxonomies by `show_in_llms_rest` property instead of `public`.
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
	);

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
	 * Get a collection of LLMS posts.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$query_args    = $this->prepare_objects_query( $request );
		$query_results = $this->get_objects( $query_args, $request );

		$page        = (int) $query_args['paged'];
		$max_pages   = $query_results['pages'];
		$total_posts = $query_results['total'];
		$objects     = $query_results['objects'];

		if ( $page > $max_pages && $total_posts > 0 ) {
			return llms_rest_bad_request_error( __( 'The page number requested is larger than the number of pages available.', 'lifterlms' ) );
		}

		$response = rest_ensure_response( $objects );

		$response->header( 'X-WP-Total', $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

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
	 * Check if a given request has access to create an item.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {

		$post_type_object = get_post_type_object( $this->post_type );
		$post_type_name   = $post_type_object->labels->singular_name;

		if ( ! empty( $request['id'] ) ) {
			// translators: The post type singular name.
			return llms_rest_bad_request_error( sprintf( __( 'Cannot create existing %s.', 'lifterlms' ), $post_type_name ) );
		}

		if ( ! $this->check_create_permission() ) {
			// translators: The post type singular name.
			return llms_rest_authorization_required_error( sprintf( __( 'Sorry, you are not allowed to create a %s as this user.', 'lifterlms' ), $post_type_name ) );
		}

		if ( ! $this->check_assign_terms_permission( $request ) ) {
			return llms_rest_authorization_required_error( __( 'Sorry, you are not allowed to assign the provided terms.', 'lifterlms' ) );
		}

		return true;
	}


	/**
	 * Creates a single LLMS post.
	 *
	 * Extending classes can add additional object fields by overriding the method update_additional_object_fields()
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {

		$prepared_item = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $prepared_item ) ) {
			return $prepared_item;
		}

		$object = $this->create_llms_post( $prepared_item );
		if ( is_wp_error( $object ) ) {

			if ( 'db_insert_error' === $object->get_error_code() ) {
				$object->add_data( array( 'status' => 500 ) );
			} else {
				$object->add_data( array( 'status' => 400 ) );
			}

			return $object;
		}

		// Set all the other properties.
		// TODO: maybe we want to filter the post properties that have already been inserted before.
		$set_bulk_result = $object->set_bulk( $prepared_item, true );
		if ( is_wp_error( $set_bulk_result ) ) {

			if ( 'db_update_error' === $set_bulk_result->get_error_code() ) {
				$set_bulk_result->add_data( array( 'status' => 500 ) );
			} else {
				$set_bulk_result->add_data( array( 'status' => 400 ) );
			}

			return $set_bulk_result;
		}

		$object_id = $object->get( 'id' );

		$schema = $this->get_item_schema();

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

		/**
		 * TODO: understand how to treat possible conflicting properties => instructors are registered as additional rest field by llms_blocks
		 */
		// $fields_update = $this->update_additional_fields_for_object( $object, $request );
		// if ( is_wp_error( $fields_update ) ) {
		// return $fields_update;
		// }
		$request->set_param( 'context', 'edit' );

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
	 * Prepare objects query.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {

		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();
		$args       = array();

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

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = $this->post_type;

		$query_args = $this->prepare_items_query( $args, $request );

		return $query_args;

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

		$object = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$post_type_object = get_post_type_object( $this->post_type );
		$post_type_name   = $post_type_object->labels->singular_name;

		if ( ! $this->check_update_permission( $object ) ) {
			// translators: The post type singular name.
			return llms_rest_authorization_required_error( sprintf( __( 'Sorry, you are not allowed to update a %s as this user.', 'lifterlms' ), $post_type_name ) );
		}

		if ( ! $this->check_assign_terms_permission( $request ) ) {
			return llms_rest_authorization_required_error( __( 'Sorry, you are not allowed to assign the provided terms.', 'lifterlms' ) );
		}

		return true;
	}

	/**
	 * Updates a single llms post.
	 *
	 * Extending classes can add additional object fields by overriding the method update_additional_object_fields().
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {

		$object = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $object ) ) {
			return $object;
		}

		$prepared_item = $this->prepare_item_for_database( $request );
		if ( is_wp_error( $prepared_item ) ) {
			return $prepared_item;
		}

		$update_result = $object->set_bulk( $prepared_item, true );
		if ( is_wp_error( $update_result ) ) {

			if ( 'db_update_error' === $update_result->get_error_code() ) {
				$update_result->add_data( array( 'status' => 500 ) );
			} else {
				$update_result->add_data( array( 'status' => 400 ) );
			}

			return $update_result;
		}

		$object_id = $object->get( 'id' );

		$schema = $this->get_item_schema();

		$additional_fields = $this->update_additional_object_fields( $object, $request, $schema, $prepared_item, false );
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

		/**
		 * TODO: understand how to treat possible conflicting properties => instructors are registered as additional rest field by llms_blocks
		 */
		// $fields_update = $this->update_additional_fields_for_object( $object, $request );
		// if ( is_wp_error( $fields_update ) ) {
		// return $fields_update;
		// }
		$request->set_param( 'context', 'edit' );

		return $this->prepare_item_for_response( $object, $request );

	}

	/**
	 * Updates a single llms post.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Post_Model $object        LMMS_Post_Model instance.
	 * @param array           $prepared_item Array.
	 * @param WP_REST_Request $request       Full details about the request.
	 * @param array           $schema        The item schema.
	 * @return bool|WP_Error True on success, WP_Error object otherwise.
	 */
	protected function update_additional_object_fields( $object, $prepared_item, $request, $schema ) {
		return true;
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

		$object = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $object ) ) {
			// Course not found, we don't return a 404.
			if ( in_array( 'llms_rest_not_found', $object->get_error_codes(), true ) ) {
				return true;
			}

			return $object;
		}

		if ( ! $this->check_delete_permission( $object ) ) {
			return llms_rest_authorization_required_error();
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
	 * Get objects.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array           $query_args Query args.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function get_objects( $query_args, $request ) {

		$query  = new WP_Query();
		$result = $query->query( $query_args );

		$objects = array();

		// Allow access to all password protected posts if the context is edit.
		if ( 'edit' === $request['context'] ) {
			add_filter( 'post_password_required', '__return_false' );
		}

		foreach ( $result as $post ) {
			$object = $this->get_object( $post );
			if ( ! $this->check_read_permission( $object ) ) {
				continue;
			}

			$response_object = $this->prepare_item_for_response( $object, $request );
			if ( ! is_wp_error( $response_object ) ) {
				$objects[] = $this->prepare_response_for_collection( $response_object );
			}
		}

		// Reset filter.
		if ( 'edit' === $request['context'] ) {
			remove_filter( 'post_password_required', '__return_false' );
		}

		$total_posts = $query->found_posts;

		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		return array(
			'objects' => $objects,
			'total'   => (int) $total_posts,
			'pages'   => (int) ceil( $total_posts / (int) $query->query_vars['posts_per_page'] ),
		);

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
	 * Prepare a single item for the REST response
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param LLMS_Post_Model $object  LLMS post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function prepare_item_for_response( $object, $request ) {

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';

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

		$data = $this->prepare_object_for_response( $object, $request );

		if ( $has_password_filter ) {
			// Reset filter.
			remove_filter( 'post_password_required', '__return_false' );
		}

		$this->maybe_add_removed_filters_for_response( $removed_filters_for_response );
		$post = $temp; // phpcs:ignore
		wp_reset_postdata();

		// Filter data including only schema props.
		$data = array_intersect_key( $data, array_flip( $this->get_fields_for_response( $request ) ) );

		// Filter data by context. E.g. in "view" mode the password property won't be allowed.
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$links = $this->prepare_links( $object );
		$response->add_links( $links );

		return $response;
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
	 *
	 * @param WP_REST_Request $request  Request object.
	 * @return array|WP_Error Array of llms post args or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {

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
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_item_schema() {

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
					'enum'        => array_merge( array_keys( get_post_statuses() ), array( 'future', 'trash', 'auto-draft' ) ),
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

		/**
		 * TODO: understand how to treat possible conflicting properties => instructors are registered as additional rest field by llms_blocks.
		 */
		// $schema = $this->add_additional_fields_schema( $schema );
		return $schema;
	}

	/**
	 * Create an LLMS_Post_Model
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $object_args Object args.
	 * @return LLMS_Post_Model|WP_Error
	 */
	abstract protected function create_llms_post( $object_args );

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.2 Filter taxonomies by `public` property instead of `show_in_rest`.
	 * @since 1.0.0-beta.3 Filter taxonomies by `show_in_llms_rest` property instead of `public`.
	 *
	 * @param LLMS_Post_Model $object  Object data.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $object ) {
		$object_id = $object->get( 'id' );

		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $object_id ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		// Content.
		$links['content'] = array(
			'href' => rest_url( sprintf( '/%s/%s/%d/%s', $this->namespace, $this->rest_base, $object_id, 'content' ) ),
		);

		// If we have a featured media, add that.
		$featured_media = get_post_thumbnail_id( $object_id );
		if ( $featured_media ) {
			$image_url = rest_url( 'wp/v2/media/' . $featured_media );

			$links['https://api.w.org/featuredmedia'] = array(
				'href'       => $image_url,
				'embeddable' => true,
			);
		}

		$taxonomies = get_object_taxonomies( $this->post_type );

		if ( ! empty( $taxonomies ) ) {
			$links['https://api.w.org/term'] = array();

			foreach ( $taxonomies as $tax ) {
				$taxonomy_obj = get_taxonomy( $tax );

				// Skip taxonomies that are not set to be shown in LLMS REST.
				if ( empty( $taxonomy_obj->show_in_llms_rest ) ) {
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
	 * @return array Array of filters removed for response
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
	 *
	 * @param LLMS_Post_Model $object LLMS_Post_Model object.
	 * @return array Array of action/filters to be removed for response.
	 */
	protected function get_filters_to_be_removed_for_response( $object ) {

		/**
		 * Modify the array of filters to be removed before building the response.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param array           $filters Array of filters to be removed.
		 * @param LLMS_Post_Model $object  LLMS_Post_Model object.
		 */
		return apply_filters( "llms_rest_{$this->post_type}_filters_removed_for_reponse", array(), $object );

	}

	/**
	 * Determines validity and normalizes the given status parameter.
	 * Heavily based on WP_REST_Posts_Controller::handle_status_param().
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param string $status Status.
	 * @return string|WP_Error Status or WP_Error if lacking the proper permission.
	 */
	protected function handle_status_param( $status ) {

		$post_type_object = get_post_type_object( $this->post_type );
		$post_type_name   = $post_type_object->labels->singular_name;

		switch ( $status ) {
			case 'draft':
			case 'pending':
				break;
			case 'private':
				if ( ! current_user_can( $post_type_object->cap->publish_posts ) ) {
					// translators: The post type singular name.
					return llms_rest_authorization_required_error( sprintf( __( 'Sorry, you are not allowed to create a private %s.', 'lifterlms' ), $post_type_name ) );
				}
				break;
			case 'publish':
			case 'future':
				if ( ! current_user_can( $post_type_object->cap->publish_posts ) ) {
					// translators: The post type singular name.
					return llms_rest_authorization_required_error( sprintf( __( 'Sorry, you are not allowed to publish a %s.', 'lifterlms' ), $post_type_name ) );
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
	 * Determines the featured media based on a request param.
	 * Heavily based on WP_REST_Posts_Controller::handle_featured_media().
	 *
	 * @since 1.0.0-beta.1
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
				return llms_bad_request_error( __( 'Invalid featured media ID.', 'lifterlms' ) );
			}
		} else {
			return delete_post_thumbnail( $object_id );
		}

	}

	/**
	 * Updates the post's terms from a REST request.
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
	 *
	 * @param LLMS_Post_Model $object The LLMS_Post_model object.
	 * @return bool Whether the post can be read.
	 */
	protected function check_read_permission( $object ) {

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

}
