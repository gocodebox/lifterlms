<?php
/**
 * Quiz Non-Attempts Reporting Table
 *
 * @package LifterLMS/Admin/Reporting/Tables/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Quiz_Non_Attempts class.
 *
 * Displays students enrolled in courses containing a quiz but who have not attempted the quiz.
 *
 * @since [version]
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
	 * @since [version]
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
				$quiz = llms_get_post( $this->quiz_id );
				$course = $quiz ? $quiz->get_course() : false;
				
				if ( $course ) {
					$enrollment_date = $student->get_enrollment_date( $course->get( 'id' ) );
					$value = $enrollment_date ? $enrollment_date : '&mdash;';
				} else {
					$value = '&mdash;';
				}
				break;

			case 'status':
				$quiz = llms_get_post( $this->quiz_id );
				$course = $quiz ? $quiz->get_course() : false;
				
				if ( $course ) {
					$status = $student->get_enrollment_status( $course->get( 'id' ) );
					$value = ucfirst( $status );
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
	 * Execute a query to retrieve results from the table
	 *
	 * @since [version]
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

		// Check permissions
		if ( ! ( current_user_can( 'view_others_lifterlms_reports' ) || ( current_user_can( 'view_lifterlms_reports' ) && current_user_can( 'edit_post', $args['quiz_id'] ) ) ) ) {
			return;
		}

		// Get the quiz and course
		$quiz = llms_get_post( $this->quiz_id );
		if ( ! $quiz ) {
			return;
		}

		$course = $quiz->get_course();
		if ( ! $course ) {
			return;
		}

		// Get all enrolled students in the course  
		$enrolled_students = llms_get_enrolled_students( $course->get( 'id' ), array( 'enrolled', 'expired', 'cancelled' ), -1, 0 );
		
		if ( empty( $enrolled_students ) ) {
			$this->tbody_data = array();
			return;
		}

		// Get students who have attempted the quiz
		$attempted_query = new LLMS_Query_Quiz_Attempt( array(
			'quiz_id'  => $this->quiz_id,
			'per_page' => -1,
		) );

		$attempted_student_ids = array();
		if ( $attempted_query->has_results() ) {
			foreach ( $attempted_query->get_attempts() as $attempt ) {
				$student = $attempt->get_student();
				if ( $student ) {
					$attempted_student_ids[] = $student->get_id();
				}
			}
		}

		// Get students who haven't attempted
		$non_attempted_student_ids = array_diff( $enrolled_students, $attempted_student_ids );

		if ( empty( $non_attempted_student_ids ) ) {
			$this->tbody_data = array();
			return;
		}

		// Apply search filter
		if ( isset( $args['search'] ) && ! empty( $args['search'] ) ) {
			$search_term = sanitize_text_field( $args['search'] );
			$filtered_ids = array();
			
			foreach ( $non_attempted_student_ids as $student_id ) {
				$student = llms_get_student( $student_id );
				if ( $student ) {
					$name = $student->get( 'display_name' );
					$email = $student->get( 'user_email' );
					$first = $student->get( 'first_name' );
					$last = $student->get( 'last_name' );
					
					if ( stripos( $name, $search_term ) !== false ||
						 stripos( $email, $search_term ) !== false ||
						 stripos( $first, $search_term ) !== false ||
						 stripos( $last, $search_term ) !== false ) {
						$filtered_ids[] = $student_id;
					}
				}
			}
			$non_attempted_student_ids = $filtered_ids;
		}

		// Apply enrollment status filter
		if ( 'any' !== $this->filter ) {
			$filtered_ids = array();
			foreach ( $non_attempted_student_ids as $student_id ) {
				$student = llms_get_student( $student_id );
				if ( $student ) {
					$status = $student->get_enrollment_status( $course->get( 'id' ) );
					if ( $status === $this->filter ) {
						$filtered_ids[] = $student_id;
					}
				}
			}
			$non_attempted_student_ids = $filtered_ids;
		}

		// Sort students
		if ( ! empty( $non_attempted_student_ids ) ) {
			$students_with_data = array();
			foreach ( $non_attempted_student_ids as $student_id ) {
				$student = llms_get_student( $student_id );
				if ( $student ) {
					$sort_key = '';
					switch ( $this->orderby ) {
						case 'name':
							$first = $student->get( 'first_name' );
							$last = $student->get( 'last_name' );
							$sort_key = ! empty( $last ) ? $last : $student->get( 'display_name' );
							break;
						case 'id':
							$sort_key = $student->get_id();
							break;
						case 'email':
							$sort_key = $student->get( 'user_email' );
							break;
						default:
							$sort_key = $student->get( 'display_name' );
					}
					$students_with_data[] = array(
						'student' => $student,
						'sort_key' => strtolower( $sort_key ),
					);
				}
			}

			// Sort the array
			usort( $students_with_data, function( $a, $b ) {
				if ( 'DESC' === $this->order ) {
					return strcmp( $b['sort_key'], $a['sort_key'] );
				}
				return strcmp( $a['sort_key'], $b['sort_key'] );
			});

			// Extract sorted students
			$sorted_students = array_map( function( $item ) {
				return $item['student'];
			}, $students_with_data );

			// Pagination
			$total_results = count( $sorted_students );
			$this->max_pages = ceil( $total_results / $per );
			$this->is_last_page = ( $this->current_page >= $this->max_pages );

			$offset = ( $this->current_page - 1 ) * $per;
			$this->tbody_data = array_slice( $sorted_students, $offset, $per );
		} else {
			$this->tbody_data = array();
		}
	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @since [version]
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
	 * @since [version]
	 */
	protected function set_columns() {

		$cols = array(
			'id' => array(
				'exportable' => true,
				'title'      => __( 'ID', 'lifterlms' ),
				'sortable'   => true,
			),
			'name' => array(
				'exportable' => true,
				'title'      => __( 'Name', 'lifterlms' ),
				'sortable'   => true,
			),
			'email' => array(
				'exportable' => true,
				'title'      => __( 'Email', 'lifterlms' ),
				'sortable'   => true,
			),
			'enrolled_courses' => array(
				'exportable' => true,
				'title'      => __( 'Enrollment Date', 'lifterlms' ),
				'sortable'   => false,
			),
			'status' => array(
				'filterable' => array(
					'enrolled' => __( 'Enrolled', 'lifterlms' ),
					'expired'  => __( 'Expired', 'lifterlms' ),
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