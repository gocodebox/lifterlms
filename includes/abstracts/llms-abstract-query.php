<?php
/**
 * LLMS_Abstract_Query class file.
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Database Query abstract class.
 *
 * @since 6.0.0
 *
 * Query Arguments
 *
 * @param int $page             Results page number. Default: `1`.
 * @param int $per_page         Number of results to retrieve per page. Default: `10`.
 * @param int $search           Text search term or query. Default: `''`.
 * @param int $sort             Result order and orderby. Default: `array()`.
 * @param int $suppress_filters Whether or not to allow filter hooks to run. Default: `false`.
 * @param int $no_found_rows    Whether or not to disable the total number of found results for the query. Default: `false`.
 */
abstract class LLMS_Abstract_Query {

	/**
	 * Identify the extending query.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Defines fields that can be sorted on via ORDER BY.
	 *
	 * If this is not defined by extending classes, the sort data
	 * will not be validated.
	 *
	 * @var string[]|null
	 */
	protected $allowed_sort_fields = array();

	/**
	 * Combined arguments prior to sanitization.
	 *
	 * This is the final resulting arguments used to generate the query,
	 * the result of the default arguments merged into the submitted arguments.
	 *
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * Default arguments before merging with original.
	 *
	 * @var array
	 */
	protected $arguments_default = array();

	/**
	 * Original arguments before merging with defaults.
	 *
	 * @var array
	 */
	protected $arguments_original = array();

	/**
	 * Total number of results matching query parameters.
	 *
	 * @var integer
	 */
	protected $found_results = 0;

	/**
	 * Maximum number of pages of results based off per_page & found_results.
	 *
	 * @var integer
	 */
	protected $max_pages = 0;

	/**
	 * Number of results on the current page.
	 *
	 * @var integer
	 */
	protected $number_results = 0;

	/**
	 * The final query used to retrieve results.
	 *
	 * For a raw database query, this is the SQL passed to `$wpdb->get_results()`. For a WP_Posts query
	 * this is the array of query arguments passed into the `WP_Query`. Other extending queries
	 * may used this as they see fit.
	 *
	 * @var mixed
	 */
	protected $query = array();

	/**
	 * The parsed and sanitized arguments ultimately used by the query.
	 *
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Array of results retrieved by the query.
	 *
	 * @var array
	 */
	protected $results = array();

	/**
	 * Constructor.
	 *
	 * @since 6.0.0
	 *
	 * @param array $args Query arguments. When not provided the default arguments will be used.
	 * @return void
	 */
	public function __construct( $args = array() ) {

		$this->arguments_original = $args;
		$this->arguments_default  = $this->get_default_args();

		$this->setup_args();

		$this->query();

	}

	/**
	 * Set result counts and pagination properties.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	protected function count_results() {

		$this->number_results = count( (array) $this->results );

		// If we have results and found rounds isn't disabled.
		if ( $this->number_results && ! $this->get( 'no_found_rows' ) ) {
			$this->found_results = $this->found_results();
			$this->max_pages     = absint( ceil( $this->found_results / $this->get( 'per_page' ) ) );
		}

	}

	/**
	 * Determines the default arguments for the query.
	 *
	 * Extending classes can override or extend this method to customize the default query
	 * arguments for the query.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	protected function default_arguments() {

		return array(
			'page'             => 1,
			'per_page'         => 10,
			'search'           => '',
			'sort'             => array(),
			'suppress_filters' => false,
			'no_found_rows'    => false,
		);

	}

	/**
	 * Determines the total number of found results for the given query and returns it.
	 *
	 * @since 6.0.0
	 *
	 * @return int
	 */
	abstract protected function found_results();

	/**
	 * Retrieve a query variable with an optional fallback value when the value is not set.
	 *
	 * @since 3.8.0
	 * @since 6.0.0 Moved from `LLMS_Database_Query` and updated to use the null coalesce operator.
	 *
	 * @param string $key     Variable key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get( $key, $default = '' ) {
		return $this->query_vars[ $key ] ?? $default;
	}

	/**
	 * Get the final combined arguments used to generate the query.
	 *
	 * Retrieves the value of the protected `$arguments` variable.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	public function get_arguments() {
		return $this->arguments;
	}

	/**
	 * Get the default arguments used for the query.
	 *
	 * Retrieves the value of the protected `$arguments_default` variable.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	public function get_arguments_default() {
		return $this->arguments_default;
	}

	/**
	 * Get the original, uncleaned, arguments submitted to the query.
	 *
	 * Retrieves the value of the protected `$arguments_original` variable.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	public function get_arguments_original() {
		return $this->arguments_original;
	}

	/**
	 * Get the query.
	 *
	 * Retrieves the value of the protected `$query` variable.
	 *
	 * @since 6.0.0
	 *
	 * @return mixed
	 */
	public function get_query() {
		return $this->query;
	}

