<?php
/**
 * Analytics Widget Abstract
 *
 * @since 3.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Analytics Widget Abstract
 *
 * @since 3.0.0
 * @since 3.30.3 Define undefined properties.
 * @since 3.33.1 In `set_order_data_query()` always set $order_clause variable to avoid PHP notices
 * @since 3.35.0 Sanitize input data from reporting filters.
 */
abstract class LLMS_Analytics_Widget {

	/**
	 * @var array
	 * @since 3.0.0
	 */
	public $chart_data;

	/**
	 * @var bool
	 * @since 3.0.0
	 */
	public $charts = false;

	/**
	 * @var string
	 * @since 3.0.0
	 * @deprecated 3.0.0
	 */
	protected $date_end;

	/**
	 * @var string
	 * @since 3.0.0
	 * @deprecated 3.0.0
	 */
	protected $date_start;

	/**
	 * @var string
	 * @since 3.0.0
	 */
	public $message = '';

	/**
	 * @var string
	 * @since 3.0.0
	 * @deprecated 3.0.0
	 */
	protected $output;

	/**
	 * One of the wpdb constants: OBJECT, OBJECT_K, ARRAY_A, or ARRAY_N
	 *
	 * @var string
	 * @since 3.0.0
	 */
	protected $output_type;

	/**
	 * @var string
	 * @since 3.0.0
	 */
	protected $prepared_query;
	/**
	 * @var string
	 * @since 3.0.0
	 */
	protected $query;

	/**
	 * @var string
	 * @since 3.0.0
	 */
	protected $query_function;

	/**
	 * @var array
	 * @since 1.0.0
	 */
	protected $query_vars;

	/**
	 * @var int
	 * @since 3.0.0
	 */
	public $response;

	/**
	 * @var array
	 * @since 1.0.0
	 */
	public $results = array();

	/**
	 * @var bool
	 * @since 3.0.0
	 */
	public $success = false;

	abstract protected function format_response();
	abstract protected function set_query();
	protected function get_chart_data() {
		return array(
			'type'   => 'count',
			'header' => array(
				'id'    => '',
				'label' => '',
				'type'  => 'string',
			),
		);
	}

	public function __construct() {}

	protected function get_posted_dates() {

		$dates = llms_filter_input( INPUT_POST, 'dates', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY );
		return $dates ? $dates : '';

	}

