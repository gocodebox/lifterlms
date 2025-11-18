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
			'search'         => '',
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

		$select = 'SELECT SQL_CALC_FOUND_ROWS qa.id';
		$from   = "FROM {$wpdb->prefix}lifterlms_quiz_attempts qa";
		$joins  = $this->sql_joins();

		return "{$select} {$from} {$joins} {$this->sql_where()} {$this->sql_orderby()} {$this->sql_limit()};";
	}

	/**
	 * SQL "joins" clause for the query.
	 *
	 * @since 9.1.0
	 *
	 * @return string
	 */
	protected function sql_joins() {
		global $wpdb;

		$joins = '';

		// Join users table for search functionality
		if ( $this->get( 'search' ) ) {
			$joins .= " LEFT JOIN {$wpdb->users} u ON qa.student_id = u.ID";
			$joins .= " LEFT JOIN {$wpdb->usermeta} um_first ON u.ID = um_first.user_id AND um_first.meta_key = 'first_name'";
			$joins .= " LEFT JOIN {$wpdb->usermeta} um_last ON u.ID = um_last.user_id AND um_last.meta_key = 'last_name'";
			// Add in posts of type llms_quiz for search functionality
			$joins .= " LEFT JOIN {$wpdb->posts} p ON qa.quiz_id = p.ID AND p.post_type = 'llms_quiz'";
		}

		return $joins;
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
				$sql     .= " AND qa.{$key} IN ({$prepared})";
			}
		}

		// Add attempt lookup.
		$val = $this->get( 'attempt' );
		if ( '' !== $val ) {
			$sql .= $wpdb->prepare( ' AND qa.attempt = %d', $val );
		}

		// Add attempt exclude.
		$exclude = $this->get( 'exclude' );
		if ( $exclude ) {
			$prepared = implode( ',', $exclude );
			$sql     .= " AND qa.id NOT IN ({$prepared})";
		}

		$status = $this->get( 'status' );
		if ( $status ) {
			$prepared = implode( ',', array_map( array( $this, 'escape_and_quote_string' ), $status ) );
			$sql     .= " AND qa.status IN ({$prepared})";
		}

		$status_exclude = $this->get( 'status_exclude' );
		if ( $status_exclude ) {
			$prepared = implode( ',', array_map( array( $this, 'escape_and_quote_string' ), $status_exclude ) );
			$sql     .= " AND qa.status NOT IN ({$prepared})";
		}

		$can_be_resumed = $this->get( 'can_be_resumed' );
		if ( '' !== $can_be_resumed ) {
			$sql .= $wpdb->prepare( ' AND qa.can_be_resumed = %d', $can_be_resumed );
		}

		$search = $this->get( 'search' );
		if ( $search ) {
			$search = $wpdb->esc_like( $search );
			$sql   .= $wpdb->prepare(
				' AND (
					u.user_login LIKE %s
					OR u.user_email LIKE %s
					OR u.display_name LIKE %s
					OR um_first.meta_value LIKE %s
					OR um_last.meta_value LIKE %s
					OR p.post_title LIKE %s
				)',
				'%' . $search . '%',
				'%' . $search . '%',
				'%' . $search . '%',
				'%' . $search . '%',
				'%' . $search . '%',
				'%' . $search . '%'
			);
		}

		return apply_filters( $this->get_filter( 'where' ), $sql, $this );
	}
}
