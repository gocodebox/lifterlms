<?php
/**
 * Courses Reporting Table
 *
 * @since    3.15.0
 * @version  3.16.14
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Table_Courses extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	protected $id = 'courses';

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
	protected $filterby = 'instructor';

	/**
	 * Is the Table Exportable?
	 * @var  boolean
	 */
	protected $is_exportable = true;

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
	protected $orderby = 'title';

	/**
	 * Retrieve data for a cell
	 * @param    string     $key   the column id / key
	 * @param    mixed      $data  object / array of data that the function can use to extract the data
	 * @return   mixed
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	protected function get_data( $key, $data ) {

		$course = llms_get_post( $data );

		switch ( $key ) {

			case 'grade':
				$value = $course->get( 'average_grade' ) . '%';
			break;

			case 'id':
				$value = $this->get_post_link( $course->get( 'id' ) );
			break;

			case 'instructors':
				$data = array();
				foreach ( $course->get_instructors() as $info ) {
					$instructor = llms_get_instructor( $info['id'] );
					if ( $instructor ) {
						$data[] = sprintf( '%1$s (%2$s)', $instructor->get( 'display_name' ),  $info['label'] );
					}
				}
				$value = implode( ', ', $data );
			break;

			case 'progress':
				$value = $this->get_progress_bar_html( $course->get( 'average_progress' ) );
			break;

			case 'students':
				$value = number_format_i18n( $course->get_student_count(), 0 );
			break;

			case 'title':
				$url = LLMS_Admin_Reporting::get_current_tab_url( array(
					'tab' => 'courses',
					'course_id' => $course->get( 'id' ),
				) );
				$value = '<a href="' . esc_url( $url ) . '">' . $course->get( 'title' ) . '</a>';
			break;

			default:
				$value = $key;

		}// End switch().

		return $value;
	}

	/**
	 * Retrieve a list of Instructors to be used for Filtering
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	private function get_instructor_filters() {

		$query = get_users( array(
			'fields' => array( 'ID', 'display_name' ),
			'meta_key' => 'last_name',
			'orderby' => 'meta_value',
			'role__in' => array( 'administrator', 'lms_manager', 'instructor', 'instructors_assistant' ),
		) );

		$instructors = wp_list_pluck( $query, 'display_name', 'ID' );

		return $instructors;

	}

	/**
	 * Execute a query to retrieve results from the table
	 * @param    array      $args  array of query args
	 * @return   void
	 * @since    3.15.0
	 * @version  3.16.14
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Courses', 'lifterlms' );

		$args = $this->clean_args( $args );

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$per = apply_filters( 'llms_reporting_' . $this->id . '_per_page', 25 );

		$this->order = isset( $args['order'] ) ? $args['order'] : $this->order;
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->orderby;

		$this->filter = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		$query_args = array(
			'order' => $this->order,
			'orderby' => $this->orderby,
			'paged' => $this->current_page,
			'post_status' => array( 'publish', 'private' ),
			'post_type' => 'course',
			'posts_per_page' => $per,
		);

		if ( 'any' !== $this->filter ) {

			$serialized_id = serialize( array(
				'id' => absint( $this->filter ),
			) );
			$serialized_id = str_replace( array( 'a:1:{', '}' ), '', $serialized_id );

			$query_args['meta_query'] = array(
				array(
					'compare' => 'LIKE',
					'key' => '_llms_instructors',
					'value' => $serialized_id,
				),
			);

		}

		if ( 'progress' === $this->orderby ) {
			$query_args['meta_key'] = '_llms_average_progress';
			$query_args['orderby'] = 'meta_value_num';
		} elseif ( 'grade' === $this->orderby ) {
			$query_args['meta_key'] = '_llms_average_progress';
			$query_args['orderby'] = 'meta_value_num';
		}

		if ( isset( $args['search'] ) ) {
			$query_args['s'] = sanitize_text_field( $args['search'] );
		}

		// if you can view others reports, make a regular query
		if ( current_user_can( 'view_others_lifterlms_reports' ) ) {

			$query = new WP_Query( $query_args );

			// user can only see their own reports, get a list of their students
		} elseif ( current_user_can( 'view_lifterlms_reports' ) ) {

			$instructor = llms_get_instructor();
			if ( ! $instructor ) {
				return;
			}
			$query = $instructor->get_courses( $query_args, 'query' );

		} else {

			return;

		}

		$this->max_pages = $query->max_num_pages;

		if ( $this->max_pages > $this->current_page ) {
			$this->is_last_page = false;
		}

		$this->tbody_data = $query->posts;

	}

	/**
	 * Get the Text to be used as the placeholder in a searchable tables search input
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_table_get_' . $this->id . '_search_placeholder', __( 'Search courses...', 'lifterlms' ) );
	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	public function set_args() {
		return array();
	}

	/**
	 * Define the structure of the table
	 * @return   array
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	protected function set_columns() {
		return array(
			'id' => array(
				'exportable' => true,
				'title' => __( 'ID', 'lifterlms' ),
				'sortable' => true,
			),
			'title' => array(
				'exportable' => true,
				'title' => __( 'Title', 'lifterlms' ),
				'sortable' => true,
			),
			'instructors' => array(
				'exportable' => true,
				'filterable' => current_user_can( 'view_others_lifterlms_reports' ) ? $this->get_instructor_filters() : false,
				'title' => __( 'Instructors', 'lifterlms' ),
			),
			'students' => array(
				'exportable' => true,
				'title' => __( 'Students', 'lifterlms' ),
			),
			'progress' => array(
				'exportable' => true,
				'title' => __( 'Average Progress', 'lifterlms' ),
				'sortable' => true,
			),
			'grade' => array(
				'exportable' => true,
				'title' => __( 'Average Grade', 'lifterlms' ),
				'sortable' => true,
			),
		);
	}

}
