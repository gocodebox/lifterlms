<?php
/**
* Analytics Widget Abstract
*
* @since   3.0.0
* @version 3.0.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Analytics_Widget {

	public $charts = false;
	public $success = false;
	public $message = '';
	public $response;

	protected $date_start;
	protected $date_end;

	protected $output;

	protected $query;
	protected $query_vars;
	protected $query_function;
	protected $output_type;
	// protected $prepared_query;

	public $results = array();

	abstract protected function format_response();
	abstract protected function set_query();
	protected function get_chart_data() {
		return array(
			'type' => 'count',
			'header' => array(
				'id' => '',
				'label' => '',
				'type' => 'string',
			),
		);
	}

	public function __construct() {}

	// protected function get_date_range_difference() {
	// 	$dates = $this->get_posted_dates();
	// 	if ( $dates ) {
	// 		return strtotime( $dates['end'] ) - strtotime( $dates['start'] );
	// 	}
	// 	return 0;
	// }

	protected function get_posted_dates() {

		return ( isset( $_POST['dates'] ) ) ? $_POST['dates'] : '';

	}

	protected function get_posted_courses() {

		return ( isset( $_POST['courses'] ) ) ? $_POST['courses'] : array();

	}

	protected function get_posted_memberships() {

		return ( isset( $_POST['memberships'] ) ) ? $_POST['memberships'] : array();

	}

	protected function get_posted_posts() {
		return array_merge( $this->get_posted_courses(), $this->get_posted_memberships() );
	}

	protected function get_posted_students() {

		return ( isset( $_POST['students'] ) ) ? $_POST['students'] : array();

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

		extract( wp_parse_args( $args, array(

			'select' => array( '*' ),
			'date_range' => true, // whether or not to add a "where" for the posted date range
			'date_field' => 'post_date',
			'query_function' => 'get_results', // query function to pass to $wpdb->query()
			'output_type' => OBJECT,
			'joins' => array(), // array of JOIN statements
			'statuses' => array(), // array of order statuses to query
			'wheres' => array(), // array of "WHERE" statements
			'order' => 'ASC',
			'orderby' => '',

		) ) );

		$this->query_function = $query_function;
		$this->query_vars = array();
		$this->output_type = $output_type;

		global $wpdb;

		// setup student join & where clauses
		$students = $this->get_posted_students();
		$students_join = '';
		$students_where = '';
		if ( $students ) {
			$students_join = "JOIN {$wpdb->postmeta} AS m1 ON orders.ID = m1.post_id";
			$students_where .= "AND m1.meta_key = '_llms_user_id'";
			$students_where .= ' AND m1.meta_value IN ( ' . implode( ', ', $students ) . ' )';
		}

		// setup post (product) joins & where clauses
		$posts = $this->get_posted_posts();
		$products_join = '';
		$products_where = '';
		if ( $posts ) {
			$products_join = "JOIN {$wpdb->postmeta} AS m2 ON orders.ID = m2.post_id";
			$products_where .= "AND m2.meta_key = '_llms_product_id'";
			$products_where .= ' AND m2.meta_value IN ( ' . implode( ', ', $posts ) . ' )';
		}

		$order_dates = '';
		if ( $date_range ) {
			$dates = $this->get_posted_dates();
			$order_dates = "AND orders.{$date_field} BETWEEN CAST( %s AS DATETIME ) AND CAST( %s AS DATETIME )";
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
				$post_statuses .= 'post_status = %s';
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
			$this->results = $wpdb->{$this->query_function}( $wpdb->prepare( $this->query, $this->query_vars ) );
		} // End if().
		else {
			$this->results = $wpdb->{$this->query_function}( $wpdb->prepare( $this->query, $this->query_vars ), $this->output_type );
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
