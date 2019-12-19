<?php
/**
 * Perform db queries for Webhooks
 *
 * @package  LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Webhooks_Query class.
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Webhooks_Query extends LLMS_Database_Query {

	/**
	 * Identify the Query
	 *
	 * @var  string
	 */
	protected $id = 'rest_webhook';

	/**
	 * Retrieve default arguments for a query
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	protected function get_default_args() {

		$args = array(
			'include'  => array(),
			'exclude'  => array(),
			'status'   => '',
			'per_page' => 10,
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		if ( $args['suppress_filters'] ) {
			return $args;
		}

		return apply_filters( $this->get_filter( 'default_args' ), $args, $this );

	}

	/**
	 * Retrieve an array of LLMS_REST_Webhook objects for the given result set returned by the query
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_webhooks() {

		$hooks   = array();
		$results = $this->get_results();

		if ( $results ) {

			foreach ( $results as $result ) {
				$hooks[] = LLMS_REST_API()->webhooks()->get( $result->id, true );
			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $hooks;
		}

		return apply_filters( $this->get_filter( 'get_webhooks' ), $hooks, $this );

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
		foreach ( array( 'include', 'exclude' ) as $key ) {
			$this->arguments[ $key ] = $this->sanitize_id_array( $this->arguments[ $key ] );
		}

		// validate status.
		$status = $this->get( 'status' );
		if ( $status && ! in_array( $status, array_keys( LLMS_REST_API()->webhooks()->get_statuses() ), true ) ) {
			$this->arguments['status'] = '';
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
				FROM {$wpdb->prefix}lifterlms_webhooks
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
			'exclude' => 'id',
		);
		foreach ( $ids_exclude as $query_key => $db_key ) {
			$ids = $this->get( $query_key );
			if ( $ids ) {
				$prepared = implode( ',', $ids );
				$sql     .= " AND {$db_key} NOT IN ({$prepared})";
			}
		}

		// Status match.
		$status = $this->get( 'status' );
		if ( $status ) {
			$sql .= $wpdb->prepare( ' AND status = %s', $status );
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		return apply_filters( $this->get_filter( 'where' ), $sql, $this );

	}

}
