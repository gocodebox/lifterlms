<?php
// restrict direct access
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS REST API
 * @author    LifterLMS
 * @category  API
 * @package   LifterLMS/API
 * @since     [version]
 * @version   [version]
 */
class LLMS_REST_Controller_Notifications extends LLMS_Abstract_REST_Controller {

	/**
	 * The base of this controller's route.
	 * @var string
	 * @since [version]
	 * @since [version]
	 */
	protected $rest_base = 'notifications';

	/**
	 * Default orderby enum options
	 * @var  array
	 * @since [version]
	 * @since [version]
	 */
	protected $orderby_enum = array( 'updated', 'created', 'id' );


	/**
	 * Register notifications routes
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args' => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'lifterlms' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/batch', array(
			array(
				'methods' => WP_REST_Server::EDITABLE,
				'callback' => array( $this, 'batch_items' ),
				// 'permission_callback' => array( $this, 'batch_item_permissions_check' ),
				'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

	}

	/**
	 * Batch Update notifications
	 * @param    WP_REST_Request     $request  request object
	 * @return   WP_REST_Response
	 * @since    [version]
	 * @version  [version]
	 */
	public function batch_items( $request ) {

		/** @var WP_REST_Server $wp_rest_server */
		global $wp_rest_server;

		// Get the request params.
		$items    = array_filter( $request->get_params() );
		$response = array();

		// Check batch limit.
		$limit = $this->check_batch_limit( $items );

		if ( is_wp_error( $limit ) ) {
			return $limit;
		}

		if ( ! empty( $items['update'] ) ) {
			foreach ( $items['update'] as $item ) {
				$_item = new WP_REST_Request( 'PUT' );
				$_item->set_body_params( $item );
				$_response = $this->update_item( $_item );
				if ( is_wp_error( $_response ) ) {
					$response['update'][] = array(
						'id'    => $item['id'],
						'error' => array( 'code' => $_response->get_error_code(), 'message' => $_response->get_error_message(), 'data' => $_response->get_error_data() ),
					);
				} else {
					$response['update'][] = $wp_rest_server->response_to_data( $_response, '' );
				}
			}
		}

		return $response;

	}

