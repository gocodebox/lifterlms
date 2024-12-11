<?php
/**
 * Database Query Abstract
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.8.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Database Query abstract class.
 *
 * @since 3.8.0
 * @since 3.30.3 `is_last_page()` method returns `true` when no results are found.
 * @since 3.34.0 Sanitizes sort parameters.
 */
abstract class LLMS_Database_Query extends LLMS_Abstract_Query {

	/**
	 * Identify the extending query.
	 *
	 * @var string
	 */
	protected $id = 'database';

	/**
	 * Retrieve query argument default values.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	protected function default_arguments() {

		return wp_parse_args(
			array(
				'per_page' => 25,
				'sort'     => array(
					'id' => 'ASC',
				),
			),
			parent::default_arguments()
		);
	}

	/**
	 * Escape and add quotes to a string, useful for array mapping when building queries.
	 *
	 * @since 3.8.0
	 * @since 6.0.0 Use {@see llms_esc_and_quote_str()}.
	 *
	 * @param mixed $input Input data.
	 * @return string
	 */
	public function escape_and_quote_string( $input ) {
		return llms_esc_and_quote_str( $input );
	}

	/**
	 * Retrieve default arguments for the query.
	 *
	 * @since 3.8.0
	 * @since 4.5.1 Added new default arg `no_found_rows` set to false.
	 * @since 6.0.0 Call parent method.
	 *
	 * @todo This should be removed in favor of the parent method only when the
	 *       `llms_db_query_get_default_args` hook is removed.
	 *
	 * @return array
	 */
	protected function get_default_args() {

		if ( $this->get( 'suppress_filters' ) ) {
			return $this->default_arguments();
		}

		// Get them from the parent with the new replacement filter.
		$args = parent::get_default_args();

		/**
		 * Filters the query default args.
		 *
		 * @since 3.8.0
		 * @deprecated 6.0.0 Filter `llms_db_query_get_default_args` is deprecated in favor of `llms_{$this->id}_query_get_default_args`.
		 *
		 * @param array $args Array of default arguments to set up the query with.
		 */
		return apply_filters_deprecated( 'llms_db_query_get_default_args', array( $args ), '6.0.0', "llms_{$this->id}_query_get_default_args" );
	}

	/**
	 * Get a string used as filter names unique to the extending query.
	 *
	 * @since 3.8.0
	 *
	 * @todo Deprecate.
	 *
	 * @param string $filter Filter name.
	 * @return string
	 */
	protected function get_filter( $filter ) {
		return 'llms_' . $this->id . '_query_' . $filter;
	}

	/**
	 * Get the number of results to skip for the query based on the current page and per_page vars.
	 *
	 * @since 3.8.0
	 *
	 * @return int
	 */
	protected function get_skip() {
		return absint( ( $this->get( 'page' ) - 1 ) * $this->get( 'per_page' ) );
	}

