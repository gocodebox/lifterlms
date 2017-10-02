<?php
/**
 * Individual Student's Courses Table
 *
 * @since   3.2.0
 * @version 3.13.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Table_Students extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	protected $id = 'students';

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
	 * Retrieve data for the columns
	 * @param    string     $key        the column id / key
	 * @param    obj        $student    Instance of the LLMS_Student
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.13.0
	 */
	public function get_data( $key, $student ) {

		switch ( $key ) {

			case 'achievements':
				$url = LLMS_Admin_Reporting::get_current_tab_url( array(
					'stab' => 'achievements',
					'student_id' => $student->get_id(),
				) );
				$value = '<a href="' . esc_url( $url ) . '">' . count( $student->get_achievements() ) . '</a>';
			break;

			case 'certificates':
				$url = LLMS_Admin_Reporting::get_current_tab_url( array(
					'stab' => 'certificates',
					'student_id' => $student->get_id(),
				) );
				$value = '<a href="' . esc_url( $url ) . '">' . count( $student->get_certificates() ) . '</a>';
			break;

			case 'completions':
				$courses = $student->get_completed_courses();
				$value = count( $courses['results'] );
			break;

			case 'enrollments':
				$url = LLMS_Admin_Reporting::get_current_tab_url( array(
					'stab' => 'courses',
					'student_id' => $student->get_id(),
				) );
				$value = '<a href="' . esc_url( $url ) . '">' . count( $this->get_enrollments( $student ) ) . '</a>';
			break;

			case 'id':
				$id = $student->get_id();
				if ( current_user_can( 'list_users' ) ) {
					$value = '<a href="' . esc_url( get_edit_user_link( $id ) ) . '">' . $id . '</a>';
				} else {
					$value = $id;
				}
			break;

			case 'memberships':
				$url = LLMS_Admin_Reporting::get_current_tab_url( array(
					'stab' => 'memberships',
					'student_id' => $student->get_id(),
				) );
				$value = '<a href="' . esc_url( $url ) . '">' . count( $student->get_membership_levels() ) . '</a>';
			break;

			case 'name':

				$first = $student->get( 'first_name' );
				$last = $student->get( 'last_name' );

				if ( ! $first || ! $last ) {
					$value = $student->get( 'display_name' );
				} else {
					$value = $last . ', ' . $first;
				}

				$url = LLMS_Admin_Reporting::get_current_tab_url( array(
					'student_id' => $student->get_id(),
				) );
				$value = '<a href="' . esc_url( $url ) . '">' . $value . '</a>';

			break;

			case 'overall_grade':
				$value = $student->get_overall_grade( true );
				if ( is_numeric( $value ) ) {
					$value .= '%';
				}
			break;

			case 'overall_progress':
				$value = $this->get_progress_bar_html( $student->get_overall_progress( true ) );
			break;

			case 'registered':
				$value = $student->get_registration_date();
			break;

			default:
				$value = $key;

		}// End switch().

		return $this->filter_get_data( $value, $key, $student );

	}

	/**
	 * Retrieve a list of IDs for all the users enrollments
	 * @param    obj     $student  instance of LLMS_Student
	 * @return   array             array of course ids
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	private function get_enrollments( $student ) {

		$r = array();

		$page = 1;
		$skip = 0;

		while ( true ) {

			$courses = $student->get_courses( array(
				'limit' => 5000,
				'skip' => 5000 * ( $page - 1 ),
			) );

			$r = array_merge( $courses['results'] );

			if ( ! $courses['more'] ) {
				break;
			} else {
				$page++;
			}
		}

		return $r;

	}

	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_gradebook_get_' . $this->id . '_search_placeholder', __( 'Search students by name or email...', 'lifterlms' ) );
	}

	/**
	 * Execute a query to retrieve results from the table
	 * @param    array      $args  array of query args
	 * @return   void
	 * @since    3.2.0
	 * @version  3.4.0
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Students', 'lifterlms' );

		if ( ! $args ) {
			$args = $this->get_args();
		}

		$args = $this->clean_args( $args );

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$this->filter = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		$this->order = isset( $args['order'] ) ? $args['order'] : $this->get_order();
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->get_orderby();

		$sort = array();
		switch ( $this->get_orderby() ) {
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

			case 'overall_grade':
				$sort = array(
					'overall_grade' => $this->get_order(),
					'last_name' => 'ASC',
					'first_name' => 'ASC',
					'id' => 'ASC',
				);
			break;

			case 'overall_progress':
				$sort = array(
					'overall_progress' => $this->get_order(),
					'last_name' => 'ASC',
					'first_name' => 'ASC',
					'id' => 'ASC',
				);
			break;

			case 'registered':
				$sort = array(
					'registered' => $this->get_order(),
					'last_name' => 'ASC',
					'first_name' => 'ASC',
					'id' => 'ASC',
				);
			break;
		}// End switch().

		$query_args = array(
			'page' => $this->get_current_page(),
			'post_id' => array(),
			'per_page' => apply_filters( 'llms_gradebook_' . $this->id . '_per_page', 20 ),
			'sort' => $sort,
		);

		if ( 'status' === $this->get_filterby() && 'any' !== $this->get_filter() ) {

			$query_args['statuses'] = array( $this->get_filter() );

		}

		if ( isset( $args['search'] ) ) {

			$this->search = $args['search'];
			$query_args['search'] = $this->get_search();

		}

		$query = null;

		// if you can view others reports, make a regular query
		if ( current_user_can( 'view_others_lifterlms_reports' ) ) {

			$query = new LLMS_Student_Query( $query_args );

			// user can only see their own reports, get a list of their students
		} elseif ( current_user_can( 'view_lifterlms_reports' ) ) {

			$instructor = llms_get_instructor();
			if ( ! $instructor ) {
				return;
			}
			$query = $instructor->get_students();

		}

		if ( ! $query ) {
			return;
		}

		$this->max_pages = $query->max_pages;
		$this->is_last_page = $query->is_last_page();

		$this->tbody_data = $query->get_students();

	}


	/**
	 * Define the structure of arguments used to pass to the get_results method
	 * @return   array
	 * @since    2.3.0
	 * @version  2.3.0
	 */
	public function set_args() {
		return array();
	}

	/**
	 * Define the structure of the table
	 * @return   array
	 * @since    3.2.0
	 * @version  3.13.0
	 */
	public function set_columns() {
		return array(
			'id' => array(
				'sortable' => true,
				'title' => __( 'ID', 'lifterlms' ),
			),
			'name' => array(
				'sortable' => true,
				'title' => __( 'Name', 'lifterlms' ),
			),
			'registered' => array(
				'sortable' => true,
				'title' => __( 'Registration Date', 'lifterlms' ),
			),
			'overall_progress' => array(
				'sortable' => true,
				'title' => __( 'Progress', 'lifterlms' ),
			),
			'overall_grade' => array(
				'sortable' => true,
				'title' => __( 'Grade', 'lifterlms' ),
			),
			'enrollments' => array(
				'sortable' => false,
				'title' => __( 'Enrollments', 'lifterlms' ),
			),
			'completions' => array(
				'sortable' => false,
				'title' => __( 'Completions', 'lifterlms' ),
			),
			'certificates' => array(
				'sortable' => false,
				'title' => __( 'Certificates', 'lifterlms' ),
			),
			'achievements' => array(
				'sortable' => false,
				'title' => __( 'Achievements', 'lifterlms' ),
			),
			'memberships' => array(
				'sortable' => false,
				'title' => __( 'Memberships', 'lifterlms' ),
			),
		);
	}

}
