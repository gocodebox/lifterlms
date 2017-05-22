<?php
/**
* Query LifterLMS Students for a given course / membership
* @since    3.4.0
* @version  ??
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Student_Query extends LLMS_Database_Query {

	/**
	 * Identify the extending query
	 * @var  string
	 */
	protected $id = 'student';

	/**
	 * Retrieve default arguments for a student query
	 * @return   array
	 * @since    3.4.0
	 * @version  ??
	 */
	protected function get_default_args() {

		global $post;

		$post_id = ! empty( $post->ID ) ? $post->ID : $this->get( 'post_id' );

		$args = array(
			'post_id' => $post_id,
			'sort' => array(
				'date' => 'DESC',
				'status' => 'ASC',
				'last_name' => 'ASC',
				'first_name' => 'ASC',
				'id' => 'ASC',
			),
			'statuses' => array_keys( llms_get_enrollment_statuses() ),
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		return apply_filters( $this->get_filter( 'default_args' ), $args );

	}

	/**
	 * Retrieve an array of LLMS_Students for the given set of students
	 * returned by the query
	 * @return   array
	 * @since    3.4.0
	 * @version  ??
	 */
	public function get_students() {

		$students = array();
		$results = $this->get_results();

		if ( $results ) {

			foreach ( $results as $result ) {
				$students[] = new LLMS_Student( $result->id );
			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $students;
		}

		return apply_filters( $this->get_filter( 'get_students' ), $students );

	}

	/**
	 * Parses data passed to $statuses
	 * Convert strings to array and ensure resulting array contains only valid statuses
	 * If no valid statuses, returns to the default
	 * @return   void
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	protected function parse_args() {

		$statuses = $this->arguments['statuses'];

		// allow strings to be submitted when only requesting one status
		if ( is_string( $statuses ) ) {
			$statuses = array( $statuses );
		}

		// ensure only valid statuses are used
		$statuses = array_intersect( $statuses, array_keys( llms_get_enrollment_statuses() ) );

		// no statuses should return original default
		if ( ! $statuses ) {
			$statuses = array_keys( llms_get_enrollment_statuses() );
		}

		$this->arguments['statuses'] = $statuses;

	}

	/**
	 * Prepare the SQL for the query
	 * @return   void
	 * @since    3.4.0
	 * @version  ??
	 */
	protected function preprare_query() {

		global $wpdb;

		$vars = array(
			$this->get( 'post_id' ),
			$this->get( 'post_id' ),
		);

		if ( $this->get( 'search' ) ) {
			$search = '%' . $wpdb->esc_like( $this->get( 'search' ) ) . '%';
			$vars[] = $search;
			$vars[] = $search;
			$vars[] = $search;
		}

		$vars[] = $this->get_skip();
		$vars[] = $this->get( 'per_page' );

		$sql = $wpdb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS
			  u.ID AS id
			, m_last.meta_value AS last_name
			, m_first.meta_value AS first_name
			, u.user_email AS email
			, (
				SELECT meta_value FROM {$wpdb->prefix}lifterlms_user_postmeta
				WHERE meta_key = '_status'
				  AND user_id = id
				  AND post_id = %d
				ORDER BY updated_date DESC
				LIMIT 1
			  ) AS status
			, (
				SELECT updated_date FROM {$wpdb->prefix}lifterlms_user_postmeta
				WHERE meta_key = '_status'
				  AND user_id = id
				  AND post_id = %d
				ORDER BY updated_date DESC
				LIMIT 1
			  ) AS date

			FROM {$wpdb->users} AS u

			JOIN {$wpdb->usermeta} AS m_first ON u.ID = m_first.user_id
			JOIN {$wpdb->usermeta} AS m_last ON u.ID = m_last.user_id

			WHERE m_first.meta_key = 'first_name'
			  AND m_last.meta_key = 'last_name'

			{$this->sql_search()}

			{$this->sql_having()}

			{$this->sql_orderby()}

			LIMIT %d, %d
			;",
			$vars
		);

		return $sql;

	}

	/**
	 * Retrieve prepared SQL for the HAVING clause
	 * @return   string
	 * @since    3.4.0
	 * @version  ??
	 */
	private function sql_having() {

		global $wpdb;

		$sql = 'HAVING status IS NOT NULL';

		$sql .= ' AND status IN (';
		$comma = false;
		$statuses = array();
		foreach ( $this->get( 'statuses' ) as $status ) {
			$sql .= $comma ? ', %s' : ' %s';
			$statuses[] = $status;
			$comma = true;
		}
		$sql .= ' )';
		$sql = $wpdb->prepare( $sql, $statuses );

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		return apply_filters( $this->get_filter( 'having' ), $sql, $this );

	}

	/**
	 * Retrieve the prepared SEARCH query for the WHERE clause
	 * @return   string
	 * @since    3.4.0
	 * @version  ??
	 */
	private function sql_search() {

		$sql = '';

		if ( $this->get( 'search' ) ) {

			global $wpdb;
			$sql .= '  AND (
                   m_last.meta_value LIKE %s
                OR m_first.meta_value LIKE %s
                OR u.user_email LIKE %s
            )';

		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		return apply_filters( $this->get_filter( 'search' ), $sql, $this );

	}

}
