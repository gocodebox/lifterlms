<?php
/**
 * Orders & Transactions Reporting Table
 *
 * @package LifterLMS/Admin/Reporting/Tables/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Orders_Transactions class.
 *
 * Displays individual transactions across all orders in an LLMS Admin Table,
 * joining parent order data for customer/product context.
 *
 * @since [version]
 */
class LLMS_Table_Orders_Transactions extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table.
	 *
	 * @var string
	 */
	protected $id = 'orders_transactions';

	/**
	 * Is the Table Exportable?
	 *
	 * @var bool
	 */
	protected $is_exportable = true;

	/**
	 * If true, tfoot will add ajax pagination links.
	 *
	 * @var bool
	 */
	protected $is_paginated = true;

	/**
	 * Determine if the table is filterable.
	 *
	 * @var bool
	 */
	protected $is_filterable = true;

	/**
	 * Determine if the table is searchable.
	 *
	 * @var bool
	 */
	protected $is_searchable = true;

	/**
	 * Results sort order.
	 *
	 * @var string
	 */
	protected $order = 'DESC';

	/**
	 * Field results are sorted by.
	 *
	 * @var string
	 */
	protected $orderby = 'date';

	/**
	 * Number of records to display per page.
	 *
	 * @var int
	 */
	protected $per_page = 25;

	/**
	 * Value of the field being filtered by.
	 *
	 * @var string
	 */
	protected $filter = '';

	/**
	 * Field results are filtered by.
	 *
	 * @var string
	 */
	protected $filterby = 'status';

	/**
	 * Retrieve data for a cell.
	 *
	 * @since [version]
	 *
	 * @param string           $key  The column id / key.
	 * @param LLMS_Transaction $data Transaction object.
	 * @return mixed
	 */
	protected function get_data( $key, $data ) {

		$order = $this->get_order_for_transaction( $data );
		$value = '';

		switch ( $key ) {

			case 'transaction_id':
				$txn_id      = $data->get( 'id' );
				$receipt_url = LLMS_Admin_Page_Orders::get_receipt_url( $txn_id );
				$value       = '#' . $txn_id;
				$value      .= '<div class="row-actions">';
				$value      .= '<span class="receipt"><a href="' . esc_url( $receipt_url ) . '" target="_blank">' . esc_html__( 'Receipt', 'lifterlms' ) . '</a></span>';
				if ( $order ) {
					$order_url = admin_url( 'post.php?post=' . $order->get( 'id' ) . '&action=edit' );
					$value    .= ' | <span class="view-order"><a href="' . esc_url( $order_url ) . '">' . esc_html__( 'View Order', 'lifterlms' ) . '</a></span>';
				}
				$value .= '</div>';
				break;

			case 'order':
				if ( $order ) {
					$order_id = $order->get( 'id' );
					$url      = esc_url( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
					$value    = '<a href="' . $url . '">#' . $order_id . '</a>';
				}
				break;

			case 'customer':
				if ( $order ) {
					$name  = $order->get_customer_name();
					$email = $order->get( 'billing_email' );
					$value = esc_html( $name );
					if ( $email ) {
						$value .= '<br><small>' . esc_html( $email ) . '</small>';
					}
				}
				break;

			case 'product':
				if ( $order ) {
					$product_id = $order->get( 'product_id' );
					if ( llms_get_post( $product_id ) ) {
						$value = '<a href="' . esc_url( get_edit_post_link( $product_id ) ) . '">' . esc_html( $order->get( 'product_title' ) ) . '</a>';
					} else {
						$value = esc_html__( '[DELETED]', 'lifterlms' ) . ' ' . esc_html( $order->get( 'product_title' ) );
					}
				}
				break;

			case 'amount':
				$amount = $data->get( 'amount' );
				$value  = wp_kses( llms_price( $amount ), LLMS_ALLOWED_HTML_PRICES );
				break;

			case 'status':
				$status      = $data->get( 'status' );
				$status_obj  = get_post_status_object( $status );
				$status_name = $status_obj ? $status_obj->label : $status;
				$value       = '<span class="llms-status ' . esc_attr( $status ) . '">' . esc_html( $status_name ) . '</span>';
				break;

			case 'payment_type':
				$type  = $data->get( 'payment_type' );
				$types = array(
					'single'    => __( 'One-time', 'lifterlms' ),
					'recurring' => __( 'Recurring', 'lifterlms' ),
					'trial'     => __( 'Trial', 'lifterlms' ),
				);
				$value = isset( $types[ $type ] ) ? $types[ $type ] : $type;
				break;

			case 'date':
				$value = $data->get_date( 'date', get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
				break;

			default:
				$value = '';
		}

		return $this->filter_get_data( $value, $key, $data );
	}

	/**
	 * Retrieve data for a cell in an export file.
	 *
	 * @since [version]
	 *
	 * @param string           $key  The column id / key.
	 * @param LLMS_Transaction $data Transaction object.
	 * @return mixed
	 */
	public function get_export_data( $key, $data ) {

		$order = $this->get_order_for_transaction( $data );

		switch ( $key ) {

			case 'transaction_id':
				return $data->get( 'id' );

			case 'order':
				return $order ? $order->get( 'id' ) : '';

			case 'customer':
				return $order ? $order->get_customer_name() : '';

			case 'customer_first_name':
				return $order ? $order->get( 'billing_first_name' ) : '';

			case 'customer_last_name':
				return $order ? $order->get( 'billing_last_name' ) : '';

			case 'customer_email':
				return $order ? $order->get( 'billing_email' ) : '';

			case 'billing_address_1':
				return $order ? $order->get( 'billing_address_1' ) : '';

			case 'billing_address_2':
				return $order ? $order->get( 'billing_address_2' ) : '';

			case 'billing_city':
				return $order ? $order->get( 'billing_city' ) : '';

			case 'billing_state':
				return $order ? $order->get( 'billing_state' ) : '';

			case 'billing_zip':
				return $order ? $order->get( 'billing_zip' ) : '';

			case 'billing_country':
				return $order ? $order->get( 'billing_country' ) : '';

			case 'gateway_transaction_id':
				return $data->get( 'gateway_transaction_id' );

			case 'amount':
				return $data->get( 'amount' );

			case 'status':
				$status_obj = get_post_status_object( $data->get( 'status' ) );
				return $status_obj ? $status_obj->label : $data->get( 'status' );

			case 'payment_type':
				$type  = $data->get( 'payment_type' );
				$types = array(
					'single'    => __( 'One-time', 'lifterlms' ),
					'recurring' => __( 'Recurring', 'lifterlms' ),
					'trial'     => __( 'Trial', 'lifterlms' ),
				);
				return isset( $types[ $type ] ) ? $types[ $type ] : $type;

			case 'product':
				return $order ? $order->get( 'product_title' ) : '';

			case 'date':
				return $data->get_date( 'date', 'Y-m-d H:i:s' );

			default:
				return $this->get_data( $key, $data );
		}
	}

	/**
	 * Get the search placeholder text.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_table_get_' . $this->id . '_search_placeholder', __( 'Search by order number, customer name, or email...', 'lifterlms' ) );
	}

	/**
	 * Get HTML for the filters displayed in the head of the table.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function output_table_filters_html() {
		$statuses = llms_get_transaction_statuses();
		$current  = $this->get_filter();
		?>
		<div class="llms-table-filters">
			<div class="llms-table-filter-wrap">
				<label class="screen-reader-text" for="<?php echo esc_attr( $this->id ); ?>-status-filter">
					<?php esc_html_e( 'Filter by Status', 'lifterlms' ); ?>
				</label>
				<select class="llms-table-filter" id="<?php echo esc_attr( $this->id ); ?>-status-filter" name="status">
					<option value=""><?php esc_html_e( 'All Statuses', 'lifterlms' ); ?></option>
					<?php foreach ( $statuses as $status ) : ?>
						<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $current, $status ); ?>>
							<?php
							$status_obj = get_post_status_object( $status );
							echo esc_html( $status_obj ? $status_obj->label : $status );
							?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php
	}

	/**
	 * Execute a query to retrieve results from the table.
	 *
	 * @since [version]
	 *
	 * @param array $args Array of query args.
	 * @return void
	 */
	public function get_results( $args = array() ) {

		if ( ! current_user_can( 'view_lifterlms_reports' ) ) {
			return;
		}

		$this->parse_args( $args );

		$query_args = array(
			'post_type'      => 'llms_transaction',
			'posts_per_page' => $this->get_per_page(),
			'paged'          => $this->get_current_page(),
			'order'          => $this->get_order(),
			'post_status'    => 'any',
			'meta_query'     => array(),
		);

		// Map the sortable column to valid WP_Query ordering arguments.
		switch ( $this->get_orderby() ) {
			case 'transaction_id':
				$query_args['orderby'] = 'ID';
				break;
			case 'amount':
				$query_args['orderby']  = 'meta_value_num';
				$query_args['meta_key'] = '_llms_amount';
				break;
			case 'date':
			default:
				$query_args['orderby'] = 'date';
				break;
		}

		// Filter by transaction status.
		if ( 'status' === $this->get_filterby() && '' !== $this->get_filter() ) {
			$query_args['post_status'] = $this->get_filter();
		}

		// Search handling.
		$search = $this->get_search();
		if ( $search ) {
			$order_ids = $this->search_orders( $search );
			if ( ! empty( $order_ids ) ) {
				$query_args['meta_query'][] = array(
					'key'     => '_llms_order_id',
					'value'   => $order_ids,
					'compare' => 'IN',
				);
			} else {
				// No matching orders found, return empty.
				$this->tbody_data = array();
				return;
			}
		}

		$query = new WP_Query( $query_args );

		$this->max_pages    = $query->max_num_pages;
		$this->is_last_page = ( $query->max_num_pages <= $this->get_current_page() );

		$transactions = array();
		foreach ( $query->posts as $post ) {
			$txn = llms_get_post( $post );
			if ( $txn instanceof LLMS_Transaction ) {
				$transactions[] = $txn;
			}
		}

		$this->tbody_data = $transactions;
	}

	/**
	 * Search orders by number or customer name/email.
	 *
	 * @since [version]
	 *
	 * @param string $term Search term.
	 * @return int[] Array of matching order IDs.
	 */
	private function search_orders( $term ) {

		// Numeric search: treat as order ID.
		if ( is_numeric( $term ) ) {
			$order_id = absint( $term );
			if ( 'llms_order' === get_post_type( $order_id ) ) {
				return array( $order_id );
			}
			return array();
		}

		// Search users by name/email.
		$user_query = new WP_User_Query(
			array(
				'search'         => '*' . esc_attr( $term ) . '*',
				'search_columns' => array( 'user_login', 'user_email', 'user_nicename', 'display_name' ),
				'fields'         => 'ID',
			)
		);

		$user_query2 = new WP_User_Query(
			array(
				'fields'     => 'ID',
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'     => 'first_name',
						'value'   => $term,
						'compare' => 'LIKE',
					),
					array(
						'key'     => 'last_name',
						'value'   => $term,
						'compare' => 'LIKE',
					),
				),
			)
		);

		$user_ids = wp_parse_id_list(
			array_merge(
				(array) $user_query->get_results(),
				(array) $user_query2->get_results()
			)
		);

		if ( empty( $user_ids ) ) {
			return array();
		}

		// Find orders belonging to these users.
		$order_query = new WP_Query(
			array(
				'post_type'      => 'llms_order',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'post_status'    => 'any',
				'meta_query'     => array(
					array(
						'key'     => '_llms_user_id',
						'value'   => $user_ids,
						'compare' => 'IN',
					),
				),
			)
		);

		return $order_query->posts;
	}

	/**
	 * Get the LLMS_Order for a given transaction (with basic caching).
	 *
	 * @since [version]
	 *
	 * @param LLMS_Transaction $txn Transaction object.
	 * @return LLMS_Order|false
	 */
	private function get_order_for_transaction( $txn ) {

		static $cache = array();

		$order_id = $txn->get( 'order_id' );
		if ( ! $order_id ) {
			return false;
		}

		if ( ! isset( $cache[ $order_id ] ) ) {
			$order              = llms_get_post( $order_id );
			$cache[ $order_id ] = ( $order instanceof LLMS_Order ) ? $order : false;
		}

		return $cache[ $order_id ];
	}

	/**
	 * Parse arguments passed to get_results().
	 *
	 * @since [version]
	 *
	 * @param array $args Array of arguments.
	 * @return void
	 */
	protected function parse_args( $args = array() ) {

		if ( ! $args ) {
			$args = $this->get_args();
		}

		$args = $this->clean_args( $args );

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$this->order    = isset( $args['order'] ) ? $args['order'] : $this->get_order();
		$this->orderby  = isset( $args['orderby'] ) ? $args['orderby'] : $this->get_orderby();
		$this->per_page = isset( $args['per_page'] ) ? $args['per_page'] : $this->get_per_page();

		if ( $this->is_filterable ) {
			$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();
			$this->filter   = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		}

		if ( isset( $args['search'] ) ) {
			$this->search = $args['search'];
		}
	}

	/**
	 * Define the structure of arguments used to pass to the get_results method.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function set_args() {
		return array(
			'per_page' => apply_filters( 'llms_table_' . $this->id . '_per_page', $this->per_page ),
		);
	}

	/**
	 * Define the structure of the table.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function set_columns() {
		return array(
			'transaction_id'         => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Transaction', 'lifterlms' ),
			),
			'order'                  => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Order', 'lifterlms' ),
			),
			'customer'               => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Customer', 'lifterlms' ),
			),
			'customer_first_name'    => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'First Name', 'lifterlms' ),
			),
			'customer_last_name'     => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Last Name', 'lifterlms' ),
			),
			'customer_email'         => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Email', 'lifterlms' ),
			),
			'product'                => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Product', 'lifterlms' ),
			),
			'amount'                 => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Amount', 'lifterlms' ),
			),
			'status'                 => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Status', 'lifterlms' ),
			),
			'payment_type'           => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Payment Type', 'lifterlms' ),
			),
			'date'                   => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Date', 'lifterlms' ),
			),
			'gateway_transaction_id' => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Gateway Transaction ID', 'lifterlms' ),
			),
			'billing_address_1'      => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Address 1', 'lifterlms' ),
			),
			'billing_address_2'      => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Address 2', 'lifterlms' ),
			),
			'billing_city'           => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing City', 'lifterlms' ),
			),
			'billing_state'          => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing State', 'lifterlms' ),
			),
			'billing_zip'            => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Zip', 'lifterlms' ),
			),
			'billing_country'        => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Country', 'lifterlms' ),
			),
		);
	}

	/**
	 * Set the table's title.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_title() {
		return __( 'Orders & Transactions', 'lifterlms' );
	}
}