	/**
	 * Retrieve a list of fields that are allowed to be used for result sorting.
	 *
	 * @since 6.0.0
	 *
	 * @return string[]
	 */
	protected function get_allowed_sort_fields() {

		$allowed_fields = $this->allowed_sort_fields;

		if ( $this->get( 'suppress_filters' ) ) {
			return $allowed_fields;
		}

		/**
		 * Filters the allowed sort fields.
		 *
		 * The dynamic portion of this hook, `$this->id`, refers to ID of the extending
		 * query class.
		 *
		 * @since 6.0.0
		 *
		 * @param array $allowed_fields Default arguments.
		 */
		return apply_filters( "llms_{$this->id}_query_allowed_sort_fields", $allowed_fields );

	}

	/**
	 * Retrieve default arguments for the query.
	 *
	 * @since 3.8.0
	 * @since 4.5.1 Added new default arg `no_found_rows` set to false.
	 * @since 6.0.0 Moved from `LLMS_Database_Query` abstract.
	 *
	 * @return array
	 */
	protected function get_default_args() {

		if ( $this->get( 'suppress_filters' ) ) {
			return $this->default_arguments();
		}

		/**
		 * Filters the query default args.
		 * The dynamic part of the filter `$this->id` identifies the extending query.
		 *
		 * @since 3.8.0
		 *
		 * @param array $args Array of default arguments to set up the query with.
		 */
		return apply_filters( "llms_{$this->id}_query_get_default_args", $this->default_arguments() );

	}

	/**
	 * Get the total results found for the query.
	 *
	 * If the query was instantiated with `$no_found_rows=true` this will always
	 * return `0`.
	 *
	 * Retrieves the value of the protected property `$found_results`.
	 *
	 * @since 6.0.0
	 *
	 * @return int
	 */
	public function get_found_results() {
		return $this->found_results;
	}

	/**
	 * Get the total number of pages available for the given query.
	 *
	 * If the query was instantiated with `$no_found_rows=true` this will always
	 * return `0`.
	 *
	 * Retrieves the value of the protected property `$max_pages`.
	 *
	 * @since 6.0.0
	 *
	 * @return int
	 */
	public function get_max_pages() {
		return $this->max_pages;
	}

	/**
	 * Get the number of results on the current page.
	 *
	 * Retrieves the value of the protected property `$number_results`.
	 *
	 * @since 6.0.0
	 *
	 * @return int
	 */
	public function get_number_results() {
		return $this->number_results;
	}

