<?php
/**
 * Database Query Abstract
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.8.0
 * @version 4.5.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Database Query abstract class
 *
 * @since 3.8.0
 * @since 3.30.3 `is_last_page()` method returns `true` when no results are found.
 * @since 3.34.0 Sanitizes sort parameters.
 */
abstract class LLMS_Database_Query {

	/**
	 * Identify the extending query
	 *
	 * @var string
	 */
	protected $id = 'database';

	/**
	 * Defines fields that can be sorted on via ORDER BY
	 *
	 * @var array
	 */
	protected $allowed_sort_fields = null;

	/**
	 * Arguments: original merged into defaults
	 *
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * Default arguments before merging with original
	 *
	 * @var  array
	 */
	protected $arguments_default = array();

	/**
	 * Original arguments before merging with defaults
	 *
	 * @var array
	 */
	protected $arguments_original = array();

	/**
	 * Total number of results matching query parameters
	 *
	 * @var integer
	 */
	public $found_results = 0;

	/**
	 * Maximum number of pages of results based off per_page & found_results
	 *
	 * @var integer
	 */
	public $max_pages = 0;

	/**
	 * Number of results on the current page
	 *
	 * @var integer
	 */
	public $number_results = 0;

	/**
	 * Array of query variables
	 *
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Array of results retrieved by the query
	 *
	 * @var array
	 */
	public $results = array();

	/**
	 * The raw SQL query
	 *
	 * @var string
	 */
	protected $sql = '';

	/**
	 * Constructor
	 *
	 * @since 3.8.0
	 *
	 * @param array $args Optional. Query arguments. Default empty array.
	 *                    When not provided the default arguments will be used.
	 * @return void
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
	 * @since 3.8.0
	 *
	 * @param mixed $input Input data.
	 * @return string
	 */
	public function escape_and_quote_string( $input ) {
		return "'" . esc_sql( $input ) . "'";
	}

