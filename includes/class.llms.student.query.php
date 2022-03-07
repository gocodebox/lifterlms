<?php
/**
 * Query LifterLMS Students for a given course / membership
 *
 * @package LifterLMS/Classes
 *
 * @since 3.4.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Student_Query
 *
 * @since 3.4.0
 * @since 3.13.0 Unknown.
 */
class LLMS_Student_Query extends LLMS_Database_Query {

	/**
	 * Identify the extending query
	 *
	 * @var string
	 */
	protected $id = 'student';

	/**
	 * Retrieve default arguments for a student query
	 *
	 * @since 3.4.0
	 * @since 4.10.2 Drop usage of `this->get_filter( 'default_args' )` in favor of `'llms_student_query_default_args'`.
	 *
	 * @return array
	 */
	protected function get_default_args() {

		global $post;

		$post_id = ! empty( $post->ID ) ? $post->ID : array();

		$args = array(
			'post_id'  => $post_id,
			'sort'     => array(
				'date'       => 'DESC',
				'status'     => 'ASC',
				'last_name'  => 'ASC',
				'first_name' => 'ASC',
				'id'         => 'ASC',
			),
			'statuses' => array_keys( llms_get_enrollment_statuses() ),
		);

		$args = wp_parse_args( $args, parent::get_default_args() );

		/**
		 * Filters the student query default args
		 *
		 * @since 3.4.0
		 *
		 * @param array              $args          Array of default arguments to set up the query with.
		 * @param LLMS_Student_Query $student_query Instance of LLMS_Student_Query.
		 */
		return apply_filters( 'llms_student_query_default_args', $args, $this );

	}

	/**
	 * Retrieve an array of LLMS_Students for the given set of students returned by the query
	 *
	 * @since 3.4.0
	 * @since 3.8.0 Unknown.
	 * @since 4.10.2 Drop usage of `this->get_filter( 'get_students' )` in favor of `'llms_student_query_get_students'`.
	 *
	 * @return array
	 */
	public function get_students() {

		$students = array();
		$results  = $this->get_results();

		if ( $results ) {

			foreach ( $results as $result ) {
				$students[] = new LLMS_Student( $result->id );
			}
		}

		if ( $this->get( 'suppress_filters' ) ) {
			return $students;
		}

		/**
		 * Filters the list of students
		 *
		 * @since Unknown
		 * @since 4.10.2 Pass this query instance as second parameter.
		 *
		 * @param LLMS_Student[]     $students      Array of LLMS_Student instances.
		 * @param LLMS_Student_Query $student_query Instance of LLMS_Student_Query.
		 */
		return apply_filters( 'llms_student_query_get_students', $students, $this );

	}

	/**
	 * Parses data passed to $statuses
	 *
	 * Convert strings to array and ensure resulting array contains only valid statuses
	 * If no valid statuses, returns to the default.
	 *
	 * @since 3.4.0
	 * @since 3.13.0 Unknown.
	 *
	 * @return void
	 */
	protected function parse_args() {

		$statuses = $this->arguments['statuses'];

		// Allow strings to be submitted when only requesting one status.
		if ( is_string( $statuses ) ) {
			$statuses = array( $statuses );
		}

		// Ensure only valid statuses are used.
		$statuses = array_intersect( $statuses, array_keys( llms_get_enrollment_statuses() ) );

		// No statuses should return original default.
		if ( ! $statuses ) {
			$statuses = array_keys( llms_get_enrollment_statuses() );
		}

		$this->arguments['statuses'] = $statuses;

		// Allow numeric strings & ints to be passed instead of an array.
		$post_ids = $this->arguments['post_id'];
		if ( ! is_array( $post_ids ) && is_numeric( $post_ids ) && $post_ids > 0 ) {
			$post_ids = array( $post_ids );
		}

		foreach ( $post_ids as $key => &$id ) {
			$id = absint( $id ); // Verify we have ints.
			if ( $id <= 0 ) { // Remove anything negative or 0.
				unset( $post_ids[ $key ] );
			}
		}
		$this->arguments['post_id'] = $post_ids;

	}

