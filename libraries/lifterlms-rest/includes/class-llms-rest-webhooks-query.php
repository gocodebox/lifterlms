<?php
/**
 * Perform db queries for Webhooks
 *
 * @package  LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.22
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
	 * @var string
	 */
	protected $id = 'rest_webhook';

	/**
	 * Retrieve default arguments for a query
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.16 Drop usage of `this->get_filter( 'default_args' )` in favor of `'llms_rest_webhook_query_default_args'`.
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

		/**
		 * Filters the webhooks query default args
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param array                    $args           Array of default arguments to set up the query with.
		 * @param LLMS_REST_Webhooks_Query $webhooks_query Instance of LLMS_REST_Webhooks_Query.
		 */
		return apply_filters( 'llms_rest_webhook_query_default_args', $args, $this );

	}

	/**
	 * Retrieve an array of LLMS_REST_Webhook objects for the given result set returned by the query
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.16 Drop usage of `this->get_filter( 'get_webhooks' )` in favor of `'llms_rest_webhook_query_get_webhooks'`.
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

		/**
		 * Filters the list of webhooks
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param LLMS_REST_Webhook[]      $webhooks       Array of LLMS_REST_Webhook instances.
		 * @param LLMS_REST_Webhooks_Query $webhooks_query Instance of LLMS_REST_Webhooks_Query.
		 */
		return apply_filters( 'llms_rest_webhook_query_get_webhooks', $hooks, $this );

	}

	/**
	 * Parses argument data
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	protected function parse_args() {

		// Sanitize post & user ids.
		foreach ( array( 'include', 'exclude' ) as $key ) {
			$this->arguments[ $key ] = $this->sanitize_id_array( $this->arguments[ $key ] );
		}

		// Validate status.
		$status = $this->get( 'status' );
		if ( $status && ! in_array( $status, array_keys( LLMS_REST_API()->webhooks()->get_statuses() ), true ) ) {
			$this->arguments['status'] = '';
		}

	}

	/**
	 * Prepare the SQL for the query
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.16 Use `$this->sql_select_columns({columns})` to determine the columns to select.
	 * @since 1.0.0-beta.22 Renamed from `preprare_query()`.
	 *
	 * @return string
	 */
	protected function prepare_query() {

		global $wpdb;

		return "SELECT {$this->sql_select_columns( 'id' )}
				FROM {$wpdb->prefix}lifterlms_webhooks
				{$this->sql_where()}
				{$this->sql_orderby()}
				{$this->sql_limit()};";

	}

	/**
	 * SQL "where" clause for the query
	 *
	 * @since 1.0.0-beta.1
	 * @since 1.0.0-beta.16 Drop usage of `$this->get_filter('where')` in favor of `'llms_rest_webhook_query_where'`.
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

		/**
		 * Filters the query WHERE clause
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param string                   $sql            The WHERE clause of the query.
		 * @param LLMS_REST_Webhooks_Query $webhooks_query Instance of LLMS_REST_Webhooks_Query.
		 */
		return apply_filters( 'llms_rest_webhook_query_where', $sql, $this );

	}

}
