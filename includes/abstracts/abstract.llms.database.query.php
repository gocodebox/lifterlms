<?php
/**
 * Abstract Database Query
 *
 * @package LifterLMS/Classes/Abstracts
 *
 * @since 3.8.0
 * @version 3.34.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Database Query Class
 *
 * @since 3.8.0
 * @since 3.30.3 `is_last_page()` method returns `true` when no results are found.
 * @since 3.34.0 Sanitizes sort parameters.
 */
abstract class LLMS_Database_Query {

	/**
	 * Identify the extending query
	 *
	 * @var  string
	 */
	protected $id = 'database';

	/**
	 * Defines fields that can be sorted on via ORDER BY
	 *
	 * @var array
	 */
	protected $allowed_sort_fields = null;

	/**
	 * Arguments
	 * Original merged into defaults
	 *
	 * @var  array
	 */
	protected $arguments = array();

	/**
	 * Default arguments before merging with original
	 *
	 * @var  array
	 */
	protected $arguments_default = array();

	/**
	 * Original args before merging with defaults
	 *
	 * @var  array
	 */
	protected $arguments_original = array();

	/**
	 * Total number of results matching query parameters
	 *
	 * @var  integer
	 */
	public $found_results = 0;

	/**
	 * Maximum number of pages of results
	 * based off per_page & found_results
	 *
	 * @var  integer
	 */
	public $max_pages = 0;

	/**
	 * Number of results on the current page
	 *
	 * @var  integer
	 */
	public $number_results = 0;

	/**
	 * Array of query variables
	 *
	 * @var  array
	 */
	public $query_vars = array();

	/**
	 * Array of results retrieved by the query
	 *
	 * @var  array
	 */
	public $results = array();

	/**
	 * The raw SQL query
	 *
	 * @var  string
	 */
	protected $sql = '';

	/**
	 * Constructor
	 *
	 * @param    array $args  query arguments
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function __construct( $args = array() ) {

		$this->arguments_original = $args;
		$this->arguments_default  = $this->get_default_args();

		$this->setup_args();

		$this->query();

	}

	/**
	 * Escape and add quotes to a string, useful for array mapping when building queries
	 *
	 * @param    mixed $input  input data
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function escape_and_quote_string( $input ) {
		return "'" . esc_sql( $input ) . "'";
	}

	/**
	 * Retrieve a query variable with an optional fallback / default
	 *
	 * @param    string $key      variable key
	 * @param    mixed  $default  default value
	 * @return   mixed
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get( $key, $default = '' ) {

		if ( isset( $this->query_vars[ $key ] ) ) {
			return $this->query_vars[ $key ];
		}

		return $default;
	}

	/**
	 * Retrieve default arguments for a the query
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_default_args() {

		$args = array(
			'page'             => 1,
			'per_page'         => 25,
			'search'           => '',
			'sort'             => array(
				'id' => 'ASC',
			),
			'suppress_filters' => false,
		);

		if ( $this->get( 'suppress_filters' ) ) {
			return $args;
		}

		return apply_filters( 'llms_db_query_get_default_args', $args );

	}

	/**
	 * Get a string used as filter names unique to the extending query
	 *
	 * @param    string $filter  filter name
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_filter( $filter ) {
		return 'llms_' . $this->id . '_query_' . $filter;
	}

	/**
	 * Retrieve an array of results for the given query
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_results() {

		if ( $this->get( 'suppress_filters' ) ) {
			return $this->results;
		}

		return apply_filters( $this->get_filter( 'get_results' ), $this->results );

	}

	/**
	 * Get the number of results to skip for the query
	 * based on the current page and per_page vars
	 *
	 * @return   int
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function get_skip() {
		return absint( ( $this->get( 'page' ) - 1 ) * $this->get( 'per_page' ) );
	}

	/**
	 * Determine if the query has at least one result
	 *
	 * @return   bool
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function has_results() {
		return ( $this->number_results > 0 );
	}

	/**
	 * Determine if we're on the first page of results
	 *
	 * @return   boolean
	 * @since    3.8.0
	 * @version  3.14.0
	 */
	public function is_first_page() {
		return ( 1 === absint( $this->get( 'page' ) ) );
	}