	/**
	 * Retrieve an array of results for the given query.
	 *
	 * @since 3.8.0
	 * @since 4.5.1 Drop use of `this->get_filter('get_results')` in favor of `"llms_{$this->id}_query_get_results"`.
	 * @since 6.0.0 Moved from `LLMS_Database_Query` abstract.
	 *
	 * @return array
	 */
	public function get_results() {

		if ( $this->get( 'suppress_filters' ) ) {
			return $this->results;
		}

		/**
		 * Filters the query results.
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
	 * Determine if the query has at least one result.
	 *
	 * @since 3.16.0
	 * @since 6.0.0 Moved from `LLMS_Database_Query` abstract.
	 *
	 * @return bool
	 */
	public function has_results() {
		return $this->number_results > 0;
	}

	/**
	 * Determine if we're on the first page of results.
	 *
	 * @since 3.8.0
	 * @since 3.14.0 Unknown.
	 * @since 6.0.0 Moved from `LLMS_Database_Query` abstract.
	 *
	 * @return boolean
	 */
	public function is_first_page() {
		return 1 === absint( $this->get( 'page' ) );
	}

	/**
	 * Determine if we're on the last page of results.
	 *
	 * @since 3.8.0
	 * @since 3.30.3 Return true if there are no results.
	 * @since 6.0.0 Moved from `LLMS_Database_Query` abstract.
	 *
	 * @return boolean
	 */
	public function is_last_page() {
		return ! $this->has_results() || ( absint( $this->get( 'page' ) ) === $this->max_pages );
	}

	/**
	 * Parse arguments needed for the query.
	 *
	 * @since 3.8.0
	 *
	 * @return void
	 */
	abstract protected function parse_args();

	/**
	 * Perform the query and return the results.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	abstract protected function perform_query();

	/**
	 * Prepare the query.
	 *
	 * Should return the query which will be used by `query()`.
	 *
	 * @since 6.0.0
	 *
	 * @return mixed
	 */
	abstract protected function prepare_query();

	/**
	 * Execute a query.
	 *
	 * @since 3.8.0
	 * @since 4.5.1 Drop use of `$this->get_filter('prepare_query')` in favor of `"llms_{$this->id}_query_prepare_query"`.
	 * @since 6.0.0 Moved from `LLMS_Database_Query` abstract.
	 *
	 * @return void
	 */
	public function query() {

		$this->query = $this->prepare_query();
		if ( ! $this->get( 'suppress_filters' ) ) {
			/**
			 * Filters the query SQL.
			 *
			 * The dynamic part of the filter `$this->id` identifies the extending query.
			 *
			 * @since 3.8.0
			 *
			 * @param string              $sql      The SQL query.
			 * @param LLMS_Database_Query $db_query The LLMS_Database_Query instance.
			 */
			$this->query = apply_filters( "llms_{$this->id}_query_prepare_query", $this->query, $this );
		}

		$this->results = $this->perform_query();

		$this->count_results();

	}

	/**
	 * Sanitize input to ensure an array of absolute integers.
	 *
	 * @since 3.15.0
	 * @since 3.24.0 Unknown.
	 * @since 6.0.0 Moved from `LLMS_Database_Query` abstract.
	 *
	 * @param string|int|array $ids String/Int or array of strings/ints.
	 * @return array
	 */
	protected function sanitize_id_array( $ids = array() ) {

		if ( empty( $ids ) ) {
			return array();
		}

		// Allow numeric strings & ints to be passed instead of an array.
		$ids = ! is_array( $ids ) ? array( $ids ) : $ids;

		// Force positive ints.
		$ids = array_map( 'absint', $ids );

		// Remove empty values.
		return array_values( array_filter( $ids ) );

	}

	/**
	 * Removes any invalid sort fields before preparing a query.
	 *
	 * @since 3.34.0
	 * @since 6.0.0 Moved from `LLMS_Database_Query`.
	 *              Use `get_allowed_sort_fields()`.
	 *
	 * @return void
	 */
	protected function sanitize_sort( $sort ) {

		$allowed_fields = $this->get_allowed_sort_fields();

		if ( empty( $allowed_fields ) ) {
			return $sort;
		}

		foreach ( (array) $sort as $orderby => $order ) {
			if ( ! in_array( $orderby, $allowed_fields, true ) || ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
				unset( $sort[ $orderby ] );
			}
		}

		return $sort;

	}

	/**
	 * Sets a query variable.
	 *
	 * @since 3.8.0
	 * @since 6.0.0 Moved from `LLMS_Database_Query` abstract.
	 *
	 * @param string $key Variable key.
	 * @param mixed  $val Variable value.
	 * @return void
	 */
	public function set( $key, $val ) {
		$this->query_vars[ $key ] = $val;
	}

	/**
	 * Setup arguments prior to a query.
	 *
	 * @since 3.8.0
	 * @since 3.34.0 Sanitizes sort parameters.
	 * @since 4.5.1 Added filter `"llms_{$this->id}_query_parse_args"`.
	 * @since 6.0.0 Moved from `LLMS_Database_Query` abstract.
	 *
	 * @return void
	 */
	protected function setup_args() {

		$this->arguments = wp_parse_args( $this->arguments_original, $this->arguments_default );

		$this->parse_args();

		if ( ! $this->get( 'suppress_filters' ) ) {
			/**
			 * Filters the parsed query arguments.
			 *
			 * The dynamic part of the filter `$this->id` identifies the extending query.
			 *
			 * @since 4.5.1
			 *
			 * @param array               $ars           The query parse arguments.
			 * @param LLMS_Database_Query $db_query      The LLMS_Database_Query instance.
			 * @param array               $original_args Original arguments before merging with defaults.
			 * @param array               $default_args  Default arguments before merging with original.
			 */
			$this->arguments = apply_filters( "llms_{$this->id}_query_parse_args", $this->arguments, $this, $this->arguments_original, $this->arguments_default );
		}

		foreach ( $this->arguments as $arg => $val ) {

			$val = 'sort' === $arg ? $this->sanitize_sort( $this->arguments['sort'] ) : $val;
			$this->set( $arg, $val );

		}

	}

}
