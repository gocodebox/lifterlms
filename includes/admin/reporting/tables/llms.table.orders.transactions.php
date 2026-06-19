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
	 * Object cache group for this table's cached aggregate queries.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'llms_orders_transactions';

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
	 * Currently selected transaction status filter.
	 *
	 * @var string
	 */
	protected $status_filter = '';

	/**
	 * Currently selected month filter, formatted `YYYYMM`.
	 *
	 * @var string
	 */
	protected $date_filter = '';

	/**
	 * Currently selected coupon ID filter.
	 *
	 * @var int
	 */
	protected $coupon_filter = 0;

	/**
	 * Active custom sort mode for the `posts_clauses` filter ('product' or 'amount').
	 *
	 * @var string
	 */
	protected $sort_mode = '';

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

		// Rows can be either a transaction or a transaction-less order (free, trial, pending payment).
		if ( $data instanceof LLMS_Order ) {
			return $this->filter_get_data( $this->get_order_row_data( $key, $data ), $key, $data );
		}

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
					$order_url = $this->get_order_url( $order->get( 'id' ), $txn_id );
					$value    .= ' | <span class="view-order"><a href="' . esc_url( $order_url ) . '">' . esc_html__( 'View Order', 'lifterlms' ) . '</a></span>';
				}
				$value .= '</div>';
				break;

			case 'order':
				if ( $order ) {
					$value = $this->get_order_link( $order, $data->get( 'id' ) );
				}
				break;

			case 'customer':
				if ( $order ) {
					$value = $this->get_customer_html( $order );
				}
				break;

			case 'product':
				if ( $order ) {
					$value = $this->get_product_html( $order );
				}
				break;

			case 'amount':
				$amount = $data->get( 'amount' );
				$value  = wp_kses( llms_price( $amount ), LLMS_ALLOWED_HTML_PRICES );
				break;

			case 'status':
				$value = $this->get_status_html( $data->get( 'status' ) );
				break;

			case 'payment_type':
				$value = $this->get_payment_type_label( $data->get( 'payment_type' ) );
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
	 * Retrieve cell data for a transaction-less order row (free, trial, or pending-payment order).
	 *
	 * @since [version]
	 *
	 * @param string     $key   The column id / key.
	 * @param LLMS_Order $order Order object.
	 * @return string
	 */
	protected function get_order_row_data( $key, $order ) {

		switch ( $key ) {

			case 'transaction_id':
				$order_url = admin_url( 'post.php?post=' . $order->get( 'id' ) . '&action=edit' );
				$value     = '&ndash;';
				$value    .= '<div class="row-actions">';
				$value    .= '<span class="view-order"><a href="' . esc_url( $order_url ) . '">' . esc_html__( 'View Order', 'lifterlms' ) . '</a></span>';
				$value    .= '</div>';
				return $value;

			case 'order':
				return $this->get_order_link( $order );

			case 'customer':
				return $this->get_customer_html( $order );

			case 'product':
				return $this->get_product_html( $order );

			case 'amount':
				return wp_kses( $order->get_initial_price( array(), 'html' ), LLMS_ALLOWED_HTML_PRICES );

			case 'status':
				$txn_status = $this->map_order_status_to_transaction_status( $order->get( 'status' ) );
				if ( $txn_status ) {
					return $this->get_status_html( $txn_status );
				}
				return $this->get_status_html( $order->get( 'status' ), llms_get_order_status_name( $order->get( 'status' ) ) );

			case 'payment_type':
				return $this->get_order_payment_type_label( $order );

			case 'date':
				return $order->get_date( 'date', get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );

			default:
				return '';
		}
	}

	/**
	 * Build a linked order number.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Order $order Order object.
	 * @return string
	 */
	protected function get_order_link( $order, $txn_id = 0 ) {
		$order_id = $order->get( 'id' );
		$url      = esc_url( $this->get_order_url( $order_id, $txn_id ) );
		return '<a href="' . $url . '">#' . $order_id . '</a>';
	}

	/**
	 * Build the edit URL for an order.
	 *
	 * When a transaction ID is provided, it's appended so the order edit screen can
	 * surface a "you're viewing the parent order for transaction #x" note with a
	 * jump link to the transactions list.
	 *
	 * @since [version]
	 *
	 * @param int $order_id Order post ID.
	 * @param int $txn_id   Optional. Transaction post ID the link originated from.
	 * @return string
	 */
	protected function get_order_url( $order_id, $txn_id = 0 ) {
		$args = array(
			'post'   => $order_id,
			'action' => 'edit',
		);
		if ( $txn_id ) {
			$args['llms_txn_id'] = $txn_id;
		}
		return add_query_arg( $args, admin_url( 'post.php' ) );
	}

	/**
	 * Build the customer cell HTML: name linked to the user profile, email as a mailto link.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Order $order Order object.
	 * @return string
	 */
	protected function get_customer_html( $order ) {

		$name = $order->get_customer_name();

		// Link to the customer's user profile (and email) unless the order is
		// anonymized or has no associated WordPress user, mirroring the legacy orders table.
		if ( llms_parse_bool( $order->get( 'anonymized' ) ) || empty( llms_get_student( $order->get( 'user_id' ) ) ) ) {
			return esc_html( $name );
		}

		$edit_user_link = $order->get( 'user_id' ) ? get_edit_user_link( $order->get( 'user_id' ) ) : '';
		$value          = $edit_user_link ? '<a href="' . esc_url( $edit_user_link ) . '">' . esc_html( $name ) . '</a>' : esc_html( $name );
		$email          = $order->get( 'billing_email' );
		if ( $email ) {
			$value .= '<br><a href="' . esc_url( 'mailto:' . $email ) . '"><small>' . esc_html( $email ) . '</small></a>';
		}

		return $value;
	}

	/**
	 * Build the product cell HTML.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Order $order Order object.
	 * @return string
	 */
	protected function get_product_html( $order ) {
		$product_id = $order->get( 'product_id' );
		if ( llms_get_post( $product_id ) ) {
			return '<a href="' . esc_url( get_edit_post_link( $product_id ) ) . '">' . esc_html( $order->get( 'product_title' ) ) . '</a>';
		}
		return esc_html__( '[DELETED]', 'lifterlms' ) . ' ' . esc_html( $order->get( 'product_title' ) );
	}

	/**
	 * Build a status badge.
	 *
	 * @since [version]
	 *
	 * @param string $status Status slug (transaction or order post status).
	 * @param string $label  Optional. Pre-resolved label. Defaults to the registered post status label.
	 * @return string
	 */
	protected function get_status_html( $status, $label = '' ) {
		if ( ! $label ) {
			$status_obj = get_post_status_object( $status );
			$label      = $status_obj ? $status_obj->label : $status;
		}
		return '<span class="llms-status llms-size--large ' . esc_attr( $status ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Map the table's transaction statuses onto the equivalent order post statuses.
	 *
	 * The combined table presents a single, transaction-centric set of statuses for
	 * both row types: a transaction-less order is shown using the transaction status
	 * it corresponds to (e.g. a free/completed enrollment reads as "Succeeded", a
	 * never-paid order reads as "Pending"). Subscription lifecycle statuses (active,
	 * on-hold, pending cancellation, expired, etc.) intentionally live in the
	 * Subscriptions table -- a transaction itself is never "pending cancellation".
	 *
	 * @since [version]
	 *
	 * @return array Map of transaction status slug => array of equivalent order status slugs.
	 */
	protected function get_status_groups() {
		return array(
			'llms-txn-succeeded' => array( 'llms-completed', 'llms-active' ),
			'llms-txn-failed'    => array( 'llms-failed' ),
			'llms-txn-pending'   => array( 'llms-pending' ),
			'llms-txn-refunded'  => array( 'llms-refunded' ),
		);
	}

	/**
	 * Resolve the transaction status that an order status maps to in this table.
	 *
	 * @since [version]
	 *
	 * @param string $order_status Order post status slug.
	 * @return string The equivalent transaction status slug, or empty string if there's no mapping.
	 */
	protected function map_order_status_to_transaction_status( $order_status ) {
		foreach ( $this->get_status_groups() as $txn_status => $order_statuses ) {
			if ( in_array( $order_status, $order_statuses, true ) ) {
				return $txn_status;
			}
		}
		return '';
	}

	/**
	 * Get the human-readable payment type label for a transaction.
	 *
	 * @since [version]
	 *
	 * @param string $type Transaction payment type.
	 * @return string
	 */
	protected function get_payment_type_label( $type ) {
		$types = array(
			'single'    => __( 'One-time', 'lifterlms' ),
			'recurring' => __( 'Recurring', 'lifterlms' ),
			'trial'     => __( 'Trial', 'lifterlms' ),
		);
		return isset( $types[ $type ] ) ? $types[ $type ] : $type;
	}

	/**
	 * Derive a payment type label for a transaction-less order row.
	 *
	 * @since [version]
	 *
	 * @param LLMS_Order $order Order object.
	 * @return string
	 */
	protected function get_order_payment_type_label( $order ) {
		if ( $order->has_trial() ) {
			return __( 'Trial', 'lifterlms' );
		}
		if ( 0 >= (float) $order->get( 'total' ) ) {
			return __( 'Free', 'lifterlms' );
		}
		return $order->is_recurring() ? __( 'Recurring', 'lifterlms' ) : __( 'One-time', 'lifterlms' );
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

		// Transaction-less order row (free, trial, or pending-payment order).
		if ( $data instanceof LLMS_Order ) {
			return $this->get_order_row_export_data( $key, $data );
		}

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
				return $this->get_payment_type_label( $data->get( 'payment_type' ) );

			case 'product':
				return $order ? $order->get( 'product_title' ) : '';

			case 'date':
				return $data->get_date( 'date', 'Y-m-d H:i:s' );

			default:
				return $this->get_data( $key, $data );
		}
	}

	/**
	 * Retrieve export cell data for a transaction-less order row.
	 *
	 * @since [version]
	 *
	 * @param string     $key   The column id / key.
	 * @param LLMS_Order $order Order object.
	 * @return string
	 */
	protected function get_order_row_export_data( $key, $order ) {

		switch ( $key ) {

			case 'transaction_id':
				return '';

			case 'order':
				return $order->get( 'id' );

			case 'customer':
				return $order->get_customer_name();

			case 'customer_first_name':
				return $order->get( 'billing_first_name' );

			case 'customer_last_name':
				return $order->get( 'billing_last_name' );

			case 'customer_email':
				return $order->get( 'billing_email' );

			case 'billing_address_1':
			case 'billing_address_2':
			case 'billing_city':
			case 'billing_state':
			case 'billing_zip':
			case 'billing_country':
				return $order->get( $key );

			case 'gateway_transaction_id':
				return '';

			case 'amount':
				return $order->get_initial_price( array(), 'float' );

			case 'status':
				$txn_status = $this->map_order_status_to_transaction_status( $order->get( 'status' ) );
				if ( $txn_status ) {
					$status_obj = get_post_status_object( $txn_status );
					return $status_obj ? $status_obj->label : $txn_status;
				}
				return llms_get_order_status_name( $order->get( 'status' ) );

			case 'payment_type':
				return $this->get_order_payment_type_label( $order );

			case 'product':
				return $order->get( 'product_title' );

			case 'date':
				return $order->get_date( 'date', 'Y-m-d H:i:s' );

			default:
				return '';
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
		return apply_filters( 'llms_table_get_' . $this->id . '_search_placeholder', __( 'Search by order or transaction number, customer name, or email...', 'lifterlms' ) );
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
		?>
		<div class="llms-table-filters">
			<div class="llms-table-filter-wrap">
				<label class="screen-reader-text" for="<?php echo esc_attr( $this->id ); ?>-status-filter">
					<?php esc_html_e( 'Filter by Status', 'lifterlms' ); ?>
				</label>
				<select class="llms-table-filter" id="<?php echo esc_attr( $this->id ); ?>-status-filter" name="status">
					<option value=""><?php esc_html_e( 'All Statuses', 'lifterlms' ); ?></option>
					<?php foreach ( $statuses as $status ) : ?>
						<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $this->status_filter, $status ); ?>>
							<?php
							$status_obj = get_post_status_object( $status );
							echo esc_html( $status_obj ? $status_obj->label : $status );
							?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="llms-table-filter-wrap">
				<label class="screen-reader-text" for="<?php echo esc_attr( $this->id ); ?>-date-filter">
					<?php esc_html_e( 'Filter by Date', 'lifterlms' ); ?>
				</label>
				<select class="llms-table-filter" id="<?php echo esc_attr( $this->id ); ?>-date-filter" name="date">
					<option value=""><?php esc_html_e( 'All Dates', 'lifterlms' ); ?></option>
					<?php
					foreach ( $this->get_available_months() as $month ) :
						$value = sprintf( '%04d%02d', $month->year, $month->month );
						$label = date_i18n( 'F Y', mktime( 0, 0, 0, $month->month, 1, $month->year ) );
						?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $this->date_filter, $value ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="llms-table-filter-wrap">
				<label class="screen-reader-text" for="<?php echo esc_attr( $this->id ); ?>-coupon-filter">
					<?php esc_html_e( 'Filter by Coupon', 'lifterlms' ); ?>
				</label>
				<select class="llms-table-filter" id="<?php echo esc_attr( $this->id ); ?>-coupon-filter" name="coupon">
					<option value=""><?php esc_html_e( 'All Coupons', 'lifterlms' ); ?></option>
					<?php foreach ( $this->get_coupons() as $coupon ) : ?>
						<option value="<?php echo esc_attr( $coupon->ID ); ?>" <?php selected( $this->coupon_filter, $coupon->ID ); ?>>
							<?php echo esc_html( $coupon->post_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php
	}

	/**
	 * Retrieve coupons available to filter by.
	 *
	 * @since [version]
	 *
	 * @return WP_Post[]
	 */
	private function get_coupons() {
		return get_posts(
			array(
				'post_type'        => 'llms_coupon',
				'posts_per_page'   => -1,
				'post_status'      => array( 'publish', 'pending', 'draft', 'private' ),
				'orderby'          => 'title',
				'order'            => 'ASC',
				'suppress_filters' => false,
			)
		);
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

		// Query both transactions and orders. Orders that already have at least one
		// transaction are represented by their transaction rows, so they're excluded
		// below; orders with no transaction (free, trial, pending payment) appear as
		// their own row.
		$query_args = array(
			'post_type'      => array( 'llms_transaction', 'llms_order' ),
			'posts_per_page' => $this->get_per_page(),
			'paged'          => $this->get_current_page(),
			'order'          => $this->get_order(),
			'post_status'    => 'any',
		);

		// Map the sortable column to valid WP_Query ordering arguments. Product and
		// amount span both post types, so they're handled via `posts_clauses` below.
		$this->sort_mode = '';
		switch ( $this->get_orderby() ) {
			case 'product':
			case 'amount':
				$this->sort_mode = $this->get_orderby();
				break;
			case 'date':
			default:
				$query_args['orderby'] = 'date';
				break;
		}

		// Filter by status. Each transaction status also matches the equivalent order
		// statuses so transaction-less orders are included (e.g. "Succeeded" matches
		// completed/active orders, "Pending" matches never-paid orders).
		if ( '' !== $this->status_filter ) {
			$groups                    = $this->get_status_groups();
			$query_args['post_status'] = isset( $groups[ $this->status_filter ] )
				? array_merge( array( $this->status_filter ), $groups[ $this->status_filter ] )
				: $this->status_filter;
		}

		// Filter by month (YYYYMM).
		if ( $this->date_filter && preg_match( '/^(\d{4})(\d{2})$/', $this->date_filter, $matches ) ) {
			$query_args['date_query'] = array(
				array(
					'year'  => absint( $matches[1] ),
					'month' => absint( $matches[2] ),
				),
			);
		}

		$search = $this->get_search();

		if ( $search || $this->coupon_filter ) {

			// Resolve the search term to the post IDs (transactions and/or
			// transaction-less orders) that should appear.
			$search_post_ids = null;
			if ( $search ) {
				$search_post_ids = $this->get_search_post_ids( $search );
				if ( empty( $search_post_ids ) ) {
					$this->tbody_data = array();
					return;
				}
			}

			// Resolve the coupon filter to the same kind of post ID set.
			$coupon_post_ids = null;
			if ( $this->coupon_filter ) {
				$coupon_order_ids = $this->get_order_ids_for_coupon( $this->coupon_filter );
				if ( empty( $coupon_order_ids ) ) {
					$this->tbody_data = array();
					return;
				}
				$coupon_post_ids = array_merge(
					$this->get_transaction_ids_for_orders( $coupon_order_ids ),
					$this->get_orders_without_transactions( $coupon_order_ids )
				);
			}

			// Intersect when both constraints are present so they narrow the results.
			if ( ! is_null( $search_post_ids ) && ! is_null( $coupon_post_ids ) ) {
				$post_in = array_intersect( $search_post_ids, $coupon_post_ids );
			} else {
				$post_in = is_null( $search_post_ids ) ? $coupon_post_ids : $search_post_ids;
			}

			$post_in = array_map( 'absint', (array) $post_in );

			if ( empty( $post_in ) ) {
				$this->tbody_data = array();
				return;
			}

			$query_args['post__in'] = $post_in;

		} elseif ( $this->is_has_transaction_backfilled() ) {

			// Backfill complete: every order with a transaction carries the
			// `_llms_has_transaction` flag, so an indexed `NOT EXISTS` lookup excludes
			// them (transactions and transaction-less orders both pass) without
			// materializing a large `post__not_in` list.
			$query_args['meta_query'] = array(
				array(
					'key'     => '_llms_has_transaction',
					'compare' => 'NOT EXISTS',
				),
			);

		} else {

			// Backfill still pending: fall back to the explicit exclusion list so
			// not-yet-flagged legacy orders don't double-list as their own order row.
			$orders_with_txns = $this->get_order_ids_with_transactions();
			if ( ! empty( $orders_with_txns ) ) {
				$query_args['post__not_in'] = array_map( 'absint', $orders_with_txns );
			}
		}

		if ( $this->sort_mode ) {
			add_filter( 'posts_clauses', array( $this, 'mixed_orderby_clauses' ), 10, 2 );
		}

		$query = new WP_Query( $query_args );

		if ( $this->sort_mode ) {
			remove_filter( 'posts_clauses', array( $this, 'mixed_orderby_clauses' ), 10 );
		}

		$this->max_pages    = $query->max_num_pages;
		$this->is_last_page = ( $query->max_num_pages <= $this->get_current_page() );

		$rows = array();
		foreach ( $query->posts as $post ) {
			$obj = llms_get_post( $post );
			if ( $obj instanceof LLMS_Transaction || $obj instanceof LLMS_Order ) {
				$rows[] = $obj;
			}
		}

		$this->tbody_data = $rows;
	}

	/**
	 * Retrieve the IDs of all orders that have at least one transaction.
	 *
	 * @since [version]
	 *
	 * @return int[] Array of order IDs.
	 */
	private function get_order_ids_with_transactions() {

		global $wpdb;

		$cache_key = 'order_ids_with_transactions';
		$ids       = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( false === $ids ) {
			$ids = $wpdb->get_col(
				"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_llms_order_id'"
			);
			$ids = array_map( 'absint', (array) $ids );
			wp_cache_set( $cache_key, $ids, self::CACHE_GROUP, HOUR_IN_SECONDS );
		}

		return $ids;
	}

	/**
	 * Flush the table's cached aggregate queries.
	 *
	 * Called when an order or transaction is created, updated, or deleted so the
	 * report (which may sit behind a persistent object cache) doesn't show stale
	 * rows -- e.g. an order showing both as its own row and as a transaction row
	 * after its first payment is recorded.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function clear_cache() {
		wp_cache_delete( 'order_ids_with_transactions', self::CACHE_GROUP );
		wp_cache_delete( 'transaction_months', self::CACHE_GROUP );
	}

	/**
	 * Retrieve transaction IDs belonging to a set of orders.
	 *
	 * @since [version]
	 *
	 * @param int[] $order_ids Array of order IDs.
	 * @return int[] Array of transaction IDs.
	 */
	private function get_transaction_ids_for_orders( $order_ids ) {

		if ( empty( $order_ids ) ) {
			return array();
		}

		$query = new WP_Query(
			array(
				'post_type'      => 'llms_transaction',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'post_status'    => 'any',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'     => '_llms_order_id',
						'value'   => $order_ids,
						'compare' => 'IN',
					),
				),
			)
		);

		return $query->posts;
	}

	/**
	 * Resolve a search term to the post IDs (transactions and/or transaction-less
	 * orders) that should appear in the table.
	 *
	 * A numeric term is treated as a post ID and can match either a transaction
	 * (shows just that transaction) or an order (shows that order's transactions,
	 * plus the order itself when it has none). A text term matches customers and
	 * expands to their orders' rows.
	 *
	 * @since [version]
	 *
	 * @param string $term Search term.
	 * @return int[] Array of matching post IDs.
	 */
	private function get_search_post_ids( $term ) {

		if ( is_numeric( $term ) ) {

			$id   = absint( $term );
			$type = get_post_type( $id );

			if ( 'llms_transaction' === $type ) {
				return array( $id );
			}

			if ( 'llms_order' === $type ) {
				return array_merge(
					$this->get_transaction_ids_for_orders( array( $id ) ),
					$this->get_orders_without_transactions( array( $id ) )
				);
			}

			return array();
		}

		$order_ids = $this->search_orders( $term );
		if ( empty( $order_ids ) ) {
			return array();
		}

		return array_merge(
			$this->get_transaction_ids_for_orders( $order_ids ),
			$this->get_orders_without_transactions( $order_ids )
		);
	}

	/**
	 * Given a bounded set of order IDs, return those that have NO transaction.
	 *
	 * Used by the search/coupon paths to decide which matched orders should appear
	 * as their own (transaction-less) row. Detection is done directly against
	 * `_llms_order_id` on the bounded set rather than the global flag, so it stays
	 * correct regardless of `_llms_has_transaction` backfill state.
	 *
	 * @since [version]
	 *
	 * @param int[] $order_ids Bounded set of order IDs to test.
	 * @return int[] Subset of `$order_ids` that have no transaction.
	 */
	private function get_orders_without_transactions( $order_ids ) {

		$order_ids = array_values( array_filter( array_map( 'absint', (array) $order_ids ) ) );

		if ( empty( $order_ids ) ) {
			return array();
		}

		// Map the bounded set's transactions back to their parent order to determine
		// which of the matched orders actually have a transaction.
		$with_txns = array();
		foreach ( $this->get_transaction_ids_for_orders( $order_ids ) as $txn_id ) {
			$with_txns[] = absint( get_post_meta( $txn_id, '_llms_order_id', true ) );
		}

		return array_values( array_diff( $order_ids, $with_txns ) );
	}

	/**
	 * Whether the `_llms_has_transaction` backfill migration has completed.
	 *
	 * Gates the indexed `NOT EXISTS` query path. Until the backfill finishes, legacy
	 * orders may lack the flag and would double-list, so the explicit `post__not_in`
	 * fallback is used instead. New installs report the current db version and use the
	 * flag path immediately.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	private function is_has_transaction_backfilled() {

		static $backfilled = null;

		if ( null === $backfilled ) {
			$backfilled = version_compare( get_option( 'lifterlms_db_version' ), '10.1.0', '>=' );
		}

		return $backfilled;
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
	 * Modify the query clauses to sort the mixed transaction/order result set.
	 *
	 * Both the product title and the amount can live on either the row's own post
	 * (for order rows) or on the parent order referenced by the transaction's
	 * `_llms_order_id` meta (for transaction rows). A `COALESCE` over the row's own
	 * ID and that meta value resolves the correct order in both cases.
	 *
	 * @since [version]
	 *
	 * @param array    $clauses Array of SQL clauses.
	 * @param WP_Query $query   The WP_Query instance (passed by reference).
	 * @return array
	 */
	public function mixed_orderby_clauses( $clauses, $query ) {

		global $wpdb;

		$order = ( 'ASC' === strtoupper( $this->get_order() ) ) ? 'ASC' : 'DESC';

		// Resolve the effective order ID: the transaction's parent order, or the row itself when it is an order.
		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS llms_oid ON ( {$wpdb->posts}.ID = llms_oid.post_id AND llms_oid.meta_key = '_llms_order_id' )";

		if ( 'amount' === $this->sort_mode ) {
			$clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} AS llms_amt ON ( {$wpdb->posts}.ID = llms_amt.post_id AND llms_amt.meta_key = '_llms_amount' )";
			$clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} AS llms_tot ON ( COALESCE( llms_oid.meta_value, {$wpdb->posts}.ID ) = llms_tot.post_id AND llms_tot.meta_key = '_llms_total' )";
			$clauses['orderby'] = "CAST( COALESCE( llms_amt.meta_value, llms_tot.meta_value, 0 ) AS DECIMAL(20,2) ) {$order}";
		} else {
			$clauses['join']   .= " LEFT JOIN {$wpdb->postmeta} AS llms_pt ON ( COALESCE( llms_oid.meta_value, {$wpdb->posts}.ID ) = llms_pt.post_id AND llms_pt.meta_key = '_llms_product_title' )";
			$clauses['orderby'] = "llms_pt.meta_value {$order}";
		}

		return $clauses;
	}

	/**
	 * Retrieve order IDs associated with a given coupon.
	 *
	 * @since [version]
	 *
	 * @param int $coupon_id WP_Post ID of the coupon.
	 * @return int[] Array of matching order IDs.
	 */
	private function get_order_ids_for_coupon( $coupon_id ) {

		$query = new WP_Query(
			array(
				'post_type'      => 'llms_order',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'post_status'    => 'any',
				'no_found_rows'  => true,
				'meta_query'     => array(
					array(
						'key'   => '_llms_coupon_id',
						'value' => absint( $coupon_id ),
					),
				),
			)
		);

		return $query->posts;
	}

	/**
	 * Retrieve the distinct year/month combinations that have transactions.
	 *
	 * @since [version]
	 *
	 * @return object[] Array of objects with `year` and `month` properties.
	 */
	private function get_available_months() {

		global $wpdb;

		$cache_key = 'transaction_months';
		$months    = wp_cache_get( $cache_key, self::CACHE_GROUP );

		if ( false === $months ) {
			$months = $wpdb->get_results(
				"SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
				 FROM {$wpdb->posts}
				 WHERE post_type IN ( 'llms_transaction', 'llms_order' )
				   AND post_status NOT IN ( 'auto-draft', 'trash' )
				 ORDER BY post_date DESC"
			);
			wp_cache_set( $cache_key, $months, self::CACHE_GROUP, HOUR_IN_SECONDS );
		}

		return $months;
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

		if ( isset( $args['status'] ) ) {
			$this->status_filter = sanitize_text_field( $args['status'] );
		}

		if ( isset( $args['date'] ) ) {
			$this->date_filter = preg_replace( '/[^0-9]/', '', $args['date'] );
		}

		if ( isset( $args['coupon'] ) ) {
			$this->coupon_filter = absint( $args['coupon'] );
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
			'status'   => $this->status_filter,
			'date'     => $this->date_filter,
			'coupon'   => $this->coupon_filter,
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
				'sortable'   => false,
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
				'sortable'   => true,
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