	/**
	 * Retrieve a query variable with an optional fallback / default
	 *
	 * @since 3.8.0
	 *
	 * @param string $key     Variable key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get( $key, $default = '' ) {

		if ( isset( $this->query_vars[ $key ] ) ) {
			return $this->query_vars[ $key ];
		}

		return $default;
	}

	/**
	 * Retrieve default arguments for the query
	 *
	 * @since 3.8.0
	 * @since 4.5.1 Added new default arg `no_found_rows` set to false.
	 *
	 * @return array
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
			'no_found_rows'    => false,
		);

		if ( $this->get( 'suppress_filters' ) ) {
			return $args;
		}

		/**
		 * Filters the query default args
		 *
		 * @since 3.8.0
		 *
		 * @param array $args Array of default arguments to set up the query with.
		 */
		return apply_filters( 'llms_db_query_get_default_args', $args );

	}

	/**
	 * Get a string used as filter names unique to the extending query
	 *
	 * @since 3.8.0
	 *
	 * @TODO Deprecate.
	 *
	 * @param string $filter Filter name.
	 * @return string
	 */
	protected function get_filter( $filter ) {
		return 'llms_' . $this->id . '_query_' . $filter;
	}

	/**
	 * Retrieve an array of results for the given query
	 *
	 * @since 3.8.0
	 * @since 4.5.1 Drop use of `this->get_filter('get_results')` in favor of `"llms_{$this->id}_query_get_results"`.
	 *
	 * @return array
	 */
	public function get_results() {

		if ( $this->get( 'suppress_filters' ) ) {
			return $this->results;
		}

		/**
		 * Filters the query results
		 *
		 * The dynamic part of the filter `$this->id` identifies the extending query.
		 *
		 * @since 3.8.0
		 *
		 * @param array $results Array of results retrieved by the query.
		 */
		return apply_filters( "llms_{$this->id}_query_get_results", $this->results );

	}

	/**
	 * Get the number of results to skip for the query based on the current page and per_page vars
	 *
	 * @since 3.8.0
	 *
	 * @return int
	 */
	protected function get_skip() {
		return absint( ( $this->get( 'page' ) - 1 ) * $this->get( 'per_page' ) );
	}

	/**
	 * Determine if the query has at least one result
	 *
	 * @since 3.16.0
	 *
	 * @return bool
	 */
	public function has_results() {
		return ( $this->number_results > 0 );
	}

	/**
	 * Determine if we're on the first page of results
	 *
	 * @since 3.8.0
	 * @since 3.14.0 Unknown.
	 *
	 * @return boolean
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
	 * @since 3.8.0
	 *
	 * @return void
	 */
	abstract protected function parse_args();

	/**
	 * Prepare the SQL for the query
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	abstract protected function preprare_query();

	/**
	 * Execute a query
	 *
	 * @since 3.8.0
	 * @since 4.5.1 Drop use of `$this->get_filter('prepare_query')` in favor of `"llms_{$this->id}_query_prepare_query"`.
	 *
	 * @return void
	 */
	public function query() {

		global $wpdb;

		$this->sql = $this->preprare_query();
		if ( ! $this->get( 'suppress_filters' ) ) {
			/**
			 * Filters the query SQL
			 *
			 * The dynamic part of the filter `$this->id` identifies the extending query.
			 *
			 * @since 3.8.0
			 *
			 * @param string              $sql      The SQL query.
			 * @param LLMS_Database_Query $db_query The LLMS_Database_Query instance.
			 */
			$this->sql = apply_filters( "llms_{$this->id}_query_prepare_query", $this->sql, $this );
		}

		$this->results        = $wpdb->get_results( $this->sql ); // db call ok; no-cache ok.
		$this->number_results = count( $this->results );

		$this->set_found_results();

	}

	/**
	 * Sanitize input to ensure an array of absints
	 *
	 * @since 3.15.0
	 * @since 3.24.0 Unknown.
	 *
	 * @param mixed $ids String/Int or array of strings/ints.
	 * @return array
	 */
	protected function sanitize_id_array( $ids = array() ) {

		if ( empty( $ids ) ) {
			$ids = array();
		}

		// Allow numeric strings & ints to be passed instead of an array.
		if ( ! is_array( $ids ) && is_numeric( $ids ) && $ids > 0 ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $key => &$id ) {
			$id = absint( $id ); // Verify we have ints.
			if ( $id <= 0 ) { // Remove anything negative or 0.
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
	 * @since 3.8.0
	 *
	 * @param string $key Variable key.
	 * @param mixed  $val Variable value.
	 * @return void
	 */
	public function set( $key, $val ) {
		$this->query_vars[ $key ] = $val;
	}

	/**
	 * Set variables related to total number of results and pages possible with supplied arguments
	 *
	 * @since 3.8.0
	 * @since 4.5.1 Bail early if the query arg `no_found_rows` is true, b/c no reason to calculate anything.
	 *
	 * @return void
	 */
	protected function set_found_results() {

		global $wpdb;

		// If no results, or found rows not required, bail early b/c no reason to calculate anything.
		if ( ! $this->number_results || $this->get( 'no_found_rows' ) ) {
			return;
		}

		$this->found_results = absint( $wpdb->get_var( 'SELECT FOUND_ROWS()' ) ); // db call ok; no-cache ok.
		$this->max_pages     = absint( ceil( $this->found_results / $this->get( 'per_page' ) ) );

	}

	/**
	 * Setup arguments prior to a query
	 *
	 * @since 3.8.0
	 * @since 3.34.0 Sanitizes sort parameters.
	 * @since 4.5.1 Added filter `"llms_{$this->id}_query_parse_args"`.
	 *
	 * @return void
	 */
	protected function setup_args() {

		$this->arguments = wp_parse_args( $this->arguments_original, $this->arguments_default );

		$this->parse_args();

		if ( ! $this->get( 'suppress_filters' ) ) {
			/**
			 * Filters the parsed query arguments
			 *
			 * The dynamic part of the filter `$this->id` identifies the extending query.
			 *
			 * @since 4.5.1
			 *
			 * @param array               $ars           The query parse arguents.
			 * @param LLMS_Database_Query $db_query      The LLMS_Database_Query instance.
			 * @param array               $original_args Original arguments before merging with defaults.
			 * @param array               $default_args  Default arguments before merging with original.
			 */
			$this->arguments = apply_filters( "llms_{$this->id}_query_parse_args", $this->arguments, $this, $this->arguments_original, $this->arguments_default );
		}

		foreach ( $this->arguments as $arg => $val ) {

			$this->set( $arg, $val );

		}

		$this->sanitize_sort();

	}

	/**
	 * Retrieve the prepared SQL for the SELECT clause
	 *
	 * @since 4.5.1
	 *
	 * @param string $select_columns Optional. Columns to select. Default '*'.
	 * @return string
	 */
	protected function sql_select_columns( $select_columns = '*' ) {

		if ( ! $this->get( 'no_found_rows' ) ) {
			$select_columns = 'SQL_CALC_FOUND_ROWS ' . $select_columns;
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $select_columns;
		}

		/**
		 * Filters the query SELECT columns
		 *
		 * The dynamic part of the filter `$this->id` identifies the extending query.
		 *
		 * @since 4.5.1
		 *
		 * @param string              $select_columns Columns to select.
		 * @param LLMS_Database_Query $db_query       Instance of LLMS_Database_Query.
		 */
		return apply_filters( "llms_{$this->id}_query_select_columns", $select_columns, $this );

	}

	/**
	 * Retrieve the prepared SQL for the LIMIT clause
	 *
	 * @since 3.16.0
	 * @since 4.5.1 Drop use of `$this->get_filter('limit')` in favor of `"llms_{$this->id}_query_limit"`.
	 *
	 * @return string
	 */
	protected function sql_limit() {

		global $wpdb;

		$sql = $wpdb->prepare( 'LIMIT %d, %d', $this->get_skip(), $this->get( 'per_page' ) );

		/**
		 * Filters the query LIMIT clause
		 *
		 * The dynamic part of the filter `$this->id` identifies the extending query.
		 *
		 * @since 3.16.0
		 *
		 * @param string              $sql      The LIMIT clause of the query.
		 * @param LLMS_Database_Query $db_query The LLMS_Database_Query instance.
		 */
		return apply_filters( "llms_{$this->id}_query_limit", $sql, $this );
	}

	/**
	 * Retrieve the prepared SQL for the ORDER BY clause
	 *
	 * @since 3.8.0
	 * @since 3.34.0 Returns an empty string if no sort fields are available.
	 * @since 4.5.1 Drop use of `$this->get_filter('orderby')` in favor of `"llms_{$this->id}_query_orderby"`.
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

		/**
		 * Filters the query ORDER BY clause
		 *
		 * The dynamic part of the filter `$this->id` identifies the extending query.
		 *
		 * @since 3.8.0
		 *
		 * @param string              $sql      The ORDER BY clause of the query.
		 * @param LLMS_Database_Query $db_query The LLMS_Database_Query instance.
		 */
		return apply_filters( "llms_{$this->id}_query_orderby", $sql, $this );

	}

}
