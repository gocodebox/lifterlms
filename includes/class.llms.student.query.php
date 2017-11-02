<?php
/**
* Query LifterLMS Students for a given course / membership
* @since    3.4.0
* @version  3.13.0
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
	 * @version  3.13.0
	 */
	protected function get_default_args() {

		global $post;

		$post_id = ! empty( $post->ID ) ? $post->ID : array();

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
	 * @version  3.8.0
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
	 * @version  3.13.0
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

		// allow numeric strings & ints to be passed instead of an array
		$post_ids = $this->arguments['post_id'];
		if ( ! is_array( $post_ids ) && is_numeric( $post_ids ) && $post_ids > 0 ) {
			$post_ids = array( $post_ids );
		}

		foreach ( $post_ids as $key => &$id ) {
			$id = absint( $id ); // verify we have ints
			if ( $id <= 0 ) { // remove anything negative or 0
				unset( $post_ids[ $key ] );
			}
		}
		$this->arguments['post_id'] = $post_ids;

	}

	/**
	 * Prepare the SQL for the query
	 * @return   void
	 * @since    3.4.0
	 * @version  3.13.0
	 */
	protected function preprare_query() {

		global $wpdb;

		$vars = array();

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
			{$this->sql_select()}
			FROM {$wpdb->users} AS u
			{$this->sql_joins()}
			{$this->sql_search()}
			{$this->sql_having()}
			{$this->sql_orderby()}
			LIMIT %d, %d;",
			$vars
		);

		return $sql;

	}

	/**
	 * Determines if a field should be selected/joined based on searching and sorting arguments
	 * @param    string     $field  field name/key
	 * @return   bool
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	private function requires_field( $field ) {

		// get the fields we're sorting by to see if we need to select them for the sorting
		$sort_fields = array_keys( $this->get( 'sort' ) );

		if ( in_array( $field, $sort_fields ) ) {
			return true;
		}

		if ( $this->get( 'search' ) ) {

			$search_fields = array( 'last_name', 'first_name', 'user_email' );
			if ( in_array( $field, $search_fields ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieve prepared SQL for the HAVING clause
	 * @return   string
	 * @since    3.4.0
	 * @version  3.13.0
	 */
	private function sql_having() {

		global $wpdb;

		$sql = "HAVING status IS NOT NULL AND {$this->sql_status_in()}";

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		return apply_filters( $this->get_filter( 'having' ), $sql, $this );

	}

	/**
	 * Setup joins based on submitted sort and search args
	 * @return   string
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	private function sql_joins() {

		global $wpdb;

		$joins = array();

		$fields = array(
			'first_name' => "JOIN {$wpdb->usermeta} AS m_first ON u.ID = m_first.user_id AND m_first.meta_key = 'first_name'",
			'last_name' => "JOIN {$wpdb->usermeta} AS m_last ON u.ID = m_last.user_id AND m_last.meta_key = 'last_name'",
			'overall_progress' => "JOIN {$wpdb->usermeta} AS m_o_p ON u.ID = m_o_p.user_id AND m_o_p.meta_key = 'llms_overall_progress'",
			'overall_grade' => "JOIN {$wpdb->usermeta} AS m_o_g ON u.ID = m_o_g.user_id AND m_o_g.meta_key = 'llms_overall_grade'",
		);

		// add the fields to the array of fields to select
		foreach ( $fields as $key => $statment ) {
			if ( $this->requires_field( $key ) ) {
				$joins[] = $statment;
			}
		}

		$sql = implode( ' ', $joins );

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		return apply_filters( $this->get_filter( 'join' ), $sql, $this );

	}

	/**
	 * Retrieve the prepared SEARCH query for the WHERE clause
	 * @return   string
	 * @since    3.4.0
	 * @version  3.8.0
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

	/**
	 * Setup the SQL for the select statement
	 * @return   string
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	private function sql_select() {

		$selects = array();

		// always select the ID
		$selects[] = 'u.ID as id';

		// always add the subqueries for enrollment status
		$selects[] = "( {$this->sql_subquery( 'meta_value' )} ) AS status";

		// all the possilbe fields
		$fields = array(
			'date' => "( {$this->sql_subquery( 'updated_date' )} ) AS `date`",
			'last_name' => 'm_last.meta_value AS last_name',
			'first_name' => 'm_last.meta_value AS first_name',
			'email' => 'u.user_email AS email',
			'registered' => 'u.user_registered AS registered',
			'overall_progress' => 'CAST( m_o_p.meta_value AS decimal( 5, 2 ) ) AS overall_progress',
			'overall_grade' => 'CAST( m_o_g.meta_value AS decimal( 5, 2 ) ) AS overall_grade',
		);

		// add the fields to the array of fields to select
		foreach ( $fields as $key => $statment ) {
			if ( $this->requires_field( $key ) ) {
				$selects[] = $statment;
			}
		}

		$sql = implode( ', ', $selects );

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		return apply_filters( $this->get_filter( 'select' ), $sql, $this );

	}

	/**
	 * Generate an SQL IN clause based on submitted status arguements
	 * @param    string     $column  name of the columen
	 * @return   string
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	private function sql_status_in( $column = 'status' ) {
		global $wpdb;
		$comma = false;
		$statuses = array();
		$sql = '';
		foreach ( $this->get( 'statuses' ) as $status ) {
			$sql .= $comma ? ',%s' : '%s';
			$statuses[] = $status;
			$comma = true;
		}

		$sql = $wpdb->prepare( $sql, $statuses );
		return "{$column} IN ( {$sql} )";

	}

	/**
	 * Generate an SQL subquery for the dynamic status or date values in the main query
	 * @param    string     $column  column name
	 * @return   string
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	private function sql_subquery( $column ) {

		$and = '';

		$post_ids = $this->get( 'post_id' );
		if ( $post_ids ) {
			$post_ids = implode( ',', $post_ids );
			$and = "AND post_id IN ( {$post_ids} )";
		} else {
			$and = "AND {$this->sql_status_in( 'meta_value' )}";
		}

		global $wpdb;

		return "SELECT {$column}
				FROM {$wpdb->prefix}lifterlms_user_postmeta
				WHERE meta_key = '_status'
		  		  AND user_id = id
		  		  {$and}
				ORDER BY updated_date DESC
				LIMIT 1";
	}

}