	/**
	 * Performs the SQL query.
	 *
	 * @since 6.0.0
	 *
	 * @return array An integer-keyed array of row objects.
	 */
	protected function perform_query() {

		global $wpdb;
		return $wpdb->get_results( $this->query ); // phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Set variables related to total number of results and pages possible with supplied arguments.
	 *
	 * @since 3.8.0
	 * @since 4.5.1 Bail early if the query arg `no_found_rows` is true, b/c no reason to calculate anything.
	 * @deprecated 6.0.0 `LLMS_Database_Query::set_found_results()` is deprecated.
	 *
	 * @return void
	 */
	protected function set_found_results() {

		_deprecated_function( 'LLMS_Database_Query::set_found_results()', '6.0.0' );

		// If no results, or found rows not required, bail early b/c no reason to calculate anything.
		if ( ! $this->number_results || $this->get( 'no_found_rows' ) ) {
			return;
		}

		$this->found_results = $this->found_results();
		$this->max_pages     = absint( ceil( $this->found_results / $this->get( 'per_page' ) ) );
	}

	/**
	 * Perform a SQL to retrieve the total number of found results for the given query.
	 *
	 * @since 6.0.0
	 *
	 * @return int
	 */
	protected function found_results() {

		global $wpdb;
		return (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // db call ok; no-cache ok.
	}

	/**
	 * Retrieve the prepared SQL for the SELECT clause.
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
		 * Filters the query SELECT columns.
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
	 * Retrieve the prepared SQL for the LIMIT clause.
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
		 * Filters the query LIMIT clause.
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
	 * Retrieve the prepared SQL for the ORDER BY clause.
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
				$sql  .= $pre . sanitize_sql_orderby( "{$orderby} {$order}" );
				$comma = true;
			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		/**
		 * Filters the query ORDER BY clause.
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

	/**
	 * Gets information about properties that used to be public and have been replaced with public getters.
	 *
	 * Used by `__get()` and `__set()` and will be removed when these are properly removed in the next
	 * major release.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	private function legacy_public_props() {

		return array(
			// Property      => $0 = alternative prop or method, $1 = has replacement.
			'found_results'  => array( 'get_found_results', true ),
			'max_pages'      => array( 'get_max_pages', true ),
			'number_results' => array( 'get_number_results', true ),
			'query_vars'     => array( 'query_vars', false ),
			'results'        => array( 'get_results', true ),
		);
	}

	/**
	 * Throws a deprecation message when a formerly public property is accessed directly.
	 *
	 * @since 6.0.0
	 *
	 * @param string $prop Property name.
	 * @return void
	 */
	private function public_prop_deprecation( $prop ) {

		$legacy_props = $this->legacy_public_props();

		list( $val, $has_replacement ) = $legacy_props[ $prop ];

		$class     = get_called_class();
		$is_method = method_exists( $this, $val );
		$suffix    = $is_method ? '()' : '';
		_deprecated_function( esc_html( "Public access to property {$class}::{$prop}" ), '6.0.0', $has_replacement ? esc_html( "{$class}::{$val}{$suffix}" ) : '' );
	}

	/**
	 * Preserve backwards compat for read access to formerly public and removed class properties.
	 *
	 * @since 6.0.0
	 *
	 * @param string $key Property key name.
	 * @return mixed
	 */
	public function __get( $key ) {

		// Handle formerly public properties.
		$legacy_props = $this->legacy_public_props();
		if ( array_key_exists( $key, $legacy_props ) ) {
			$this->public_prop_deprecation( $key );
			$val = $legacy_props[ $key ][0];
			return method_exists( $this, $val ) ? $this->$val() : $this->$val;
		} elseif ( 'sql' === $key ) {
			$class = get_called_class();
			_deprecated_function( esc_html( "Property {$class}::sql" ), '6.0.0', esc_html( "{$class}::get_query()" ) );
			return $this->query;
		}
	}

	/**
	 * Preserve backwards compat for write access to formerly public and removed class properties.
	 *
	 * @since 6.0.0
	 *
	 * @param string $key Property name.
	 * @param mixed  $val Property value.
	 * @return void
	 */
	public function __set( $key, $val ) {

		$legacy_props = $this->legacy_public_props();
		if ( array_key_exists( $key, $legacy_props ) ) {
			$this->public_prop_deprecation( $key );
			$this->$key = $val;
		} elseif ( 'sql' === $key ) {
			$class = get_called_class();
			_deprecated_function( esc_html( "Property {$class}::sql" ), '6.0.0', esc_html( "{$class}::query" ) );
			$this->query = $val;
		}
	}

	/**
	 * Handle backwards compatibility for the misspelled (and removed) method `preprare_query()`.
	 *
	 * @since 6.0.0
	 *
	 * @param string $name Method name.
	 * @param array  $args Arguments passed to the method.
	 * @return void|string
	 */
	public function __call( $name, $args ) {
		if ( 'preprare_query' === $name ) {
			$class = get_called_class();
			_deprecated_function( esc_html( "{$class}::preprare_query()" ), '6.0.0', esc_html( "{$class}::prepare_query()" ) );
			return $this->prepare_query();
		}
	}

	/**
	 * Prepare the query.
	 *
	 * Should return the query which will be used by `query()`.
	 *
	 * This *should* be an abstract method but is defined here for backwards compatibility
	 * to preserve the previous method, `preprare_query()` (notice the misspelling).
	 *
	 * Once the `preprare_query()` method is fully removed in the next major release this
	 * method can be removed in favor of the abstract from the parent class.
	 *
	 * @since 6.0.0
	 *
	 * @return mixed
	 */
	protected function prepare_query() {
		if ( method_exists( $this, 'preprare_query' ) ) {
			$class = get_called_class();
			_deprecated_function( esc_html( "{$class}::preprare_query()" ), '6.0.0', esc_html( "{$class}::prepare_query()" ) );
			return $this->preprare_query();
		} else {
			_doing_it_wrong(
				__METHOD__,
				esc_html( sprintf( __( "Method '%s' not implemented. Must be overridden in subclass.", 'lifterlms' ), __METHOD__ ) ),
				'6.0.0'
			);
		}
	}
}
