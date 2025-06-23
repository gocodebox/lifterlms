<?php
/**
 * Query LifterLMS Students for a given course / membership.
 *
 * @package LifterLMS/Classes
 *
 * @since 3.16.0
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query LifterLMS Students for a given course / membership
 *
 * @since 3.16.0
 * @since 3.35.0 Unknown.
 * @since 4.2.0 Added `exclude` arg.
 *
 * @arg  $attempt    (int)       Query by attempt number
 * @arg  $quiz_id    (int|array) Query by Quiz WP post ID (locate multiple quizzes with an array of ids)
 * @arg  $student_id (int|array) Query by WP User ID (locate by multiple users with an array of ids)
 *
 * @arg  $page       (int)       Get results by page
 * @arg  $per_page   (int)       Number of results per page (default: 25)
 * @arg  $sort       (array)     Define query sorting options [id,student_id,quiz_id,start_date,update_date,end_date,attempt,grade,current,passed]
 *
 * @example
 *       $query = new LLMS_Query_Quiz_Attempt( array(
 *           'student_id' => 1234,
 *           'quiz_id' => 5678,
 *       ) );
 */
class LLMS_Query_Quiz_Attempt extends LLMS_Database_Query {

	/**
	 * Identify the extending query
	 *
	 * @var  string
	 */
	protected $id = 'quiz_attempt';

	/**
	 * Retrieve default arguments for a student query.
	 *
	 * @since 3.16.0
	 * @since 4.2.0 Added `exclude` default arg.
	 * @since 7.8.0 Added `can_be_resumed` default arg.
	 *
	 * @return array
	 */
	protected function get_default_args() {

		$args = array(
			'student_id'     => array(),
			'quiz_id'        => array(),
			'sort'           => array(
				'start_date' => 'DESC',
				'attempt'    => 'DESC',
				'id'         => 'ASC',
			),
			'status'         => array(),
			'status_exclude' => array(),
			'attempt'        => null,
			'exclude'        => array(),
			'can_be_resumed' => null,
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		return apply_filters( $this->get_filter( 'default_args' ), $args );

	}

	/**
	 * Retrieve an array of LLMS_Quiz_Attempts for the given result set returned by the query
	 *
	 * @since 3.16.0
	 *
	 * @return LLMS_Quiz_Attempt[]
	 */
	public function get_attempts() {

		$attempts = array();
		$results  = $this->get_results();

		if ( $results ) {

			foreach ( $results as $result ) {
				$attempts[] = new LLMS_Quiz_Attempt( $result->id );
			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $attempts;
		}

		return apply_filters( $this->get_filter( 'get_attempts' ), $attempts );

	}

	/**
	 * Parses data passed to $statuses
	 *
	 * Convert strings to array and ensure resulting array contains only valid statuses.
	 * If no valid statuses, returns to the default.
	 *
	 * @since 3.16.0
	 * @since 4.2.0 Added `exclude` arg sanitization.
	 *
	 * @return void
	 */
	protected function parse_args() {

		// Sanitize post, user, excluded attempts ids.
		foreach ( array( 'student_id', 'quiz_id', 'exclude' ) as $key ) {
			$this->arguments[ $key ] = $this->sanitize_id_array( $this->arguments[ $key ] );
		}

		// Validate status args.
		$valid_statuses = array_keys( llms_get_quiz_attempt_statuses() );
		foreach ( array( 'status', 'status_exclude' ) as $key ) {

			// Allow single statuses to be passed in as a string.
			if ( is_string( $this->arguments[ $key ] ) ) {
				$this->arguments[ $key ] = array( $this->arguments[ $key ] );
			}

			// Ensure submitted statuses are valid.
			if ( $this->arguments[ $key ] ) {
				$this->arguments[ $key ] = array_intersect( $valid_statuses, $this->arguments[ $key ] );
			}
		}

	}

	/**
	 * Prepare the SQL for the query.
	 *
	 * @since 3.16.0
	 * @since 6.0.0 Renamed from `preprare_query()`.
	 *
	 * @return string
	 */
	protected function prepare_query() {

		global $wpdb;

		return "SELECT SQL_CALC_FOUND_ROWS id
				FROM {$wpdb->prefix}lifterlms_quiz_attempts
				{$this->sql_where()}
				{$this->sql_orderby()}
				{$this->sql_limit()};";

	}

	/**
	 * SQL "where" clause for the query.
	 *
	 * @since 3.16.0
	 * @since 3.35.0 Better SQL preparation.
	 * @since 4.2.0 Added `exclude` arg logic.
	 * @since 7.8.0 Added `can_be_resumed` arg logic.
	 *
	 * @return string
	 */
	protected function sql_where() {

		global $wpdb;

		$sql = 'WHERE 1';

		foreach ( array( 'quiz_id', 'student_id' ) as $key ) {
			$ids = $this->get( $key );
			if ( $ids ) {
				$prepared = implode( ',', $ids );
				$sql     .= " AND {$key} IN ({$prepared})";
			}
		}

		// Add attempt lookup.
		$val = $this->get( 'attempt' );
		if ( '' !== $val ) {
			$sql .= $wpdb->prepare( ' AND attempt = %d', $val );
		}

		// Add attempt exclude.
		$exclude = $this->get( 'exclude' );
		if ( $exclude ) {
			$prepared = implode( ',', $exclude );
			$sql     .= " AND id NOT IN ({$prepared})";
		}

		$status = $this->get( 'status' );
		if ( $status ) {
			$prepared = implode( ',', array_map( array( $this, 'escape_and_quote_string' ), $status ) );
			$sql     .= " AND status IN ({$prepared})";
		}

		$status_exclude = $this->get( 'status_exclude' );
		if ( $status_exclude ) {
			$prepared = implode( ',', array_map( array( $this, 'escape_and_quote_string' ), $status_exclude ) );
			$sql     .= " AND status NOT IN ({$prepared})";
		}

		$can_be_resumed = $this->get( 'can_be_resumed' );
		if ( '' !== $can_be_resumed ) {
			$sql .= $wpdb->prepare( ' AND can_be_resumed = %d', $can_be_resumed );
		}

		return apply_filters( $this->get_filter( 'where' ), $sql, $this );

	}

}
