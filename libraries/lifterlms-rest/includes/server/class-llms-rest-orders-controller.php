<?php
/**
 * REST Orders Controller
 *
 * @package LifterLMS_REST/Classes/Controllers
 *
 * @since 10.2.0
 * @version 10.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Orders_Controller class.
 *
 * Read-only controller: orders can only be created through checkout (or the admin panel),
 * so no create/update/delete routes are registered.
 *
 * @since 10.2.0
 */
class LLMS_REST_Orders_Controller extends LLMS_REST_Posts_Controller {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'llms_order';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'orders';

	/**
	 * Whether search is allowed.
	 *
	 * @var boolean
	 */
	protected $is_searchable = false;

	/**
	 * Schema properties available for ordering the collection.
	 *
	 * @var string[]
	 */
	protected $orderby_properties = array(
		'id',
		'date_created',
		'date_updated',
		'title',
	);

	/**
	 * Register routes.
	 *
	 * Only read routes: orders are managed through checkout and the admin panel.
	 *
	 * @since 10.2.0
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
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the order. The WordPress Post ID.', 'lifterlms' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => $this->get_get_item_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/transactions',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the order. The WordPress Post ID.', 'lifterlms' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_transactions' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'page'     => array(
							'description' => __( 'Current page of the collection.', 'lifterlms' ),
							'type'        => 'integer',
							'default'     => 1,
							'minimum'     => 1,
						),
						'per_page' => array(
							'description' => __( 'Maximum number of results to be returned in the result set.', 'lifterlms' ),
							'type'        => 'integer',
							'default'     => 10,
							'minimum'     => 1,
							'maximum'     => 100,
						),
					),
				),
			)
		);
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @since 10.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'manage_lifterlms' ) ) {
			return llms_rest_authorization_required_error();
		}

		return true;
	}

	/**
	 * Checks if an order can be read.
	 *
	 * @since 10.2.0
	 *
	 * @param LLMS_Order $object The LLMS_Order object.
	 * @return bool Whether the order can be read.
	 */
	protected function check_read_permission( $object ) {
		return current_user_can( 'manage_lifterlms' );
	}

	/**
	 * Whether the trash is supported.
	 *
	 * @since 10.2.0
	 *
	 * @return bool
	 */
	protected function is_trash_supported() {
		return false;
	}

	/**
	 * Retrieves the query params for the objects collection.
	 *
	 * @since 10.2.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {

		$query_params = parent::get_collection_params();

		$query_params['status'] = array(
			'description' => __( 'Limit results to orders assigned one or more statuses. By default orders of any status are returned.', 'lifterlms' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'string',
				'enum' => $this->get_order_statuses(),
			),
			'default'     => array(),
		);

		$query_params['student'] = array(
			'description' => __( 'Limit results to orders placed by a specific student. Accepts a WP_User ID.', 'lifterlms' ),
			'type'        => 'integer',
		);

		$query_params['product'] = array(
			'description' => __( 'Limit results to orders for a specific product (course or membership). Accepts a WP_Post ID.', 'lifterlms' ),
			'type'        => 'integer',
		);

		$query_params['plan'] = array(
			'description' => __( 'Limit results to orders for a specific access plan. Accepts a WP_Post ID.', 'lifterlms' ),
			'type'        => 'integer',
		);

		return $query_params;
	}

	/**
	 * Format query arguments to retrieve a collection of objects.
	 *
	 * @since 10.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error
	 */
	protected function prepare_collection_query_args( $request ) {

		$query_args = parent::prepare_collection_query_args( $request );
		if ( is_wp_error( $query_args ) ) {
			return $query_args;
		}

		// Map the (prefix-stripped) status param to real order post statuses, defaulting to all of them.
		$statuses = array_filter( (array) $request['status'] );
		if ( empty( $statuses ) ) {
			$query_args['post_status'] = array_keys( llms_get_order_statuses() );
		} else {
			$query_args['post_status'] = array_map(
				function ( $status ) {
					return 'llms-' . $status;
				},
				$statuses
			);
		}

		$meta_query = array();

		if ( ! empty( $request['student'] ) ) {
			$meta_query[] = array(
				'key'   => '_llms_user_id',
				'value' => absint( $request['student'] ),
			);
		}

		if ( ! empty( $request['product'] ) ) {
			$meta_query[] = array(
				'key'   => '_llms_product_id',
				'value' => absint( $request['product'] ),
			);
		}

		if ( ! empty( $request['plan'] ) ) {
			$meta_query[] = array(
				'key'   => '_llms_plan_id',
				'value' => absint( $request['plan'] ),
			);
		}

		if ( $meta_query ) {
			$meta_query['relation']   = 'AND';
			$query_args['meta_query'] = $meta_query;
		}

		return $query_args;
	}

