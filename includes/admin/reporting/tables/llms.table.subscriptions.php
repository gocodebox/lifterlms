<?php
/**
 * Subscriptions Reporting Table
 *
 * @package LifterLMS/Admin/Reporting/Tables/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Subscriptions class.
 *
 * Displays recurring orders (subscriptions) in an LLMS Admin Table
 * with lifetime revenue totals.
 *
 * @since [version]
 */
class LLMS_Table_Subscriptions extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table.
	 *
	 * @var string
	 */
	protected $id = 'subscriptions';

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
	 * @param string     $key  The column id / key.
	 * @param LLMS_Order $data Order object.
	 * @return mixed
	 */
	protected function get_data( $key, $data ) {

		$value = '';

		switch ( $key ) {

			case 'order':
				$order_id = $data->get( 'id' );
				$url      = esc_url( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) );
				$value    = '<a href="' . $url . '">#' . $order_id . '</a>';
				break;

			case 'customer':
				$value = $this->get_customer_html( $data );
				break;

			case 'product':
				$product_id = $data->get( 'product_id' );
				if ( llms_get_post( $product_id ) ) {
					$value = '<a href="' . esc_url( get_edit_post_link( $product_id ) ) . '">' . esc_html( $data->get( 'product_title' ) ) . '</a>';
				} else {
					$value = esc_html__( '[DELETED]', 'lifterlms' ) . ' ' . esc_html( $data->get( 'product_title' ) );
				}
				break;

			case 'status':
				$status = $data->get( 'status' );
				$value  = '<span class="llms-status llms-size--large ' . esc_attr( $status ) . '">' . esc_html( llms_get_order_status_name( $status ) ) . '</span>';
				break;

			case 'plan':
				$frequency = $data->get( 'billing_frequency' );
				$period    = $data->get( 'billing_period' );
				$length    = $data->get( 'billing_length' );
				$price     = $data->get_price( 'total' );

				$value = $price . ' / ';
				if ( $frequency > 1 ) {
					$value .= $frequency . ' ';
				}
				$value .= $period;
				if ( $length > 0 ) {
					/* translators: %d: billing length (number of payments) */
					$value .= ' ' . sprintf( __( '(%d payments)', 'lifterlms' ), $length );
				}
				break;

			case 'revenue':
				$grosse = $data->get_revenue( 'grosse' );
				$net    = $data->get_revenue( 'net' );

				if ( $grosse !== $net ) {
					$value = '<del>' . wp_kses( llms_price( $grosse ), LLMS_ALLOWED_HTML_PRICES ) . '</del> ';
				}
				$value .= wp_kses( llms_price( $net ), LLMS_ALLOWED_HTML_PRICES );
				break;

			case 'next_payment':
				$next = $data->get_next_payment_due_date( get_option( 'date_format' ) );
				if ( is_wp_error( $next ) ) {
					$value = '&ndash;';
				} else {
					$value = $next;
				}
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
	 * Retrieve data for a cell in an export file.
	 *
	 * @since [version]
	 *
	 * @param string     $key  The column id / key.
	 * @param LLMS_Order $data Order object.
	 * @return mixed
	 */
	public function get_export_data( $key, $data ) {

		switch ( $key ) {

			case 'order':
				return $data->get( 'id' );

			case 'customer':
				return $data->get_customer_name();

			case 'customer_first_name':
				return $data->get( 'billing_first_name' );

			case 'customer_last_name':
				return $data->get( 'billing_last_name' );

			case 'customer_email':
				return $data->get( 'billing_email' );

			case 'billing_address_1':
				return $data->get( 'billing_address_1' );

			case 'billing_address_2':
				return $data->get( 'billing_address_2' );

			case 'billing_city':
				return $data->get( 'billing_city' );

			case 'billing_state':
				return $data->get( 'billing_state' );

			case 'billing_zip':
				return $data->get( 'billing_zip' );

			case 'billing_country':
				return $data->get( 'billing_country' );

			case 'status':
				return llms_get_order_status_name( $data->get( 'status' ) );

			case 'revenue':
				return $data->get_revenue( 'net' );

			case 'next_payment':
				$next = $data->get_next_payment_due_date( 'Y-m-d H:i:s' );
				return is_wp_error( $next ) ? '' : $next;

			case 'date':
				return $data->get_date( 'date', 'Y-m-d H:i:s' );

			case 'billing_frequency':
				return $data->get( 'billing_frequency' );

			case 'billing_period':
				return $data->get( 'billing_period' );

			case 'billing_length':
				return $data->get( 'billing_length' );

			case 'trial_offer':
				return $data->has_trial() ? __( 'Yes', 'lifterlms' ) : __( 'No', 'lifterlms' );

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
		$statuses = llms_get_order_statuses( 'recurring' );
		$current  = $this->get_filter();
		?>
		<div class="llms-table-filters">
			<div class="llms-table-filter-wrap">
				<label class="screen-reader-text" for="<?php echo esc_attr( $this->id ); ?>-status-filter">
					<?php esc_html_e( 'Filter by Status', 'lifterlms' ); ?>
				</label>
				<select class="llms-table-filter" id="<?php echo esc_attr( $this->id ); ?>-status-filter" name="status">
					<option value=""><?php esc_html_e( 'All Statuses', 'lifterlms' ); ?></option>
					<?php foreach ( $statuses as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>>
							<?php echo esc_html( $label ); ?>
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
			'post_type'      => 'llms_order',
			'posts_per_page' => $this->get_per_page(),
			'paged'          => $this->get_current_page(),
			'order'          => $this->get_order(),
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'   => '_llms_order_type',
					'value' => 'recurring',
				),
			),
		);

		// Map the sortable column to valid WP_Query ordering arguments.
		switch ( $this->get_orderby() ) {
			case 'order':
				$query_args['orderby'] = 'ID';
				break;
			case 'product':
				$query_args['orderby']  = 'meta_value';
				$query_args['meta_key'] = '_llms_product_title';
				break;
			case 'next_payment':
				$query_args['orderby']  = 'meta_value';
				$query_args['meta_key'] = '_llms_date_next_payment';
				break;
			case 'date':
			default:
				$query_args['orderby'] = 'date';
				break;
		}

		// Filter by order status.
		if ( 'status' === $this->get_filterby() && '' !== $this->get_filter() ) {
			$query_args['post_status'] = $this->get_filter();
		}

		// Search handling.
		$search = $this->get_search();
		if ( $search ) {
			if ( is_numeric( $search ) ) {
				$query_args['p'] = absint( $search );
			} else {
				$user_ids = $this->search_users( $search );
				if ( ! empty( $user_ids ) ) {
					$query_args['meta_query'][] = array(
						'key'     => '_llms_user_id',
						'value'   => $user_ids,
						'compare' => 'IN',
					);
				} else {
					$this->tbody_data = array();
					return;
				}
			}
		}

		$query = new WP_Query( $query_args );

		$this->max_pages    = $query->max_num_pages;
		$this->is_last_page = ( $query->max_num_pages <= $this->get_current_page() );

		$orders = array();
		foreach ( $query->posts as $post ) {
			$order = llms_get_post( $post );
			if ( $order instanceof LLMS_Order ) {
				$orders[] = $order;
			}
		}

		$this->tbody_data = $orders;
	}

	/**
	 * Search users by name or email.
	 *
	 * @since [version]
	 *
	 * @param string $term Search term.
	 * @return int[] Array of matching user IDs.
	 */
	private function search_users( $term ) {

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

		return wp_parse_id_list(
			array_merge(
				(array) $user_query->get_results(),
				(array) $user_query2->get_results()
			)
		);
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
			'order'               => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Order', 'lifterlms' ),
			),
			'customer'            => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Customer', 'lifterlms' ),
			),
			'customer_first_name' => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'First Name', 'lifterlms' ),
			),
			'customer_last_name'  => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Last Name', 'lifterlms' ),
			),
			'customer_email'      => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Email', 'lifterlms' ),
			),
			'product'             => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Product', 'lifterlms' ),
			),
			'status'              => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Status', 'lifterlms' ),
			),
			'plan'                => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Plan', 'lifterlms' ),
			),
			'revenue'             => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Revenue', 'lifterlms' ),
			),
			'next_payment'        => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Next Payment', 'lifterlms' ),
			),
			'date'                => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Date', 'lifterlms' ),
			),
			'billing_frequency'   => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Frequency', 'lifterlms' ),
			),
			'billing_period'      => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Period', 'lifterlms' ),
			),
			'billing_length'      => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Length', 'lifterlms' ),
			),
			'trial_offer'         => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Has Trial', 'lifterlms' ),
			),
			'billing_address_1'   => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Address 1', 'lifterlms' ),
			),
			'billing_address_2'   => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Address 2', 'lifterlms' ),
			),
			'billing_city'        => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing City', 'lifterlms' ),
			),
			'billing_state'       => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing State', 'lifterlms' ),
			),
			'billing_zip'         => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Billing Zip', 'lifterlms' ),
			),
			'billing_country'     => array(
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
		return __( 'Subscriptions', 'lifterlms' );
	}
}
