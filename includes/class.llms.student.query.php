<?php
/**
* Query LifterLMS Students for a given course / membership
* @since    3.4.0
* @version  3.4.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Student_Query {

	/**
	 * Arguments
	 * Original merged into defaults
	 * @var  array
	 */
	private $arguments = array();

	/**
	 * Default arguments before merging with original
	 * @var  array
	 */
	private $arguments_default = array();

	/**
	 * Original args before merging with defaults
	 * @var  array
	 */
	private $arguments_original = array();

	/**
	 * Total number of students matching query parameters
	 * @var  integer
	 */
	public $found_students = 0;

	/**
	 * Maximum number of pages of results
	 * based off per_page & found_students
	 * @var  integer
	 */
	public $max_pages = 0;

	/**
	 * Number of students on the current page
	 * @var  integer
	 */
	public $number_students = 0;

	/**
	 * Current Page
	 * @var  integer
	 */
	private $page = 1;

	/**
	 * Students per page
	 * @var  integer
	 */
	private $per_page = 25;

	/**
	 * Course / Membership ID
	 * @var  int
	 */
	private $post_id = null;

	/**
	 * Search string
	 * @var  string
	 */
	private $search = '';

	/**
	 * List of fields to sort by
	 * @var  array
	 */
	private $sort = array(
		'date' => 'DESC',
		'status' => 'ASC',
		'last_name' => 'ASC',
		'first_name' => 'ASC',
		'id' => 'ASC',
	);

	/**
	 * The raw SQL query
	 * @var  string
	 */
	private $sql = '';

	/**
	 * If true, all filters will be bypassed
	 * @var  boolean
	 */
	private $suppress_filters = false;

	/**
	 * Enrollment statuses to query by
	 * @var  array
	 */
	private $statuses = array();

	/**
	 * Array of students retrieved by the query
	 * @var  array
	 */
	public $students = array();


	public function __construct( $args = array() ) {

		$this->arguments_original = $args;
		$this->arguments_default = $this->get_default_args();
		$this->arguments = wp_parse_args( $this->arguments_original, $this->arguments_default );

		$this->setup_args();

		$this->query();

	}

	/**
	 * Retrieve default arguments for a student query
	 * @return   array
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	protected function get_default_args() {

		global $post;

		$post_id = ! empty( $post->ID ) ? $post->ID : $this->post_id;

		$args = array(
			'page' => $this->page,
			'per_page' => $this->per_page,
			'post_id' => $post_id,
			'search' => $this->search,
			'sort' => $this->sort,
			'suppress_filters' => $this->suppress_filters,
			'statuses' => array_keys( llms_get_enrollment_statuses() ),
		);

		if ( $this->suppress_filters ) {
			return $args;
		}

		return apply_filters( 'llms_student_query_default_args', $args );

	}

	/**
	 * Get the number of results to skip for the query
	 * based on the current page and per_page vars
	 * @return   int
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	private function get_skip() {
		return absint( ( $this->page - 1 ) * $this->per_page );
	}

	/**
	 * Retrieve an array of LLMS_Students for the given set of students
	 * returned by the query
	 * @return   array
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	public function get_students() {

		$students = array();

		if ( $this->students ) {

			foreach ( $this->students as $student ) {
				$students[] = new LLMS_Student( $student->id );
			}

		}

		if ( $this->suppress_filters ) {
			return $students;
		}

		return apply_filters( 'llms_student_query_get_students', $students );

	}

	/**
	 * Determine if we're on the first page of results
	 * @return   boolean
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	public function is_first_page() {
		return ( 1 === $this->page );
	}

	/**
	 * Determine if we're on the last page of results
	 * @return   boolean
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	public function is_last_page() {
		return ( $this->page === $this->max_pages );
	}

	/**
	 * Prepare the SQL for the query
	 * @return   void
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	private function preprare_query() {

		global $wpdb;

		$vars = array(
			$this->post_id,
			$this->post_id,
		);

		if ( $this->search ) {
			$search = '%' . $wpdb->esc_like( $this->search ) . '%';
			$vars[] = $search;
			$vars[] = $search;
			$vars[] = $search;
		}

		$vars[] = $this->get_skip();
		$vars[] = $this->per_page;

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

		if ( ! $this->suppress_filters ) {
			$sql = apply_filters( 'llms_student_query_prepare_query', $sql, $this );
		}

		$this->sql = $sql;

	}

	/**
	 * Execute a query
	 * @return   void
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	public function query() {

		global $wpdb;

		$this->preprare_query();

		$this->students = $wpdb->get_results( $this->sql );
		$this->number_students = count( $this->students );

		$this->set_found_students();

	}

	/**
	 * Set variables related to total number of results and pages possible
	 * with supplied arguments
	 * @return   void
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	private function set_found_students() {

		global $wpdb;

		// if no students bail early b/c no reason to calculate anything
		if ( ! $this->number_students ) {
			return;
		}

		$this->found_students = absint( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );
		$this->max_pages = absint( ceil( $this->found_students / $this->per_page ) );

	}

	/**
	 * Setup arguments prior to a query
	 * @return   void
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	private function setup_args() {

		foreach ( $this->arguments as $arg => $val ) {

			$this->$arg = $val;

		}

	}

	/**
	 * Retrieve prepared SQL for the HAVING clause
	 * @return   string
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	private function sql_having() {

		global $wpdb;

		$sql = "HAVING status IS NOT NULL";

		$sql .= " AND status IN (";
		$comma = false;
		$statuses = array();
		foreach ( $this->statuses AS $status ) {
			$sql .= $comma ? ", %s" : " %s";
			$statuses[] = $status;
			$comma = true;
		}
		$sql .= " )";
		$sql = $wpdb->prepare( $sql, $statuses );

		if ( $this->suppress_filters ) {
			return $sql;
		}

		return apply_filters( 'llms_student_query_having', $sql, $this );

	}

	/**
	 * Retrieve the prepared SQL for the ORDER clase
	 * @return   string
	 * @since    3.2.2
	 * @version  3.2.2
	 */
	private function sql_orderby() {

		$sql = "ORDER BY";

		$comma = false;

		foreach ( $this->sort as $orderby => $order ) {
			$pre = ( $comma ) ? ", " : " ";
			$sql .= $pre . "{$orderby} {$order}";
			$comma = true;
		}

		if ( $this->suppress_filters ) {
			return $sql;
		}

		return apply_filters( 'llms_student_query_orderby', $sql, $this );

	}

	/**
	 * Retrieve the prepared SEARCH query for the WHERE clause
	 * @return   string
	 * @since    3.4.0
	 * @version  3.4.0
	 */
	private function sql_search() {

		$sql = "";

		if ( $this->search ) {

			global $wpdb;
			$sql .= "  AND (
				   m_last.meta_value LIKE %s
				OR m_first.meta_value LIKE %s
				OR u.user_email LIKE %s
			)";

		}

		if ( $this->suppress_filters ) {
			return $sql;
		}

		return apply_filters( 'llms_student_query_search', $sql, $this );

	}

}
