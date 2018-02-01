<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Query LifterLMS Students for a given course / membership
* @since    3.16.0
* @version  3.16.0
*
* @arg  $attempt     (int)        Query by attempt number
* @arg  $quiz_id     (int|array)  Query by Quiz WP post ID (locate multiple quizzes with an array of ids)
* @arg  $student_id  (int|array)  Query by WP User ID (locate by multiple users with an array of ids)
*
* @arg  $page        (int)        Get results by page
* @arg  $per_page    (int)        Number of results per page (default: 25)
* @arg  $sort        (array)      Define query sorting options [id,student_id,quiz_id,start_date,update_date,end_date,attempt,grade,current,passed]
*
* @example
* 		$query = new LLMS_Query_Quiz_Attempt( array(
* 			'student_id' => 1234,
* 			'quiz_id' => 5678,
* 		) );
*/
class LLMS_Query_Quiz_Attempt extends LLMS_Database_Query {

	/**
	 * Identify the extending query
	 * @var  string
	 */
	protected $id = 'quiz_attempt';

	/**
	 * Retrieve default arguments for a student query
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	protected function get_default_args() {

		$args = array(
			'student_id' => array(),
			'quiz_id' => array(),
			'sort' => array(
				'start_date' => 'DESC',
				'attempt' => 'DESC',
				'id' => 'ASC',
			),
			'status' => array(),
			'status_exclude' => array(),
			'attempt' => null,
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		return apply_filters( $this->get_filter( 'default_args' ), $args );

	}

	/**
	 * Retrieve an array of LLMS_Quiz_Attempts for the given result set returned by the query
	 * @return   array
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	public function get_attempts() {

		$attempts = array();
		$results = $this->get_results();

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
	 * Convert strings to array and ensure resulting array contains only valid statuses
	 * If no valid statuses, returns to the default
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	protected function parse_args() {

		// sanitize post & user ids
		foreach ( array( 'student_id', 'quiz_id' ) as $key ) {
			$this->arguments[ $key ] = $this->sanitize_id_array( $this->arguments[ $key ] );
		}

		// validate status args
		$valid_statuses = array_keys( llms_get_quiz_attempt_statuses() );
		foreach ( array( 'status', 'status_exclude' ) as $key ) {

			// allow single statuses to be passed in as a string
			if ( is_string( $this->arguments[ $key ] ) ) {
				$this->arguments[ $key ] = array( $this->arguments[ $key ] );
			}

			// ensure submitted statuses are valid
			if ( $this->arguments[ $key ] ) {
				$this->arguments[ $key ] = array_intersect( $valid_statuses, $this->arguments[ $key ] );
			}
		}

	}

	/**
	 * Prepare the SQL for the query
	 * @return   void
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	protected function preprare_query() {

		global $wpdb;

		return "SELECT SQL_CALC_FOUND_ROWS id
				FROM {$wpdb->prefix}lifterlms_quiz_attempts
				{$this->sql_where()}
				{$this->sql_orderby()}
				{$this->sql_limit()};";

	}

	/**
	 * SQL "where" clause for the query
	 * @return   string
	 * @since    3.16.0
	 * @version  3.16.0
	 */
	protected function sql_where() {

		global $wpdb;

		$sql = 'WHERE 1';

		foreach ( array( 'quiz_id', 'student_id' ) as $key ) {
			$ids = $this->get( $key );
			if ( $ids ) {
				$prepared = implode( ',', $ids );
				$sql .= " AND {$key} IN ({$prepared})";
			}
		}

		// add numeric lookups
		foreach ( array( 'attempt' ) as $key ) {

			$val = $this->get( $key );
			if ( '' !== $val ) {
				$sql .= $wpdb->prepare( " AND {$key} = %d", $val );
			}
		}

		$status = $this->get( 'status' );
		if ( $status ) {
			$prepared = implode( ',', array_map( array( $this, 'escape_and_quote_string' ), $status ) );
			$sql .= " AND status IN ({$prepared})";
		}

		$status_exclude = $this->get( 'status_exclude' );
		if ( $status_exclude ) {
			$prepared = implode( ',', array_map( array( $this, 'escape_and_quote_string' ), $status_exclude ) );
			$sql .= " AND status NOT IN ({$prepared})";
		}

		return apply_filters( $this->get_filter( 'where' ), $sql, $this );

	}

}