	/**
	 * Prepare the SQL for the query.
	 *
	 * @since 3.4.0
	 * @since 3.13.0 Unknown.
	 * @since 4.10.2 Demands to `$this->sql_select()` to determine whether or not `SQL_CALC_FOUND_ROWS` statement is needed.
	 * @since 6.0.0 Renamed from `preprare_query()`.
	 *
	 * @return string
	 */
	protected function prepare_query() {

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

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- $vars is an array with the correct number of items.
		$sql = $wpdb->prepare(
			"SELECT {$this->sql_select()}
			FROM {$wpdb->users} AS u
			{$this->sql_joins()}
			{$this->sql_search()}
			{$this->sql_having()}
			{$this->sql_orderby()}
			LIMIT %d, %d;",
			$vars
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $sql;

	}

	/**
	 * Determines if a field should be selected/joined based on searching and sorting arguments
	 *
	 * @since 3.13.0
	 *
	 * @param string $field Field name/key.
	 * @return bool
	 */
	private function requires_field( $field ) {

		// Get the fields we're sorting by to see if we need to select them for the sorting.
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
	 *
	 * @since 3.4.0
	 * @since 3.13.0 Unknown.
	 * @since 4.10.2 Drop usage of `this->get_filter( 'having' )` in favor of `'llms_student_query_having'`.
	 *
	 * @return string
	 */
	private function sql_having() {

		global $wpdb;

		$sql = "HAVING status IS NOT NULL AND {$this->sql_status_in()}";

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		/**
		 * Filters the query HAVING clause
		 *
		 * @since Unknown
		 *
		 * @param string             $sql           The HAVING clause of the query.
		 * @param LLMS_Student_Query $student_query Instance of LLMS_Student_Query.
		 */
		return apply_filters( 'llms_student_query_having', $sql, $this );
	}

	/**
	 * Setup joins based on submitted sort and search args
	 *
	 * @since 3.13.0
	 * @since 4.10.2 Drop usage of `this->get_filter( 'join' )` in favor of `'llms_student_query_join'`.
	 *
	 * @return string
	 */
	private function sql_joins() {

		global $wpdb;

		$joins = array();

		$fields = array(
			'first_name'       => "JOIN {$wpdb->usermeta} AS m_first ON u.ID = m_first.user_id AND m_first.meta_key = 'first_name'",
			'last_name'        => "JOIN {$wpdb->usermeta} AS m_last ON u.ID = m_last.user_id AND m_last.meta_key = 'last_name'",
			'overall_progress' => "JOIN {$wpdb->usermeta} AS m_o_p ON u.ID = m_o_p.user_id AND m_o_p.meta_key = 'llms_overall_progress'",
			'overall_grade'    => "JOIN {$wpdb->usermeta} AS m_o_g ON u.ID = m_o_g.user_id AND m_o_g.meta_key = 'llms_overall_grade'",
		);

		// Add the fields to the array of fields to select.
		foreach ( $fields as $key => $statement ) {
			if ( $this->requires_field( $key ) ) {
				$joins[] = $statement;
			}
		}

		$sql = implode( ' ', $joins );

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		/**
		 * Filters the query JOIN clause
		 *
		 * @since 3.13.0
		 *
		 * @param string             $sql           The JOIN clause of the query.
		 * @param LLMS_Student_Query $student_query Instance of LLMS_Student_Query.
		 */
		return apply_filters( 'llms_student_query_join', $sql, $this );

	}

	/**
	 * Retrieve the prepared SEARCH query for the WHERE clause
	 *
	 * @since 3.4.0
	 * @since 3.8.0 Unknown.
	 * @since 4.10.2 Drop usage of `this->get_filter( 'search' )` in favor of `'llms_student_query_search'`.
	 *
	 * @return string
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

		/**
		 * Filters the part of the SQL query that performs the search.
		 *
		 * @since Unknown
		 *
		 * @param string             $sql           The SQL part that performs the search.
		 * @param LLMS_Student_Query $student_query Instance of LLMS_Student_Query.
		 */
		return apply_filters( 'llms_student_query_search', $sql, $this );

	}

	/**
	 * Set up the SQL for the select statement.
	 *
	 * @since 3.13.0
	 * @since 4.10.2 Drop usage of `this->get_filter( 'select' )` in favor of `'llms_student_query_select'`.
	 *               Use `$this->sql_select_columns({columns})` to determine additional columns to select.
	 * @since 5.10.0 Add a subquery for completed date.
	 *
	 * @return string
	 */
	private function sql_select() {

		$selects = array();

		// Always select the ID.
		$selects[] = 'u.ID as id';

		// Always add the subqueries for enrollment status.
		$selects[] = "( {$this->sql_subquery( 'meta_value' )} ) AS status";

		// All the possible fields.
		$fields = array(
			'completed'        => "( {$this->sql_subquery( 'updated_date', '_is_complete' )} ) AS completed",
			'date'             => "( {$this->sql_subquery( 'updated_date' )} ) AS `date`",
			'last_name'        => 'm_last.meta_value AS last_name',
			'first_name'       => 'm_first.meta_value AS first_name',
			'email'            => 'u.user_email AS email',
			'registered'       => 'u.user_registered AS registered',
			'overall_progress' => 'CAST( m_o_p.meta_value AS decimal( 5, 2 ) ) AS overall_progress',
			'overall_grade'    => 'CAST( m_o_g.meta_value AS decimal( 5, 2 ) ) AS overall_grade',
		);

		// Add the fields to the array of fields to select.
		foreach ( $fields as $key => $statement ) {
			if ( $this->requires_field( $key ) ) {
				$selects[] = $statement;
			}
		}

		$sql = implode( ', ', $selects );
		$sql = $this->sql_select_columns( $sql );

		if ( $this->get( 'suppress_filters' ) ) {
			return $sql;
		}

		/**
		 * Filters the query SELECT clause
		 *
		 * @since 3.13.0
		 *
		 * @param string             $sql           The SELECT clause of the query.
		 * @param LLMS_Student_Query $student_query Instance of LLMS_Student_Query.
		 */
		return apply_filters( 'llms_student_query_select', $sql, $this );

	}

	/**
	 * Generate an SQL IN clause based on submitted status arguments
	 *
	 * @since 3.13.0
	 *
	 * @param string $column Name of the column.
	 * @return string
	 */
	private function sql_status_in( $column = 'status' ) {
		global $wpdb;
		$comma    = false;
		$statuses = array();
		$sql      = '';
		foreach ( $this->get( 'statuses' ) as $status ) {
			$sql       .= $comma ? ',%s' : '%s';
			$statuses[] = $status;
			$comma      = true;
		}

		$sql = $wpdb->prepare( $sql, $statuses ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return "{$column} IN ( {$sql} )";

	}

	/**
	 * Generate an SQL subquery for the meta key in the main query.
	 *
	 * @since 3.13.0
	 * @since 5.10.0 Add `$meta_key` argument.
	 *
	 * @param string $column   Column name.
	 * @param string $meta_key Optional meta key to use in the WHERE condition. Defaults to '_status'.
	 * @return string
	 */
	private function sql_subquery( $column, $meta_key = '_status' ) {

		global $wpdb;

		$post_ids = $this->get( 'post_id' );
		if ( $post_ids ) {
			$post_ids = implode( ',', $post_ids );
			$and      = "AND post_id IN ( {$post_ids} )";
		} else {
			$and = "AND {$this->sql_status_in( 'meta_value' )}";
		}

		return "SELECT {$column}
				FROM {$wpdb->prefix}lifterlms_user_postmeta
				WHERE meta_key = '{$meta_key}'
		  		  AND user_id = id
		  		  {$and}
				ORDER BY updated_date DESC
				LIMIT 1";
	}

}
