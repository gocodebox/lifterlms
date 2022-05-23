<?php
/**
 * Perform db queries for events
 *
 * @package LifterLMS/Classes
 *
 * @since 3.36.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Events_Query class
 *
 * @since 3.36.0
 */
class LLMS_Events_Query extends LLMS_Database_Query {

	/**
	 * Identify the Query
	 *
	 * @var string
	 */
	protected $id = 'events';

	/**
	 * Retrieve default arguments for a query
	 *
	 * @since 3.36.0
	 * @since 4.7.0 Drop usage of `this->get_filter( 'default_args' )` in favor of `'llms_events_query_default_args'`.
	 *
	 * @return array
	 */
	protected function get_default_args() {

		$args = array(
			'actor'         => array(),
			'actor_not_in'  => array(),
			'date_after'    => '',
			'date_before'   => '',
			'exclude'       => array(),
			'include'       => array(),
			'object_type'   => '',
			'object'        => array(),
			'object_not_in' => array(),
			'event_type'    => '',
			'event_action'  => '',
			'sort'          => array(
				'date' => 'DESC',
			),
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		if ( $args['suppress_filters'] ) {
			return $args;
		}

		/**
		 * Filters the events query default args
		 *
		 * @since 3.36.0
		 *
		 * @param array             $args         Array of default arguments to set up the query with.
		 * @param LLMS_Events_Query $events_query Instance of LLMS_Events_Query.
		 */
		return apply_filters( 'llms_events_query_default_args', $args, $this );

	}

	/**
	 * Retrieve an array of LLMS_Event objects for the given result set returned by the query
	 *
	 * @since 3.36.0
	 * @since 4.7.0 Drop usage of `$this->get_filter('get_events')` in favor of `'llms_events_query_get_events'`.
	 *
	 * @return array
	 */
	public function get_events() {

		$events  = array();
		$results = $this->get_results();

		if ( $results ) {

			foreach ( $results as $result ) {
				$events[] = new LLMS_Event( $result->id, true );
			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $events;
		}

		/**
		 * Filters the list of events
		 *
		 * @since 3.36.0
		 *
		 * @param LLMS_Event[]      $events       Array of LLMS_Event instances.
		 * @param LLMS_Events_Query $events_query Instance of LLMS_Events_Query.
		 */
		return apply_filters( 'llms_events_query_get_events', $events, $this );

	}

	/**
	 * Parses argument data
	 *
	 * @since 3.36.0
	 *
	 * @return void
	 */
	protected function parse_args() {

		// Sanitize post & user ids.
		foreach ( array( 'actor', 'actor_not_in', 'object', 'object_not_in', 'include', 'exclude' ) as $key ) {
			$this->arguments[ $key ] = $this->sanitize_id_array( $this->arguments[ $key ] );
		}

		foreach ( array( 'date_before', 'date_after' ) as $key ) {
			if ( ! empty( $this->arguments[ $key ] ) ) {
				$date = $this->arguments[ $key ];
				if ( ! is_numeric( $date ) ) {
					$date = strtotime( $date );
				}
				$this->arguments[ $key ] = date( 'Y-m-d H:i:s', $date );
			}
		}

	}

	/**
	 * Prepare the SQL for the query.
	 *
	 * @since 3.36.0
	 * @since 4.7.0 Use `$this->sql_select_columns({columns})` to determine the columns to select.
	 * @since 6.0.0 Renamed from `preprare_query()`.
	 *
	 * @return string
	 */
	protected function prepare_query() {

		global $wpdb;

		return "SELECT {$this->sql_select_columns( 'id' )}
				FROM {$wpdb->prefix}lifterlms_events
				{$this->sql_where()}
				{$this->sql_orderby()}
				{$this->sql_limit()};";

	}

	/**
	 * SQL "where" clause for the query
	 *
	 * @since 3.36.0
	 * @since 4.7.0 Drop usage of `$this->get_filter('where')` in favor of `'llms_events_query_where'`.
	 *
	 * @return string
	 */
	protected function sql_where() {

		global $wpdb;

		$sql = 'WHERE 1';

		// "IN" clauses for id fields.
		$ids_include = array(
			'actor'   => 'actor_id',
			'object'  => 'object_id',
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
			'actor_not_in'  => 'actor_id',
			'object_not_in' => 'object_id',
			'exclude'       => 'id',
		);
		foreach ( $ids_exclude as $query_key => $db_key ) {
			$ids = $this->get( $query_key );
			if ( $ids ) {
				$prepared = implode( ',', $ids );
				$sql     .= " AND {$db_key} NOT IN ({$prepared})";
			}
		}

		// Matching fields.
		$matching = array( 'object_type', 'event_type', 'event_action' );
		foreach ( $matching as $key ) {
			$val = $this->get( $key );
			if ( $val ) {
				$sql .= sprintf( " AND {$key} = '%s'", esc_sql( $val ) );
			}
		}

		// Date fields.
		$before = $this->get( 'date_before' );
		if ( $before ) {
			$sql .= $wpdb->prepare( ' AND date < %s', $before );
		}

		$after = $this->get( 'date_after' );
		if ( $after ) {
			$sql .= $wpdb->prepare( ' AND date > %s', $after );
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		/**
		 * Filters the query WHERE clause
		 *
		 * @since 3.36.0
		 *
		 * @param string            $sql          The WHERE clause of the query.
		 * @param LLMS_Events_Query $events_query Instance of LLMS_Events_Query.
		 */
		return apply_filters( 'llms_events_query_where', $sql, $this );

	}

}
