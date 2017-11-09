<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Display students enrolled in a given course on the course students subtab
 * @since   [version]
 * @version [version]
 */
class LLMS_Table_Course_Students extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	protected $id = 'course-students';

	/**
	 * Value of the field being filtered by
	 * Only applicable if $filterby is set
	 * @var  string
	 */
	protected $filter = 'any';

	/**
	 * Field results are filtered by
	 * @var  string
	 */
	protected $filterby = 'status';

	/**
	 * Determine if the table is filterable
	 * @var  boolean
	 */
	protected $is_filterable = true;

	/**
	 * If true, tfoot will add ajax pagination links
	 * @var  boolean
	 */
	protected $is_paginated = true;

	/**
	 * Determine of the table is searchable
	 * @var  boolean
	 */
	protected $is_searchable = true;

	/**
	 * Results sort order
	 * 'ASC' or 'DESC'
	 * Only applicable of $orderby is not set
	 * @var  string
	 */
	protected $order = 'ASC';

	/**
	 * Field results are sorted by
	 * @var  string
	 */
	protected $orderby = 'name';

	/**
	 * Post ID for the current table
	 * @var  int
	 */
	protected $course_id = null;

	/**
	 * Retrieve data for the columns
	 * @param    string     $key        the column id / key
	 * @param    int        $user_id    WP User ID
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_data( $key, $student ) {

		$value = '';

		switch ( $key ) {

			case 'enrolled':
				$value = $student->get_enrollment_date( $this->course_id, 'updated' );
			break;

			case 'grade':
				$value = $student->get_grade( $this->course_id );
				if ( is_numeric( $value ) ) {
					$value .= '%';
				}
			break;

			case 'id':
				$id = $student->get_id();
				if ( current_user_can( 'edit_users', $id ) ) {
					$value = '<a href="' . esc_url( get_edit_user_link( $id ) ) . '">' . $id . '</a>';
				} else {
					$value = $id;
				}
			break;

			case 'last_lesson':
				$lid = $student->get_last_completed_lesson( $this->course_id );
				if ( $lid ) {
					$value = $this->get_post_link( $lid, llms_trim_string( get_the_title( $lid ), 30 ) );
				} else {
					$value = '&ndash;';
				}
			break;

			case 'name':

				$first = $student->get( 'first_name' );
				$last = $student->get( 'last_name' );

				if ( ! $first || ! $last ) {
					$value = $student->get( 'display_name' );
				} else {
					$value = $last . ', ' . $first;
				}

				$url = add_query_arg( array(
					'page' => 'llms-reporting',
					'tab' => 'students',
					'student_id' => $student->get_id(),
					'stab' => 'courses',
					'course_id' => 5202,
				), admin_url( 'admin.php' ) );
				$value = '<a href="' . esc_url( $url ) . '">' . $value . '</a>';

			break;

			case 'progress':
				$value = $this->get_progress_bar_html( $student->get_progress( $this->course_id ) );
			break;

			case 'status':
				$value = llms_get_enrollment_status_name( $student->get_enrollment_status( $this->course_id ) );
			break;

			case 'trigger':
				$trigger = $student->get_enrollment_trigger( $this->course_id );
				if ( $trigger && false !== strpos( $trigger, 'order_' ) ) {
					$tid = $student->get_enrollment_trigger_id( $this->course_id );
					$value = $this->get_post_link( $tid, sprintf( __( 'Order #%d', 'lifterlms' ), $tid ) );
				} else {
					$value = $trigger;
				}
			break;

			default:
				$value = $key;

		}// End switch().

		return $this->filter_get_data( $value, $key, $student );

	}

	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_reporting_get_' . $this->id . '_search_placeholder', __( 'Search students by name or email...', 'lifterlms' ) );
	}

	/**
	 * Execute a query to retrieve results from the table
	 * @param    array      $args  array of query args
	 * @return   void
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Students', 'lifterlms' );

		if ( ! $args ) {
			$args = $this->get_args();
		}

		$args = $this->clean_args( $args );

		$this->course_id = $args['course_id'];

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$this->filter = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		$this->order = isset( $args['order'] ) ? $args['order'] : $this->get_order();
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->get_orderby();

		$sort = array();
		switch ( $this->get_orderby() ) {

			case 'enrolled':
				$sort = array(
					'date' => $this->get_order(),
					'last_name' => 'ASC',
					'first_name' => 'ASC',
					'id' => 'ASC',
				);
			break;

			case 'id':
				$sort = array(
					'id' => $this->get_order(),
				);
			break;

			case 'name':
				$sort = array(
					'last_name' => $this->get_order(),
					'first_name' => 'ASC',
					'id' => 'ASC',
				);
			break;

			case 'status':
				$sort = array(
					'status' => $this->get_order(),
					'last_name' => 'ASC',
					'first_name' => 'ASC',
					'id' => 'ASC',
				);
			break;

		}

		$query_args = array(
			'page' => $this->get_current_page(),
			'post_id' => $args['course_id'],
			'per_page' => apply_filters( 'llms_' . $this->id . '_table_students_per_page', 25 ),
			'sort' => $sort,
		);

		if ( 'status' === $this->get_filterby() && 'any' !== $this->get_filter() ) {

			$query_args['statuses'] = array( $this->get_filter() );

		}

		if ( isset( $args['search'] ) ) {

			$this->search = $args['search'];
			$query_args['search'] = $this->get_search();

		}

		$query = new LLMS_Student_Query( $query_args );

		$this->max_pages = $query->max_pages;
		$this->is_last_page = $query->is_last_page();

		$this->tbody_data = $query->get_students();

	}


	/**
	 * Define the structure of arguments used to pass to the get_results method
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function set_args() {

		if ( ! $this->course_id ) {
			$this->course_id = ! empty( $_GET['course_id'] ) ? absint( $_GET['course_id'] ) : null;
		}

		return array(
			'course_id' => $this->course_id,
		);

	}

	/**
	 * Define the structure of the table
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function set_columns() {
		$cols = array(
			'id' => array(
				'sortable' => true,
				'title' => __( 'ID', 'lifterlms' ),
			),
			'name' => array(
				'sortable' => true,
				'title' => __( 'Name', 'lifterlms' ),
			),
			'status' => array(
				'filterable' => llms_get_enrollment_statuses(),
				'sortable' => true,
				'title' => __( 'Status', 'lifterlms' ),
			),
			'enrolled' => array(
				'sortable' => true,
				'title' => __( 'Enrollment Updated', 'lifterlms' ),
			),
			'progress' => array(
				'sortable' => false,
				'title' => __( 'Progress', 'lifterlms' ),
			),
			'grade' => array(
				'sortable' => false,
				'title' => __( 'Grade', 'lifterlms' ),
			),
			'last_lesson' => array(
				'sortable' => false,
				'title' => __( 'Last Lesson', 'lifterlms' ),
			),
		);

		return $cols;
	}

}