	/**
	 * Retrieves the query params for the collections.
	 * @return  array
	 * @since   [version]
	 * @version [version]
	 */
	public function get_collection_params() {

		$params = parent::get_collection_params();

		$params['status'] = array(
			'default' => 'any',
			'description' => __( 'Limit result set to notifications assigned a specific status.', 'lifterlms' ),
			'type' => 'string',
			'enum' => array_merge( array( 'any' ), LLMS()->notifications()->get_statuses() ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$params['subscriber'] = array(
			'default' => null,
			'description' => __( 'Limit result set to notifications for a specific subscriber.', 'lifterlms' ),
			'type' => 'string',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => array( $this, 'validate_subscriber' ),
		);

		$params['type'] = array(
			'default' => 'any',
			'description' => __( 'Limit result set to notifications assigned a specific status.', 'lifterlms' ),
			'type' => 'string',
			'enum' => array_merge( array( 'any' ), LLMS()->notifications()->get_types() ),
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;

	}

	/**
	 * Retrieve a single item
	 * @param    WP_REST_Request     $request  request object
	 * @return   WP_REST_Response
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_item( $request ) {

		return rest_ensure_response( $this->prepare_item_for_response( $request->get_param( 'id' ), $request ) );

	}

	/**
	 * Check permissions
	 * @param    obj     $request  WP_REST_Request
	 * @return   true|WP_Error
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_item_permissions_check( $request ) {

		if ( ! current_user_can( 'manage_lifterlms' ) ) {

			$notification = new LLMS_Notification( $request->get_param( 'id' ) );
			if ( get_current_user_id() != $notification->get( 'subscriber' ) ) {
				return new WP_Error( 'llms_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'lifterlms' ), array( 'status' => rest_authorization_required_code() ) );
			}

		}

		return true;

	}

	/**
	 * Get the notification's schema
	 * @return array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title' => 'notification',
			'type' => 'object',
			'properties' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'lifterlms' ),
					'type' => 'integer',
					'context' => array( 'view', 'edit' ),
					'readonly' => true,
				),
				'created' => array(
					'description' => __( "The date the resource was created, in the site's timezone.", 'lifterlms' ),
					'type' => 'date-time',
					'context' => array( 'view', 'edit' ),
					'readonly' => true,
				),
				'updated' => array(
					'description' => __( "The date the resource was updated, in the site's timezone.", 'lifterlms' ),
					'type' => 'date-time',
					'context' => array( 'view', 'edit' ),
					'readonly' => true,
				),
				'post_id' => array(
					'description' => __( 'The unique identifier of the related post (course or membership).', 'lifterlms' ),
					'type' => 'integer',
					'context' => array( 'view', 'edit' ),
					'readonly' => false,
				),
				'status' => array(
					'description' => __( 'Resource status.', 'lifterlms' ),
					'type' => 'string',
					'default' => 'new',
					'enum' => LLMS()->notifications()->get_statuses(),
					'context' => array( 'view', 'edit' ),
					'readonly' => false,
				),
				'subscriber' => array(
					'description' => __( 'The identifier of the subscriber.', 'lifterlms' ),
					'type' => 'string',
					'context' => array( 'view', 'edit' ),
					'readonly' => false,
				),
				'trigger_id' => array(
					'description' => __( 'The identifier of the triggering action.', 'lifterlms' ),
					'type' => 'string',
					'context' => array( 'view', 'edit' ),
					'readonly' => false,
				),
				'type' => array(
					'description' => __( 'Notification type.', 'lifterlms' ),
					'type' => 'string',
					'default' => 'new',
					'enum' => LLMS()->notifications()->get_types(),
					'context' => array( 'view', 'edit' ),
					'readonly' => false,
				),
				'user_id' => array(
					'description' => __( 'Unique identifier of the user who triggered the resource.', 'lifterlms' ),
					'type' => 'integer',
					'context' => array( 'view', 'edit' ),
					'readonly' => false,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );

	}

	/**
	 * Retrieve items
	 * @param    obj     $request  WP_REST_Request
	 * @return   obj               WP_Rest_Response
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_items( $request ) {

		$subscriber = $request->get_param( 'subscriber' );
		if ( 'self' === $subscriber ) {
			$subscriber = get_current_user_id();
		}

		$query = new LLMS_Notifications_Query( array(
			'page' => $request->get_param( 'page' ),
			'per_page' => $request->get_param( 'per_page' ),
			'statuses' => $request->get_param( 'status' ),
			'types' => $request->get_param( 'type' ),
			'subscriber' => $subscriber,
			'sort' => array(
				$request->get_param( 'orderby' ) => strtoupper( $request->get_param( 'order' ) ),
			),
		) );

		$params = $request->get_query_params();
		if ( isset( $params['_'] ) ) {
			unset( $params['_'] );
		}

		$notifications = array();
		foreach ( $query->get_notifications() as $item ) {
			$item = $this->prepare_item_for_response( $item, $request );
			$notifications[] = $this->prepare_response_for_collection( $item );
		}

		$res = rest_ensure_response( $notifications );

		$res->header( 'X-WP-Total', $query->found_results );
		$res->header( 'X-WP-TotalPages', $query->max_pages );

		// setup pagination header links
		$base = add_query_arg( $params, rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

		$page = $request->get_param( 'page' );

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			if ( $prev_page > $query->max_pages ) {
				$prev_page = $query->max_pages;
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$res->link_header( 'prev', $prev_link );
		}

		if ( $query->max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$res->link_header( 'next', $next_link );
		}

		return $res;

	}

	/**
	 * Check permissions
	 * @param    obj     $request  WP_REST_Request
	 * @return   true|WP_Error
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! is_user_logged_in() ) {

			return new WP_Error( 'llms_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'lifterlms' ), array( 'status' => rest_authorization_required_code() ) );

		} elseif ( 'self' !== $request->get_param( 'subscriber' ) && get_current_user_id() !== $request->get_param( 'subscriber' ) ) {

			if ( ! current_user_can( 'manage_lifterlms' ) ) {

				return new WP_Error( 'llms_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'lifterlms' ), array( 'status' => rest_authorization_required_code() ) );

			}

		}

		return true;

	}

	/**
	 * Prepare a notification for a resonse
	 * @param    int|obj          $item     Notification object or notification ID
	 * @param    WP_REST_Request  $request  request object
	 * @return   WP_REST_Response
	 * @since    [version]
	 * @version  [version]
	 */
	public function prepare_item_for_response( $item, $request ) {

		if ( is_numeric( $item ) ) {
			$id = $item;
		} elseif ( isset( $item->id ) ) {
			$id = $item->id;
		}

		$item = new LLMS_Notification( $id );

		$response = rest_ensure_response( $item->toArray() );
		$response->add_links( $this->prepare_links( $item, $request ) );

		return $response;

	}

	/**
	 * Retrieve the schema for a batch request
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_public_batch_schema() {

		$schema = array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title' => 'batch',
			'type' => 'object',
			'properties' => array(
				// 'create' => array(
				// 	'description' => __( 'List of created resources.', 'lifterlms' ),
				// 	'type' => 'array',
				// 	'context' => array( 'view', 'edit' ),
				// 	'items' => array(
				// 		'type' => 'object',
				// 	),
				// ),
				'update' => array(
					'description' => __( 'List of updated resources.', 'lifterlms' ),
					'type' => 'array',
					'context' => array( 'view', 'edit' ),
					'items' => array(
						'type' => 'object',
					),
				),
				// 'delete' => array(
				// 	'description' => __( 'List of delete resources.', 'lifterlms' ),
				// 	'type' => 'array',
				// 	'context' => array( 'view', 'edit' ),
				// 	'items' => array(
				// 		'type' => 'integer',
				// 	),
				// ),
			),
		);

		return $schema;

	}

	/**
	 * Prep links to add to a single notification
	 * @param    obj              $item     single notification item
	 * @param    WP_Rest_Request  $request  request object
	 * @return   WP_REST_Response
	 * @since    [version]
	 * @version  [version]
	 */
	protected function prepare_links( $item, $request ) {

		$links = array(
			'self' => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $item->id ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		return $links;

	}

	/**
	 * Update single item
	 * @param    obj     $request  WP_REST_Request
	 * @return   obj               WP_Rest_Response
	 * @since    [version]
	 * @version  [version]
	 */
	public function update_item( $request ) {

		$notification = new LLMS_Notification( $request->get_param( 'id' ) );

		$allowed = array_keys( $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ) );

		foreach ( $request->get_params() as $key => $val ) {

			if ( in_array( $key, $allowed ) ) {

				$notification->set( $key, $val );

			}

		}

		return rest_ensure_response( $this->prepare_item_for_response( $request->get_param( 'id' ), $request ) );

	}

	/**
	 * Check permissions
	 * @param    obj     $request  WP_REST_Request
	 * @return   true|WP_Error
	 * @since    [version]
	 * @version  [version]
	 */
	public function update_item_permissions_check( $request ) {

		if ( ! current_user_can( 'manage_lifterlms' ) ) {

			$notification = new LLMS_Notification( $request->get_param( 'id' ) );
			if ( get_current_user_id() != $notification->get( 'subscriber' ) ) {
				return new WP_Error( 'llms_rest_cannot_view', __( 'Sorry, you cannot update resources.', 'lifterlms' ), array( 'status' => rest_authorization_required_code() ) );
			}

		}

		return true;

	}

	/**
	 * Validate submitted subscriber values
	 * @param    mixed     $val      user submitted value
	 * @param    obj       $request  WP_REST_Request
	 * @param    array     $param    Params
	 * @return   bool
	 * @since    [version]
	 * @version  [version]
	 */
	public function validate_subscriber( $val, $request, $param ) {

		if ( is_numeric( $val ) ) {

			return $val;

		} elseif ( 'self' === $val ) {

			return true;

		}

		return false;

	}

}
