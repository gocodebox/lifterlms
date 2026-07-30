<?php
/**
 * Customers admin table
 *
 * @package LifterLMS/Admin/Reporting/Tables/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Customers class
 *
 * @since [version]
 */
class LLMS_Table_Customers extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table.
	 *
	 * @var string
	 */
	protected $id = 'customers';

	/**
	 * Customer segment slug.
	 *
	 * @var string
	 */
	protected $segment = 'all';

	/**
	 * Is the Table Exportable?
	 *
	 * @var boolean
	 */
	protected $is_exportable = true;

	/**
	 * If true, tfoot will add ajax pagination links.
	 *
	 * @var boolean
	 */
	protected $is_paginated = true;

	/**
	 * Determine if the table is searchable.
	 *
	 * @var boolean
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
	protected $orderby = 'last_order';

	/**
	 * Number of records to display per page.
	 *
	 * @var int
	 */
	protected $per_page = 25;

	/**
	 * Retrieve data for a cell.
	 *
	 * @since [version]
	 *
	 * @param string $key      The column id / key.
	 * @param object $customer Customer row object from LLMS_Customer_Query.
	 * @return mixed
	 */
	public function get_data( $key, $customer ) {

		switch ( $key ) {

			case 'name':
				$first = $customer->first_name;
				$last  = $customer->last_name;
				if ( ! $first || ! $last ) {
					$value = $customer->display_name;
				} else {
					$value = $last . ', ' . $first;
				}
				$url    = llms_get_customers_admin_url( $customer->user_id );
				$avatar = get_avatar( $customer->user_id, 32 );
				$value  = '<a class="llms-customer-name" href="' . esc_url( $url ) . '">' . $avatar . ' ' . esc_html( $value ) . '</a>';
				break;

			case 'email':
				$value = '<a href="' . esc_url( 'mailto:' . $customer->user_email ) . '">' . esc_html( $customer->user_email ) . '</a>';
				break;

			case 'order_count':
				$value = absint( $customer->order_count );
				break;

			case 'ltv':
				$value = llms_price( $customer->ltv );
				break;

			case 'aov':
				$value = llms_price( $customer->aov );
				break;

			case 'last_order':
			case 'first_order':
			case 'registered':
				$raw   = $customer->{$key};
				$value = $raw ? date_i18n( get_option( 'date_format' ), strtotime( $raw ) ) : '&ndash;';
				break;

			case 'active_recurring_count':
				$value = absint( $customer->active_recurring_count );
				break;

			default:
				$value = $key;
		}

		return $this->filter_get_data( $value, $key, $customer );
	}

	/**
	 * Retrieve export cell data.
	 *
	 * @since [version]
	 *
	 * @param string $key      The column id / key.
	 * @param object $customer Customer row object.
	 * @return mixed
	 */
	public function get_export_data( $key, $customer ) {

		switch ( $key ) {

			case 'name':
				$first = $customer->first_name;
				$last  = $customer->last_name;
				$value = ( ! $first || ! $last ) ? $customer->display_name : $last . ', ' . $first;
				break;

			case 'email':
				$value = $customer->user_email;
				break;

			case 'ltv':
			case 'aov':
				$value = llms_price_raw( $customer->{$key} );
				break;

			case 'last_order':
			case 'first_order':
			case 'registered':
				$value = $customer->{$key} ? $customer->{$key} : '';
				break;

			case 'order_count':
			case 'active_recurring_count':
				$value = absint( $customer->{$key} );
				break;

			default:
				$value = $this->get_data( $key, $customer );
		}

		return $this->filter_get_data( $value, $key, $customer, 'export' );
	}

	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_table_search_form_placeholder() {
		/**
		 * Filters the customers table search placeholder.
		 *
		 * @since [version]
		 *
		 * @param string $placeholder Placeholder text.
		 */
		return apply_filters( 'llms_table_get_' . $this->id . '_search_placeholder', __( 'Search customers by name, email, or ID…', 'lifterlms' ) );
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

		if ( ! current_user_can( apply_filters( 'lifterlms_admin_order_access', 'manage_lifterlms' ) ) ) {
			return;
		}

		$this->parse_args( $args );

		$query = new LLMS_Customer_Query(
			array(
				'page'     => $this->get_current_page(),
				'per_page' => $this->get_per_page(),
				'search'   => $this->get_search(),
				'segment'  => $this->segment,
				'sort'     => $this->get_sort(),
			)
		);

		$this->max_pages    = $query->get_max_pages();
		$this->is_last_page = $query->is_last_page();
		$this->tbody_data   = $query->get_customers();
	}

	/**
	 * Setup the array of sort arguments.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	private function get_sort() {

		$sort = array();
		switch ( $this->get_orderby() ) {

			case 'name':
				$sort = array(
					'name'    => $this->get_order(),
					'user_id' => 'ASC',
				);
				break;

			case 'ltv':
			case 'order_count':
			case 'aov':
			case 'last_order':
			case 'first_order':
			case 'registered':
			case 'active_recurring_count':
				$sort = array(
					$this->get_orderby() => $this->get_order(),
					'user_id'            => 'DESC',
				);
				break;

			default:
				$sort = array(
					'last_order' => 'DESC',
					'user_id'    => 'DESC',
				);
		}

		return $sort;
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

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->get_order();
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->get_orderby();

		$this->per_page = isset( $args['per_page'] ) ? $args['per_page'] : $this->get_per_page();

		if ( isset( $args['search'] ) ) {
			$this->search = $args['search'];
		}

		if ( isset( $args['segment'] ) ) {
			$this->segment = sanitize_title( $args['segment'] );
		} else {
			$segment = llms_filter_input_sanitize_string( INPUT_GET, 'segment' );
			if ( $segment ) {
				$this->segment = sanitize_title( $segment );
			}
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
			'segment'  => $this->segment,
		);
	}

	/**
	 * Define the structure of the table.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function set_columns() {
		return array(
			'name'                   => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Customer', 'lifterlms' ),
			),
			'email'                  => array(
				'exportable' => true,
				'sortable'   => false,
				'title'      => __( 'Email', 'lifterlms' ),
			),
			'order_count'            => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Orders', 'lifterlms' ),
			),
			'ltv'                    => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'LTV', 'lifterlms' ),
			),
			'aov'                    => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'AOV', 'lifterlms' ),
			),
			'last_order'             => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Last order', 'lifterlms' ),
			),
			'first_order'            => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'First order', 'lifterlms' ),
			),
			'active_recurring_count' => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Active subscriptions', 'lifterlms' ),
			),
			'registered'             => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Registered', 'lifterlms' ),
			),
		);
	}

	/**
	 * Set table title.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function set_title() {
		return __( 'Customers', 'lifterlms' );
	}
}
