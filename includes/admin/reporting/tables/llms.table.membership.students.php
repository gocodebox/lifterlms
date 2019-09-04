<?php
/**
 * Display students enrolled in a given membership on the membership students subtab.
 *
 * @since   3.32.0
 * @version 3.32.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * Display students enrolled in a given membership on the membership students subtab class.
 *
 * @since   3.32.0
 */
class LLMS_Table_Membership_Students extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @since   3.32.0
	 *
	 * @var  string
	 */
	protected $id = 'membership-students';

	/**
	 * Value of the field being filtered by
	 * Only applicable if $filterby is set.
	 *
	 * @since   3.32.0
	 *
	 * @var  string
	 */
	protected $filter = 'any';

	/**
	 * Field results are filtered by.
	 *
	 * @since   3.32.0
	 *
	 * @var  string
	 */
	protected $filterby = 'status';

	/**
	 * Is the Table Exportable?
	 *
	 * @since   3.32.0
	 *
	 * @var  boolean
	 */
	protected $is_exportable = true;


	/**
	 *
	 * @since   3.32.0
	 *
	 * Determine if the table is filterable.
	 * @var  boolean
	 */
	protected $is_filterable = true;

	/**
	 * If true, tfoot will add ajax pagination links.
	 *
	 * @since   3.32.0
	 *
	 * @var  boolean
	 */
	protected $is_paginated = true;

	/**
	 * Determine of the table is searchable.
	 *
	 * @since   3.32.0
	 *
	 * @var  boolean
	 */
	protected $is_searchable = true;

	/**
	 * Results sort order 'ASC' or 'DESC'.
	 * Only applicable of $orderby is not set.
	 *
	 * @since   3.32.0
	 *
	 * @var  string
	 */
	protected $order = 'ASC';

	/**
	 * Field results are sorted by.
	 *
	 * @since   3.32.0
	 *
	 * @var  string
	 */
	protected $orderby = 'name';

	/**
	 * Post ID for the current table.
	 *
	 * @since   3.32.0
	 *
	 * @var  int
	 */
	public $membership_id = null;

	/**
	 * Retrieve data for the columns
	 *
	 * @since 3.32.0
	 *
	 * @param string $key     The column id / key.
	 * @param int    $user_id WP User ID.
	 * @return mixed
	 */
	public function get_data( $key, $student ) {

		$value = '';

		switch ( $key ) {

			case 'enrolled':
				$value = $student->get_enrollment_date( $this->membership_id, 'updated' );
				break;

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

				$url   = add_query_arg(
					array(
						'page'          => 'llms-reporting',
						'tab'           => 'students',
						'student_id'    => $student->get_id(),
						'stab'          => 'memberships',
						'membership_id' => $this->membership_id,
					),
					admin_url( 'admin.php' )
				);
				$value = '<a href="' . esc_url( $url ) . '">' . $value . '</a>';

				break;

			case 'status':
				$value = llms_get_enrollment_status_name( $student->get_enrollment_status( $this->membership_id ) );
				break;

			default:
				$value = $key;

		}// End switch().

		return $this->filter_get_data( $value, $key, $student );

	}

	/**
	 * Retrieve data for a cell in an export file.
	 * Should be overridden in extending classes.
	 *
	 * @since 3.32.0
	 *
	 * @param string $key     The column id / key.
	 * @param obj    $student Instance of the LLMS_Student.
	 * @return mixed
	 */
	public function get_export_data( $key, $student ) {

		switch ( $key ) {

			case 'id':
				$value = $student->get_id();
				break;

			case 'email':
				$value = $student->get( 'user_email' );
				break;

			case 'name_first':
				$value = $student->get( 'first_name' );
				break;

			case 'name_last':
				$value = $student->get( 'last_name' );
				break;

			default:
				$value = $this->get_data( $key, $student );

		}// End switch().

		return $this->filter_get_data( $value, $key, $student, 'export' );

	}


	/**
	 * Get a lock key unique to the table & user for locking the table during export generation.
	 *
	 * @since 3.32.0
	 *
	 * @return string
	 */
	public function get_export_lock_key() {
		$args = $this->get_args();
		return sprintf( '%1$s:%2$d:%3$d', $this->id, get_current_user_id(), $args['membership_id'] );
	}

	/**
	 * Allow customization of the title for export files.
	 *
	 * @since 3.32.0
	 *
	 * @param  array $args Optional. Array of arguments passed from table to csv processor. Default empty array.
	 * @return string
	 */
	public function get_export_title( $args = array() ) {
		$title = $this->get_title();
		if ( isset( $args['membership_id'] ) ) {
			$title = get_the_title( $args['membership_id'] ) . ' ' . $title;
		}
		return apply_filters( 'llms_table_get_' . $this->id . '_export_title', $title );
	}

	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input.
	 *
	 * @since 3.32.0
	 *
	 * @return string
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_reporting_get_' . $this->id . '_search_placeholder', __( 'Search students by name or email...', 'lifterlms' ) );
	}

	/**
	 * Execute a query to retrieve results from the table.
	 *
	 * @since 3.32.0
	 *
	 * @param array $args Optional. Array of query args. Default empty array.
	 * @return void
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Students', 'lifterlms' );

		if ( ! $args ) {
			$args = $this->get_args();
		}

		$args = $this->clean_args( $args );

		$this->membership_id = $args['membership_id'];

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$this->filter   = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->get_order();
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->get_orderby();

		$sort = array();
		switch ( $this->get_orderby() ) {

			case 'enrolled':
				$sort = array(
					'date'       => $this->get_order(),
					'last_name'  => 'ASC',
					'first_name' => 'ASC',
					'id'         => 'ASC',
				);
				break;

			case 'id':
				$sort = array(
					'id' => $this->get_order(),
				);
				break;

			case 'name':
				$sort = array(
					'last_name'  => $this->get_order(),
					'first_name' => 'ASC',
					'id'         => 'ASC',
				);
				break;

			case 'status':
				$sort = array(
					'status'     => $this->get_order(),
					'last_name'  => 'ASC',
					'first_name' => 'ASC',
					'id'         => 'ASC',
				);
				break;

		}

		$query_args = array(
			'page'     => $this->get_current_page(),
			'post_id'  => $args['membership_id'],
			'per_page' => apply_filters( 'llms_' . $this->id . '_table_students_per_page', 25 ),
			'sort'     => $sort,
		);

		if ( 'status' === $this->get_filterby() && 'any' !== $this->get_filter() ) {

			$query_args['statuses'] = array( $this->get_filter() );

		}

		if ( isset( $args['search'] ) ) {

			$this->search         = $args['search'];
			$query_args['search'] = $this->get_search();

		}

		$query = new LLMS_Student_Query( $query_args );

		$this->max_pages    = $query->max_pages;
		$this->is_last_page = $query->is_last_page();

		$this->tbody_data = $query->get_students();

	}


	/**
	 * Define the structure of arguments used to pass to the get_results method.
	 *
	 * @since 3.32.0
	 *
	 * @return array
	 */
	public function set_args() {

		if ( ! $this->membership_id ) {
			$this->membership_id = ! empty( $_GET['membership_id'] ) ? absint( $_GET['membership_id'] ) : null;
		}

		return array(
			'membership_id' => $this->membership_id,
		);

	}

	/**
	 * Define the structure of the table
	 *
	 * @since 3.32.0
	 *
	 * @return array
	 */
	public function set_columns() {
		$cols = array(
			'id'         => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'ID', 'lifterlms' ),
			),
			'name'       => array(
				'sortable' => true,
				'title'    => __( 'Name', 'lifterlms' ),
			),
			'name_last'  => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Last Name', 'lifterlms' ),
			),
			'name_first' => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'First Name', 'lifterlms' ),
			),
			'email'      => array(
				'exportable'  => true,
				'export_only' => true,
				'title'       => __( 'Email', 'lifterlms' ),
			),
			'status'     => array(
				'exportable' => true,
				'filterable' => llms_get_enrollment_statuses(),
				'sortable'   => true,
				'title'      => __( 'Status', 'lifterlms' ),
			),
			'enrolled'   => array(
				'exportable' => true,
				'sortable'   => true,
				'title'      => __( 'Enrollment Updated', 'lifterlms' ),
			),
		);

		return $cols;

	}

}
