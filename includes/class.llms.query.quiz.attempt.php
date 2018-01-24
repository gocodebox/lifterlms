<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Query LifterLMS Students for a given course / membership
* @since    [version]
* @version  [version]
*
* @arg  $attempt     (int)        Query by attempt number
* @arg  $current     (bool)       Query by current status
* @arg  $passed      (bool)       Query by passed status
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
	 * @since    [version]
	 * @version  [version]
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
			'attempt' => null,
			'current' => null,
			'passed' => null,
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		return apply_filters( $this->get_filter( 'default_args' ), $args );

	}

	/**
	 * Retrieve an array of LLMS_Quiz_Attempts for the given result set returned by the query
	 * @return   array
	 * @since    [version]
	 * @version  [version]
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
	 * @since    [version]
	 * @version  [version]
	 */
	protected function parse_args() {

		// sanitize post & user ids
		foreach ( array( 'student_id', 'quiz_id' ) as $key ) {
			$this->arguments[ $key ] = $this->sanitize_id_array( $this->arguments[ $key ] );
		}

	}

	/**
	 * Prepare the SQL for the query
	 * @return   void
	 * @since    [version]
	 * @version  [version]
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
	 * @since    [version]
	 * @version  [version]
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

		// add option bools
		foreach ( array( 'current', 'attempt', 'passed' ) as $key ) {

			$val = $this->get( $key );
			if ( '' !== $val ) {
				$sql .= $wpdb->prepare( " AND {$key} = %d", $val );
			}

		}

		return apply_filters( $this->get_filter( 'where' ), $sql, $this );

	}

}