	/**
	 * Determine if we're on the last page of results
	 *
	 * @since 3.8.0
	 * @since 3.30.3 Return true if there are no results.
	 *
	 * @return boolean
	 */
	public function is_last_page() {
		return ! $this->has_results() || ( absint( $this->get( 'page' ) ) === $this->max_pages );
	}

	/**
	 * Parse arguments needed for the query
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	abstract protected function parse_args();

	/**
	 * Prepare the SQL for the query
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	abstract protected function preprare_query();

	/**
	 * Execute a query
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function query() {

		global $wpdb;

		$this->sql = $this->preprare_query();
		if ( ! $this->get( 'suppress_filters' ) ) {
			$this->sql = apply_filters( $this->get_filter( 'prepare_query' ), $this->sql, $this );
		}

		$this->results        = $wpdb->get_results( $this->sql ); // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- SQL is prepared just not right here.
		$this->number_results = count( $this->results );

		$this->set_found_results();

	}

	/**
	 * Sanitize input to ensure an array of absints
	 *
	 * @param    mixed $ids  String/Int or array of strings/ints
	 * @return   array
	 * @since    3.15.0
	 * @version  3.24.0
	 */
	protected function sanitize_id_array( $ids = array() ) {

		if ( empty( $ids ) ) {
			$ids = array();
		}

		// allow numeric strings & ints to be passed instead of an array
		if ( ! is_array( $ids ) && is_numeric( $ids ) && $ids > 0 ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $key => &$id ) {
			$id = absint( $id ); // verify we have ints
			if ( $id <= 0 ) { // remove anything negative or 0
				unset( $ids[ $key ] );
			}
		}

		return $ids;

	}

	/**
	 * Removes any invalid sort fields before preparing a query.
	 *
	 * @since 3.34.0
	 *
	 * @return void
	 */
	protected function sanitize_sort() {

		if ( empty( $this->allowed_sort_fields ) ) {
			return;
		}

		foreach ( (array) $this->get( 'sort' ) as $orderby => $order ) {

			if ( ! in_array( $orderby, $this->allowed_sort_fields, true ) || ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {

				unset( $this->arguments['sort'][ $orderby ] );

			}
		}

	}

	/**
	 * Sets a query variable
	 *
	 * @param    string $key  variable key
	 * @param    mixed  $val  variable value
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function set( $key, $val ) {
		$this->query_vars[ $key ] = $val;
	}

	/**
	 * Set variables related to total number of results and pages possible
	 * with supplied arguments
	 *
	 * @return   void
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	protected function set_found_results() {

		global $wpdb;

		// if no results bail early b/c no reason to calculate anything
		if ( ! $this->number_results ) {
			return;
		}

		$this->found_results = absint( $wpdb->get_var( 'SELECT FOUND_ROWS()' ) );
		$this->max_pages     = absint( ceil( $this->found_results / $this->get( 'per_page' ) ) );

	}

	/**
	 * Setup arguments prior to a query
	 *
	 * @since 3.8.0
	 * @since 3.34.0 Sanitizes sort parameters.
	 *
	 * @return   void
	 */
	protected function setup_args() {

		$this->arguments = wp_parse_args( $this->arguments_original, $this->arguments_default );

		$this->parse_args();

		foreach ( $this->arguments as $arg => $val ) {

			$this->set( $arg, $val );

		}

		$this->sanitize_sort();

	}

	/**
	 * Retrieve the prepared SQL for the LIMIT clause
	 *
	 * @return   string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	protected function sql_limit() {

		global $wpdb;

		$sql = $wpdb->prepare( 'LIMIT %d, %d', $this->get_skip(), $this->get( 'per_page' ) );

		return apply_filters( $this->get_filter( 'limit' ), $sql, $this );
	}

	/**
	 * Retrieve the prepared SQL for the ORDER clause
	 *
	 * @since 3.8.0
	 * @since 3.34.0 Returns an empty string if no sort fields are available.
	 *
	 * @return string
	 */
	protected function sql_orderby() {

		$sql = '';

		$sort = $this->get( 'sort' );
		if ( $sort ) {

			$sql = 'ORDER BY';

			$comma = false;

			foreach ( $sort as $orderby => $order ) {
				$pre   = ( $comma ) ? ', ' : ' ';
				$sql  .= $pre . "{$orderby} {$order}";
				$comma = true;
			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		return apply_filters( $this->get_filter( 'orderby' ), $sql, $this );

	}

}
