<?php
/**
 * Individual Student's Courses Table
 *
 * @since   3.2.0
 * @version 3.4.1
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
	 * @param    int        $user       Instance of the WP User
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.4.1
	 */
	public function get_data( $key, $user ) {

		$student = new LLMS_Student( $user->ID );

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

			case 'grade':
				$value = $student->get_overall_grade( true );
				if ( is_numeric( $value ) ) {
					$value .= '%';
				}
			break;

			case 'id':
				$value = '<a href="' . esc_url( get_edit_user_link( $student->get_id() ) ) . '">' . $student->get_id() . '</a>';
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

				$url = LLMS_Admin_Reporting::get_current_tab_url( array( 'student_id' => $student->get_id() ) );
				$value = '<a href="' . esc_url( $url ) . '">' . $value . '</a>';

			break;

			case 'progress':
				$value = $this->get_progress_bar_html( $student->get_overall_progress( true ) );
				;
			break;

			case 'registered':
				$value = $student->get_registration_date();
			break;

			default:
				$value = $key;

		}

		return $this->filter_get_data( $value, $key, $user );

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

		$args = $this->clean_args( $args );

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$per = apply_filters( 'llms_gradebook_' . $this->id . '_per_page', 20 );

		$this->order = isset( $args['order'] ) ? $args['order'] : 'ASC';
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : 'name';

		$query = array(
			'number' => $per,
			'offset' => ( $this->current_page - 1 ) * $per,
			'paged' => $this->current_page,
			'order' => $this->order,
		);

		switch ( $this->orderby ) {

			case 'grade':
				$query['meta_key'] = 'llms_overall_grade';
				$query['orderby'] = 'meta_value_num';
			break;

			case 'progress':
				$query['meta_key'] = 'llms_overall_progress';
				$query['orderby'] = 'meta_value_num';
			break;

			case 'registered':
				$query['orderby'] = 'registered';
			break;

			case 'name':
			default:
				$query['meta_key'] = 'last_name';
				$query['orderby'] = 'meta_value';
			break;

		}

		if ( isset( $args['search'] ) ) {

			$this->search = $args['search'];
			$query['search'] = '*' . $this->search . '*';

		}

		$q = new WP_User_Query( $query );

		$this->max_pages = ceil( $q->total_users / $per );

		if ( $this->max_pages > $this->current_page ) {
			$this->is_last_page = false;
		}

		$this->tbody_data = $q->get_results();

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
	 * @version  3.2.3
	 */
	public function set_columns() {
		return array(
			'id' => array(
				'sortable' => false,
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
			'progress' => array(
				'sortable' => true,
				'title' => __( 'Progress', 'lifterlms' ),
			),
			'grade' => array(
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
