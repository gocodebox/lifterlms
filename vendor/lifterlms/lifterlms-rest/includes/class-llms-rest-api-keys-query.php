<?php
/**
 * Perform db queries for API Keys
 *
 * @package  LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_API_Keys_Query class
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_API_Keys_Query extends LLMS_Database_Query {

	/**
	 * Identify the Query
	 *
	 * @var  string
	 */
	protected $id = 'rest_api_key';

	/**
	 * Retrieve default arguments for a query
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	protected function get_default_args() {

		$args = array(
			'include'     => array(),
			'exclude'     => array(),
			'per_page'    => 10,
			'permissions' => '',
			'user'        => array(),
			'user_not_in' => array(),
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		if ( $args['suppress_filters'] ) {
			return $args;
		}

		return apply_filters( $this->get_filter( 'default_args' ), $args, $this );

	}

	/**
	 * Retrieve an array of LLMS_REST_API_Keys for the given result set returned by the query
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_keys() {

		$keys    = array();
		$results = $this->get_results();

		if ( $results ) {

			foreach ( $results as $result ) {
				$keys[] = LLMS_REST_API()->keys()->get( $result->id, true );
			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $keys;
		}

		return apply_filters( $this->get_filter( 'get_keys' ), $keys, $this );

	}

	/**
	 * Parses argument data
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	protected function parse_args() {

		// sanitize post & user ids.
		foreach ( array( 'include', 'exclude', 'user', 'user_not_in' ) as $key ) {
			$this->arguments[ $key ] = $this->sanitize_id_array( $this->arguments[ $key ] );
		}

		// validate permissions.
		$permissions = $this->get( 'permissions' );
		if ( $permissions && ! in_array( $permissions, array_keys( LLMS_REST_API()->keys()->get_permissions() ), true ) ) {
			$this->arguments['permissions'] = '';
		}

	}

	/**
	 * Prepare the SQL for the query
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	protected function preprare_query() {

		global $wpdb;

		return "SELECT SQL_CALC_FOUND_ROWS id
				FROM {$wpdb->prefix}lifterlms_api_keys
				{$this->sql_where()}
				{$this->sql_orderby()}
				{$this->sql_limit()};";

	}

	/**
	 * SQL "where" clause for the query
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	protected function sql_where() {

		global $wpdb;

		$sql = 'WHERE 1';

		// "IN" clauses for id fields.
		$ids_include = array(
			'include' => 'id',
			'user'    => 'user_id',
		);
		foreach ( $ids_include as $query_key => $db_key ) {
			$ids = $this->get( $query_key );
			if ( $ids ) {
				$prepared = implode( ',', $ids );
				$sql     .= " AND {$db_key} IN ({$prepared})";
			}
		}

		// "NOT IN" clauses for id fields.
		$ids_exclude = array(
			'exclude'     => 'id',
			'user_not_in' => 'user_id',
		);
		foreach ( $ids_exclude as $query_key => $db_key ) {
			$ids = $this->get( $query_key );
			if ( $ids ) {
				$prepared = implode( ',', $ids );
				$sql     .= " AND {$db_key} NOT IN ({$prepared})";
			}
		}

		// Permission match.
		$permissions = $this->get( 'permissions' );
		if ( $permissions ) {
			$sql .= $wpdb->prepare( ' AND permissions = %s', $permissions );
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		return apply_filters( $this->get_filter( 'where' ), $sql, $this );

	}

}