	/**
	 * Prepare a single object output for response.
	 *
	 * @since 10.2.0
	 *
	 * @param LLMS_Order      $order   Order object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array
	 */
	protected function prepare_object_for_response( $order, $request ) {

		$data = parent::prepare_object_for_response( $order, $request );

		$data['status'] = str_replace( 'llms-', '', $data['status'] );

		$props = array(
			'order_key',
			'order_type',
			'user_id',
			'billing_email',
			'billing_first_name',
			'billing_last_name',
			'product_id',
			'product_type',
			'plan_id',
			'coupon_id',
			'coupon_code',
			'payment_gateway',
			'currency',
			'original_total',
			'coupon_amount',
			'sale_value',
			'total',
			'trial_original_total',
			'trial_total',
			'billing_frequency',
			'billing_period',
			'billing_length',
			'date_next_payment',
			'date_access_expires',
			'date_trial_end',
		);

		foreach ( $props as $prop ) {
			$data[ $prop ] = $order->get( $prop );
		}

		return $data;
	}

	/**
	 * Get the Order's schema, conforming to JSON Schema.
	 *
	 * @since 10.2.0
	 *
	 * @return array Item schema data.
	 */
	protected function get_item_schema_base() {

		$schema = parent::get_item_schema_base();

		// Orders don't expose these post props.
		unset(
			$schema['properties']['content'],
			$schema['properties']['excerpt'],
			$schema['properties']['permalink'],
			$schema['properties']['slug'],
			$schema['properties']['password'],
			$schema['properties']['featured_media'],
			$schema['properties']['comment_status'],
			$schema['properties']['ping_status'],
			$schema['properties']['menu_order']
		);

		$schema['properties']['title']['required'] = false;

		$schema['properties']['status'] = array(
			'description' => __( 'The order status.', 'lifterlms' ),
			'type'        => 'string',
			'enum'        => $this->get_order_statuses(),
			'context'     => array( 'view', 'edit' ),
			'readonly'    => true,
		);

		$order_properties = array(
			'order_key'            => array(
				'description' => __( 'The unique order key.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'order_type'           => array(
				'description' => __( 'The order type.', 'lifterlms' ),
				'type'        => 'string',
				'enum'        => array( 'single', 'recurring' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'user_id'              => array(
				'description' => __( 'The WP_User ID of the student who placed the order.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'billing_email'        => array(
				'description' => __( 'Billing email address.', 'lifterlms' ),
				'type'        => 'string',
				'format'      => 'email',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'billing_first_name'   => array(
				'description' => __( 'Billing first name.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'billing_last_name'    => array(
				'description' => __( 'Billing last name.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'product_id'           => array(
				'description' => __( 'The WP_Post ID of the purchased product (course or membership).', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'product_type'         => array(
				'description' => __( 'The purchased product type.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'plan_id'              => array(
				'description' => __( 'The WP_Post ID of the access plan used to place the order.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'coupon_id'            => array(
				'description' => __( 'The WP_Post ID of the coupon used, if any.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'coupon_code'          => array(
				'description' => __( 'The coupon code used, if any.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'payment_gateway'      => array(
				'description' => __( 'The LifterLMS payment gateway ID used to process the order.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'currency'             => array(
				'description' => __( 'The order currency code.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'original_total'       => array(
				'description' => __( 'Total price of the order before coupon adjustments.', 'lifterlms' ),
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'coupon_amount'        => array(
				'description' => __( 'Amount of the coupon adjustment applied to the order.', 'lifterlms' ),
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'sale_value'           => array(
				'description' => __( 'Amount saved due to a sale in effect at the time of purchase.', 'lifterlms' ),
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'total'                => array(
				'description' => __( 'Total price of the order after all adjustments.', 'lifterlms' ),
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'trial_original_total' => array(
				'description' => __( 'Total price of the trial before applicable coupon adjustments.', 'lifterlms' ),
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'trial_total'          => array(
				'description' => __( 'Total price of the trial after applicable coupon adjustments.', 'lifterlms' ),
				'type'        => 'number',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'billing_frequency'    => array(
				'description' => __( 'Billing frequency for recurring orders. `0` for one-time payments.', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'billing_period'       => array(
				'description' => __( 'Billing period for recurring orders.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'billing_length'       => array(
				'description' => __( 'Number of billing intervals for recurring orders. `0` means "until cancelled".', 'lifterlms' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'date_next_payment'    => array(
				'description' => __( 'Date of the next scheduled payment for recurring orders. Format: Y-m-d H:i:s.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'date_access_expires'  => array(
				'description' => __( 'Date when the student\'s access to the product expires. Format: Y-m-d H:i:s.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'date_trial_end'       => array(
				'description' => __( 'Date when the trial period ends for recurring orders with a trial. Format: Y-m-d H:i:s.', 'lifterlms' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);

		$schema['properties'] = array_merge( $schema['properties'], $order_properties );

		return $schema;
	}

	/**
	 * Retrieve a list of transactions for the order.
	 *
	 * @since 10.2.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_transactions( $request ) {

		$order = $this->get_object( (int) $request['id'] );
		if ( is_wp_error( $order ) ) {
			return $order;
		}

		$result = $order->get_transactions(
			array(
				'per_page' => $request['per_page'],
				'paged'    => $request['page'],
			)
		);

		$transactions = array();
		foreach ( $result['transactions'] as $transaction ) {
			$transactions[] = $this->prepare_transaction_for_response( $transaction, $order );
		}

		$response = rest_ensure_response( $transactions );
		$response->header( 'X-WP-Total', (int) $result['total'] );
		$response->header( 'X-WP-TotalPages', (int) $result['pages'] );

		return $response;
	}

	/**
	 * Prepare a single transaction for the transactions list response.
	 *
	 * @since 10.2.0
	 *
	 * @param LLMS_Transaction $transaction Transaction object.
	 * @param LLMS_Order       $order       Parent order object.
	 * @return array
	 */
	protected function prepare_transaction_for_response( $transaction, $order ) {

		return array(
			'id'                         => $transaction->get( 'id' ),
			'order_id'                   => $order->get( 'id' ),
			'status'                     => str_replace( 'llms-txn-', '', $transaction->get( 'status' ) ),
			'date_created'               => $transaction->get_date( 'date', 'Y-m-d H:i:s' ),
			'payment_type'               => $transaction->get( 'payment_type' ),
			'payment_gateway'            => $transaction->get( 'payment_gateway' ),
			'currency'                   => $transaction->get( 'currency' ),
			'amount'                     => $transaction->get( 'amount' ),
			'refund_amount'              => $transaction->get( 'refund_amount' ),
			'gateway_transaction_id'     => $transaction->get( 'gateway_transaction_id' ),
			'gateway_source_description' => $transaction->get( 'gateway_source_description' ),
		);
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 10.2.0
	 *
	 * @param LLMS_Order      $object  Object data.
	 * @param WP_REST_Request $request Request object.
	 * @return array Links for the given object.
	 */
	protected function prepare_links( $object, $request ) {

		$links = parent::prepare_links( $object, $request );

		// Orders don't expose a content route.
		unset( $links['content'] );

		$object_id = $object->get( 'id' );

		$links['transactions'] = array(
			'href' => rest_url( sprintf( '/%1$s/%2$s/%3$d/transactions', $this->namespace, $this->rest_base, $object_id ) ),
		);

		$user_id = $object->get( 'user_id' );
		if ( $user_id ) {
			$links['student'] = array(
				'href'       => rest_url( sprintf( '/%1$s/students/%2$d', $this->namespace, $user_id ) ),
				'embeddable' => true,
			);
		}

		$product_id        = $object->get( 'product_id' );
		$product_rest_base = 'course' === get_post_type( $product_id ) ? 'courses' : 'memberships';
		if ( $product_id ) {
			$links['product'] = array(
				'href'       => rest_url( sprintf( '/%1$s/%2$s/%3$d', $this->namespace, $product_rest_base, $product_id ) ),
				'embeddable' => true,
			);
		}

		$plan_id = $object->get( 'plan_id' );
		if ( $plan_id ) {
			$links['plan'] = array(
				'href'       => rest_url( sprintf( '/%1$s/access-plans/%2$d', $this->namespace, $plan_id ) ),
				'embeddable' => true,
			);
		}

		return $links;
	}

	/**
	 * Retrieve the list of order statuses with the `llms-` prefix stripped.
	 *
	 * @since 10.2.0
	 *
	 * @return string[]
	 */
	protected function get_order_statuses() {

		return array_map(
			function ( $status ) {
				return str_replace( 'llms-', '', $status );
			},
			array_keys( llms_get_order_statuses() )
		);
	}
}
