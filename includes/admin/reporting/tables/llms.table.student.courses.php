<?php
/**
 * Individual Student's Courses Table
 *
 * @since   3.2.0
 * @version 3.13.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Table_Student_Courses extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	protected $id = 'student-courses';

	/**
	 * If true, tfoot will add ajax pagination links
	 * @var  boolean
	 */
	protected $is_paginated = true;

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
	 * Instance of LLMS_Student
	 * @var  null
	 */
	protected $student = null;

	/**
	 * Retrieve data for the columns
	 * @param    string     $key        the column id / key
	 * @param    int        $course_id  ID of the course
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.13.0
	 */
	public function get_data( $key, $course_id ) {

		$course = new LLMS_Course( $course_id );

		switch ( $key ) {

			case 'progress':
				$value = $this->student->get_progress( $course->get( 'id' ), 'course' ) . '%';
			break;

			case 'completed':
				$date = $this->student->get_completion_date( $course->get( 'id' ) );
				$value = $date ? $date : '&ndash;';
			break;

			case 'grade':

				$grade = $this->student->get_grade( $course->get( 'id' ) );
				$value  = is_numeric( $grade ) ? $grade . '%' : $grade;

			break;

			case 'id':
				$value = $course->get( 'id' );
				if ( current_user_can( 'edit_post', $value ) ) {
					$value = $this->get_post_link( $value );
				}
			break;

			case 'name':
				$id = $course->get( 'id' );
				if ( current_user_can( 'edit_post', $id ) ) {
					$url = esc_url( add_query_arg( array(
						'course_id' => $course->get( 'id' ),
						'page' => 'llms-reporting',
						'stab' => 'courses',
						'student_id' => $this->student->get_id(),
					), admin_url( 'admin.php' ) ) );
					$value = '<a href="' . $url . '">' . $course->get( 'title' ) . '</a>';
				} else {
					$value = $course->get( 'title' );
				}
			break;

			case 'status':
				$value = llms_get_enrollment_status_name( $this->student->get_enrollment_status( $course->get( 'id' ) ) );
			break;

			case 'updated':
				$value = $this->student->get_enrollment_date( $course->get( 'id' ), 'updated' );
			break;

			default:
				$value = $key;

		}// End switch().

		return $this->filter_get_data( $value, $key, $course_id );

	}

	/**
	 * Execute a query to retrieve results from the table
	 * @param    array      $args  array of query args
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_results( $args = array() ) {

		$args = $this->clean_args( $args );

		if ( is_numeric( $args['student'] ) ) {
			$args['student'] = new LLMS_Student( $args['student'] );
		}

		$this->student = $args['student'];

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$per = apply_filters( 'llms_gradebook_' . $this->id . '_per_page', 20 );

		$order = ! empty( $args['order'] ) ? $args['order'] : 'ASC';
		$order = in_array( $order, array( 'ASC', 'DESC' ) ) ? $order : 'ASC';

		if ( isset( $args['order'] ) ) {
			$this->order = $order;
		}
		if ( isset( $args['orderby'] ) ) {
			$this->orderby = $args['orderby'];
		}

		switch ( $this->orderby ) {

			case 'updated':
				$orderby = 'upm.updated_date';
			break;

			case 'name';
			default:
				$orderby = 'p.post_title';
			break;
		}

		$courses = $this->student->get_courses( array(
			'limit' => $per,
			'skip' => ( $this->current_page - 1 ) * $per,
			'orderby' => $orderby,
			'order' => $order,
		) );

		if ( $courses['more'] ) {
			$this->is_last_page = false;
		}

		$this->tbody_data = $courses['results'];

	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 * @return   array
	 * @since    2.3.0
	 * @version  2.3.0
	 */
	public function set_args() {
		return array(
			'page' => $this->get_current_page(),
			'student' => ! empty( $this->student ) ? $this->student->get_id() : absint( $_GET['student_id'] ),
		);
	}

	/**
	 * Define the structure of the table
	 * @return   array
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function set_columns() {
		return array(
			'id' => array(
				'title' => __( 'ID', 'lifterlms' ),
			),
			'name' => array(
				'title' => __( 'Name', 'lifterlms' ),
				'sortable' => true,
			),
			'status' => array(
				'title' => __( 'Status', 'lifterlms' ),
			),
			'grade' => array(
				'title' => __( 'Grade', 'lifterlms' ),
			),
			'progress' => array(
				'title' => __( 'Progress', 'lifterlms' ),
			),
			'updated' => array(
				'title' => __( 'Updated', 'lifterlms' ),
				'sortable' => true,
			),
			'completed' => array(
				'title' => __( 'Completed', 'lifterlms' ),
			),
		);
	}

	/**
	 * Empty message displayed when no results are found
	 * @return   string
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	protected function set_empty_message() {
		return __( 'This student is not enrolled in any courses.', 'lifterlms' );
	}

}