	protected function get_posted_courses() {

		$courses = llms_filter_input( INPUT_POST, 'courses', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
		return $courses ? $courses : array();

	}

	protected function get_posted_memberships() {

		$memberships = llms_filter_input( INPUT_POST, 'memberships', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
		return $memberships ? $memberships : array();

	}

	protected function get_posted_posts() {
		return array_merge( $this->get_posted_courses(), $this->get_posted_memberships() );
	}

	protected function get_posted_students() {
		$students = llms_filter_input( INPUT_POST, 'students', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );
		return $students ? $students : array();

	}

	protected function get_prepared_query() {
		return $this->prepared_query;
	}

	protected function get_query() {
		return $this->query;
	}

	protected function get_query_vars() {
		return $this->query_vars;
	}

	protected function get_results() {
		return $this->results;
	}

	protected function format_date( $date, $type ) {

		switch ( $type ) {

			case 'start':
				$date .= ' 00:00:00';

				break;

			case 'end':
				$date .= ' 23:23:59';

				break;

		}

		return $date;

	}

	protected function is_error() {

		return ( $this->success ) ? false : true;

	}

	protected function set_order_data_query( $args = array() ) {

		extract(
			wp_parse_args(
				$args,
				array(
					'select'         => array( '*' ),
					'date_range'     => true, // whether or not to add a "where" for the posted date range
					'date_field'     => 'post_date',
					'query_function' => 'get_results', // query function to pass to $wpdb->query()
					'output_type'    => OBJECT,
					'joins'          => array(), // array of JOIN statements
					'statuses'       => array(), // array of order statuses to query
					'wheres'         => array(), // array of "WHERE" statements
					'order'          => 'ASC',
					'orderby'        => '',

				)
			)
		);

		$this->query_function = $query_function;
		$this->query_vars     = array();
		$this->output_type    = $output_type;

		global $wpdb;

		// setup student join & where clauses
		$students       = $this->get_posted_students();
		$students_join  = '';
		$students_where = '';
		if ( $students ) {
			$students_join   = "JOIN {$wpdb->postmeta} AS m1 ON orders.ID = m1.post_id";
			$students_where .= "AND m1.meta_key = '_llms_user_id'";
			$students_where .= ' AND m1.meta_value IN ( ' . implode( ', ', $students ) . ' )';
		}

		// setup post (product) joins & where clauses
		$posts          = $this->get_posted_posts();
		$products_join  = '';
		$products_where = '';
		if ( $posts ) {
			$products_join   = "JOIN {$wpdb->postmeta} AS m2 ON orders.ID = m2.post_id";
			$products_where .= "AND m2.meta_key = '_llms_product_id'";
			$products_where .= ' AND m2.meta_value IN ( ' . implode( ', ', $posts ) . ' )';
		}

		$order_dates = '';
		if ( $date_range ) {
			$dates              = $this->get_posted_dates();
			$order_dates        = "AND orders.{$date_field} BETWEEN CAST( %s AS DATETIME ) AND CAST( %s AS DATETIME )";
			$this->query_vars[] = $this->format_date( $dates['start'], 'start' );
			$this->query_vars[] = $this->format_date( $dates['end'], 'end' );
		}

		// setup post status conditions in the where clause
		$post_statuses = '';
		if ( $statuses ) {
			$post_statuses .= ' AND ( ';
			foreach ( $statuses as $i => $status ) {
				if ( $i > 0 ) {
					$post_statuses .= ' OR ';
				}
				$post_statuses     .= 'post_status = %s';
				$this->query_vars[] = $status;
			}
			$post_statuses .= ' )';
		}

		// setup the select clause
		$select_clause = '';
		foreach ( $select as $i => $s ) {
			if ( $i > 0 ) {
				$select_clause .= ', ';
			}
			$select_clause .= $s;
		}

		$joins_clause = '';
		foreach ( $joins as $join ) {
			$joins_clause .= $join . "\r\n";
		}

		$wheres_clause = '';
		foreach ( $wheres as $where ) {
			$wheres_clause .= $where . "\r\n";
		}

		$order_clause = '';
		if ( $order && $orderby ) {
			$order_clause = 'ORDER BY ' . $orderby . ' ' . $order;
		}

		$this->query = "SELECT {$select_clause}
						FROM {$wpdb->posts} AS orders
						{$students_join}
						{$products_join}
						{$joins_clause}
						WHERE orders.post_type = 'llms_order'
							{$order_dates}
							{$post_statuses}
							{$students_where}
							{$products_where}
							{$wheres_clause}
						{$order_clause}
						;";

	}

	protected function query() {

		global $wpdb;
		// no output options
		if ( in_array( $this->query_function, array( 'get_var', 'get_col' ) ) ) {
			$this->results = $wpdb->{$this->query_function}( $wpdb->prepare( $this->query, $this->query_vars ) ); // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- It is prepared.
		} else {
			$this->results = $wpdb->{$this->query_function}( $wpdb->prepare( $this->query, $this->query_vars ), $this->output_type ); // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- It is prepared.
		}

		$this->prepared_query = trim( str_replace( array( "\r", "\n", "\t", '  ' ), ' ', $wpdb->last_query ) );

		if ( ! $wpdb->last_error ) {

			$this->success = true;
			$this->message = 'success';

		} else {

			$this->message = $wpdb->last_error;

		}

	}

	public function output() {

		$this->set_query();
		$this->query();
		$this->response = $this->format_response();

		if ( $this->charts ) {
			$this->chart_data = $this->get_chart_data();
		}

		header( 'Content-Type: application/json' );
		echo json_encode( $this );
		wp_die();

	}


}
