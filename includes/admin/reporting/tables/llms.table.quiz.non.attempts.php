<?php
/**
 * Quiz Non-Attempts Reporting Table
 *
 * @package LifterLMS/Admin/Reporting/Tables/Classes
 *
 * @since 9.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Quiz_Non_Attempts class.
 *
 * Displays students enrolled in courses containing a quiz but who have not attempted the quiz.
 *
 * @since 9.1.0
 */
class LLMS_Table_Quiz_Non_Attempts extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'quiz_non_attempts';

	/**
	 * Value of the field being filtered by
	 * Only applicable if $filterby is set
	 *
	 * @var  string
	 */
	protected $filter = 'any';

	/**
	 * Field results are filtered by
	 *
	 * @var  string
	 */
	protected $filterby = 'status';

	/**
	 * Is the Table Exportable?
	 *
	 * @var  boolean
	 */
	protected $is_exportable = true;

	/**
	 * Determine if the table is filterable
	 *
	 * @var  boolean
	 */
	protected $is_filterable = true;

	/**
	 * If true, tfoot will add ajax pagination links
	 *
	 * @var  boolean
	 */
	protected $is_paginated = true;

	/**
	 * Determine of the table is searchable
	 *
	 * @var  boolean
	 */
	protected $is_searchable = true;

	/**
	 * Results sort order
	 * 'ASC' or 'DESC'
	 * Only applicable of $orderby is not set
	 *
	 * @var  string
	 */
	protected $order = 'ASC';

	/**
	 * Field results are sorted by
	 *
	 * @var  string
	 */
	protected $orderby = 'name';

	/**
	 * WP Post ID of the displayed quiz
	 *
	 * @var  null
	 */
	protected $quiz_id = null;

	/**
	 * Retrieve data for a cell.
	 *
	 * @since 9.1.0
	 *
	 * @param string       $key     The column id / key.
	 * @param LLMS_Student $student LLMS_Student obj.
	 * @return mixed
	 */
	protected function get_data( $key, $student ) {

		switch ( $key ) {

			case 'id':
				$id = $student->get_id();
				if ( current_user_can( 'edit_users', $id ) ) {
					$value = '<a href="' . esc_url( get_edit_user_link( $id ) ) . '">' . $id . '</a>';
				} else {
					$value = $id;
				}
				break;

			case 'name':
				$first = $student->get( 'first_name' );
				$last  = $student->get( 'last_name' );

				if ( ! $first || ! $last ) {
					$value = $student->get( 'display_name' );
				} else {
					$value = $last . ', ' . $first;
				}

				$id = $student->get_id();
				if ( current_user_can( 'edit_users', $id ) ) {
					$value = '<a href="' . esc_url( get_edit_user_link( $id ) ) . '">' . $value . '</a>';
				}
				break;

			case 'email':
				$value = $student->get( 'user_email' );
				break;

			case 'enrolled_courses':
				$quiz   = llms_get_post( $this->quiz_id );
				$course = $quiz ? $quiz->get_course() : false;

				if ( $course ) {
					$enrollment_date = $student->get_enrollment_date( $course->get( 'id' ) );
					$value           = $enrollment_date ? $enrollment_date : '&mdash;';
				} else {
					$value = '&mdash;';
				}
				break;

			case 'status':
				$quiz   = llms_get_post( $this->quiz_id );
				$course = $quiz ? $quiz->get_course() : false;

				if ( $course ) {
					$status = $student->get_enrollment_status( $course->get( 'id' ) );
					$value  = ucfirst( $status );
				} else {
					$value = '&mdash;';
				}
				break;

			default:
				$value = $key;

		}// End switch().

		return $value;
	}

	/**
	 * Execute a query to retrieve results from the table.
	 *
	 * @since 9.1.0
	 *
	 * @param array $args Array of query args.
	 * @return void
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Students Without Quiz Attempts', 'lifterlms' );

		$args = $this->clean_args( $args );

		$this->quiz_id = $args['quiz_id'];

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$per = apply_filters( 'llms_reporting_' . $this->id . '_per_page', 25 );

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->order;
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->orderby;

		$this->filter   = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		if ( ! ( current_user_can( 'view_others_lifterlms_reports' ) || ( current_user_can( 'view_lifterlms_reports' ) && current_user_can( 'edit_post', $args['quiz_id'] ) ) ) ) {
			return;
		}

		$quiz = llms_get_post( $this->quiz_id );
		if ( ! $quiz ) {
			return;
		}

		$course = $quiz->get_course();
		if ( ! $course ) {
			return;
		}

		// Use a single optimized database query.
		global $wpdb;

		$search_sql = '';
		if ( isset( $args['search'] ) && ! empty( $args['search'] ) ) {
			$search_term = sanitize_text_field( $args['search'] );
			$search_sql  = $wpdb->prepare(
				'AND (
					u.user_login LIKE %s
					OR u.user_email LIKE %s
					OR u.display_name LIKE %s
					OR m_first.meta_value LIKE %s
					OR m_last.meta_value LIKE %s
				)',
				'%' . $search_term . '%',
				'%' . $search_term . '%',
				'%' . $search_term . '%',
				'%' . $search_term . '%',
				'%' . $search_term . '%'
			);
		}

		if ( 'any' !== $this->filter ) {
			$status_sql = $wpdb->prepare( 'AND upm.meta_value = %s', $this->filter );
		} else {
			$status_sql = "AND upm.meta_value IN ('enrolled', 'expired', 'cancelled')";
		}

		switch ( $this->orderby ) {
			case 'name':
				$order_sql = 'ORDER BY m_last.meta_value ' . $this->order . ', m_first.meta_value ' . $this->order;
				break;
			case 'id':
				$order_sql = 'ORDER BY u.ID ' . $this->order;
				break;
			case 'email':
				$order_sql = 'ORDER BY u.user_email ' . $this->order;
				break;
			default:
				$order_sql = 'ORDER BY u.display_name ' . $this->order;
		}

		$offset = ( $this->current_page - 1 ) * $per;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS DISTINCT
					u.ID as user_id,
					u.user_email,
					u.display_name,
					u.user_registered,
					m_first.meta_value as first_name,
					m_last.meta_value as last_name,
					upm.meta_value as enrollment_status,
					upm.updated_date as enrollment_date
				FROM {$wpdb->users} u
				INNER JOIN {$wpdb->prefix}lifterlms_user_postmeta upm
					ON u.ID = upm.user_id
					AND upm.post_id = %d
					AND upm.meta_key = '_status'
					{$status_sql}
				LEFT JOIN {$wpdb->usermeta} m_first
					ON u.ID = m_first.user_id
					AND m_first.meta_key = 'first_name'
				LEFT JOIN {$wpdb->usermeta} m_last
					ON u.ID = m_last.user_id
					AND m_last.meta_key = 'last_name'
				LEFT JOIN {$wpdb->prefix}lifterlms_quiz_attempts qa
					ON u.ID = qa.student_id
					AND qa.quiz_id = %d
				WHERE upm.updated_date = (
					SELECT MAX(upm2.updated_date)
					FROM {$wpdb->prefix}lifterlms_user_postmeta upm2
					WHERE upm2.user_id = u.ID
					AND upm2.post_id = %d
					AND upm2.meta_key = '_status'
				)
				AND qa.id IS NULL
				{$search_sql}
				{$order_sql}
				LIMIT %d, %d",
				$course->get( 'id' ),    // Course ID for enrollment check
				$this->quiz_id,          // Quiz ID for attempt check
				$course->get( 'id' ),    // Course ID for latest enrollment status
				$offset,
				$per
			)
		);

		$total_results = $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		$this->max_pages    = ceil( $total_results / $per );
		$this->is_last_page = ( $this->current_page >= $this->max_pages );

		$this->tbody_data = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$this->tbody_data[] = llms_get_student( $result->user_id );
			}
		}
	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @since 9.1.0
	 *
	 * @return array
	 */
	public function set_args() {
		return array(
			'quiz_id' => ! empty( $this->quiz_id ) ? $this->quiz_id : ( isset( $_GET['quiz_id'] ) ? absint( $_GET['quiz_id'] ) : null ),
		);
	}

	/**
	 * Define the structure of the table
	 *
	 * @return array
	 * @since 9.1.0
	 */
	protected function set_columns() {

		$cols = array(
			'id'               => array(
				'exportable' => true,
				'title'      => __( 'ID', 'lifterlms' ),
				'sortable'   => true,
			),
			'name'             => array(
				'exportable' => true,
				'title'      => __( 'Name', 'lifterlms' ),
				'sortable'   => true,
			),
			'email'            => array(
				'exportable' => true,
				'title'      => __( 'Email', 'lifterlms' ),
				'sortable'   => true,
			),
			'enrolled_courses' => array(
				'exportable' => true,
				'title'      => __( 'Enrollment Date', 'lifterlms' ),
				'sortable'   => false,
			),
			'status'           => array(
				'filterable' => array(
					'enrolled'  => __( 'Enrolled', 'lifterlms' ),
					'expired'   => __( 'Expired', 'lifterlms' ),
					'cancelled' => __( 'Cancelled', 'lifterlms' ),
				),
				'exportable' => true,
				'title'      => __( 'Status', 'lifterlms' ),
				'sortable'   => false,
			),
		);

		return $cols;
	}
}
