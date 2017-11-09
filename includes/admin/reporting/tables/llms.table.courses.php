<?php
/**
 * Courses Reporting Table
 *
 * @since    [version]
 * @version  [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Table_Courses extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	protected $id = 'courses';

	/**
	 * If true, tfoot will add ajax pagination links
	 * @var  boolean
	 */
	protected $is_paginated = true;

	/**
	 * Determine of the table is searchable
	 * @var  boolean
	 */
	protected $is_searchable = false;

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
	 * @since    [version]
	 * @version  [version]
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

		}

		return $value;
	}

	/**
	 * Execute a query to retrieve results from the table
	 * @param    array      $args  array of query args
	 * @return   void
	 * @since    [version]
	 * @version  [version]
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

		$query_args = array(
			'order' => $this->order,
			'orderby' => $this->orderby,
			'paged' => $this->current_page,
			'post_status' => 'publish',
			'post_type' => 'course',
			'posts_per_page' => $per,
		);

		if ( 'progress' === $this->orderby ) {
			$query_args['meta_key'] = '_llms_average_progress';
			$query_args['orderby'] = 'meta_value_num';
		} elseif ( 'grade' === $this->orderby ) {
			$query_args['meta_key'] = '_llms_average_progress';
			$query_args['orderby'] = 'meta_value_num';
		}

		$query = new WP_Query( $query_args );

		// var_dump( $query );

		$this->max_pages = $query->max_num_pages;

		if ( $this->max_pages > $this->current_page ) {
			$this->is_last_page = false;
		}

		$this->tbody_data = $query->posts;

	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	public function set_args() {
		return array();
	}

	/**
	 * Define the structure of the table
	 * @return   array
	 * @since    [version]
	 * @version  [version]
	 */
	protected function set_columns() {
		return array(
			'id' => array(
				'title' => __( 'ID', 'lifterlms' ),
				'sortable' => true,
			),
			'title' => array(
				'title' => __( 'Title', 'lifterlms' ),
				'sortable' => true,
			),
			'students' => array(
				'title' => __( 'Students', 'lifterlms' ),
			),
			'progress' => array(
				'title' => __( 'Average Progress', 'lifterlms' ),
				'sortable' => true,
			),
			'grade' => array(
				'title' => __( 'Average Grade', 'lifterlms' ),
				'sortable' => true,
			),
		);
	}

}
