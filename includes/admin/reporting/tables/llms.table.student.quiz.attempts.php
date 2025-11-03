<?php
/**
 * Student Quiz Attempts Reporting Table
 *
 * @package LifterLMS/Admin/Reporting/Tables/Classes
 *
 * @since 9.1.0
 * @version 9.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Student_Quiz_Attempts class.
 *
 * Displays all quiz attempts for a specific student across all courses.
 *
 * @since 9.1.0
 */
class LLMS_Table_Student_Quiz_Attempts extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'student_quiz_attempts';

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
	protected $filterby = 'grade';

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
	protected $order = 'DESC';

	/**
	 * Field results are sorted by
	 *
	 * @var  string
	 */
	protected $orderby = 'id';

	/**
	 * Student ID for the displayed student
	 *
	 * @var  null
	 */
	protected $student_id = null;

	/**
	 * Retrieve data for a cell.
	 *
	 * @since 9.1.0
	 *
	 * @param string            $key     The column id / key.
	 * @param LLMS_Quiz_Attempt $attempt LLMS_Quiz_Attempt obj.
	 * @return mixed
	 */
	protected function get_data( $key, $attempt ) {

		switch ( $key ) {

			case 'quiz':
				$quiz = $attempt->get_quiz();
				if ( $quiz ) {
					$value = $quiz->get( 'title' );

					// Add link to quiz attempts if user has permission
					if ( current_user_can( 'edit_post', $quiz->get( 'id' ) ) ) {
						$url   = LLMS_Admin_Reporting::get_current_tab_url(
							array(
								'tab'     => 'quizzes',
								'stab'    => 'attempts',
								'quiz_id' => $quiz->get( 'id' ),
							)
						);
						$value = '<a href="' . esc_url( $url ) . '">' . esc_html( $value ) . '</a>';
					}
				} else {
					$value = __( '[Deleted Quiz]', 'lifterlms' );
				}
				break;

			case 'course':
				$value = '&mdash;';
				$quiz  = $attempt->get_quiz();
				if ( $quiz ) {
					$course = $quiz->get_course();
					if ( $course ) {
						$url   = LLMS_Admin_Reporting::get_current_tab_url(
							array(
								'tab'       => 'courses',
								'stab'      => 'overview',
								'course_id' => $course->get( 'id' ),
							)
						);
						$value = '<a href="' . esc_url( $url ) . '">' . esc_html( $course->get( 'title' ) ) . '</a>';
					}
				}
				break;

			case 'lesson':
				$quiz = $attempt->get_quiz();
				if ( $quiz ) {
					$lesson = $quiz->get_lesson();
					if ( $lesson ) {
						$value = $lesson->get( 'title' );
					} else {
						$value = __( '[Deleted Lesson]', 'lifterlms' );
					}
				} else {
					$value = '&ndash;';
				}
				break;

			case 'attempt':
				$value = '#' . $attempt->get( $key );
				break;

			case 'grade':
				$value      = $attempt->get( $key ) ? $attempt->get( $key ) . '%' : '0%';
				$additional = $attempt->l10n( 'status' );
				if ( $attempt->can_be_resumed() && $attempt->is_last_attempt() ) {
					$additional .= ' - ' . esc_html__( 'Can be resumed', 'lifterlms' );
				}
				$value .= ' (' . $additional . ')';
				break;

			case 'start_date':
			case 'end_date':
				$value = '&ndash;';
				$date  = $attempt->get( $key );
				if ( $date ) {
					$value = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date ) );
				}
				break;

			case 'id':
				$value = sprintf( '%2$d (%1$s)', $attempt->get_key(), $attempt->get( 'id' ) );

				$url = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'tab'        => 'quizzes',
						'stab'       => 'attempts',
						'quiz_id'    => $attempt->get( 'quiz_id' ),
						'attempt_id' => $attempt->get( 'id' ),
					)
				);

				$value = '<a href="' . esc_url( $url ) . '">' . $value . '</a>';
				break;

			default:
				$value = $key;

		}// End switch().

		return $value;
	}

	/**
	 * Execute a query to retrieve results from the table
	 *
	 * @since 9.1.0
	 *
	 * @param array $args Array of query args.
	 * @return void
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Quiz Attempts', 'lifterlms' );

		$args = $this->clean_args( $args );

		$this->student_id = $args['student_id'];

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$per = apply_filters( 'llms_reporting_' . $this->id . '_per_page', 25 );

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->order;
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->orderby;

		$this->filter   = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		$query_args = array(
			'sort'       => array(
				$this->orderby => $this->order,
			),
			'page'       => $this->current_page,
			'per_page'   => $per,
			'student_id' => $this->student_id,
		);

		// Add search functionality
		if ( isset( $args['search'] ) && ! empty( $args['search'] ) ) {
			$query_args['search'] = $args['search'];
		}

		if ( 'any' !== $this->filter ) {
			$query_args['status'] = $this->filter;
		}

		// Check permissions
		if ( ! current_user_can( 'view_others_lifterlms_reports' ) && ! llms_current_user_can( 'view_lifterlms_reports', $this->student_id ) ) {
			return;
		}

		$query = new LLMS_Query_Quiz_Attempt( $query_args );

		$this->max_pages    = $query->get_max_pages();
		$this->is_last_page = $query->is_last_page();

		$this->tbody_data = $query->get_attempts();
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
			'student_id' => ! empty( $this->student_id ) ? $this->student_id : ( isset( $_GET['student_id'] ) ? absint( $_GET['student_id'] ) : null ),
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
			'id'         => array(
				'exportable' => true,
				'title'      => __( 'ID', 'lifterlms' ),
				'sortable'   => true,
			),
			'quiz'       => array(
				'exportable' => true,
				'title'      => __( 'Quiz', 'lifterlms' ),
				'sortable'   => false,
			),
			'course'     => array(
				'exportable' => true,
				'title'      => __( 'Course', 'lifterlms' ),
				'sortable'   => false,
			),
			'lesson'     => array(
				'exportable' => true,
				'title'      => __( 'Lesson', 'lifterlms' ),
				'sortable'   => false,
			),
			'attempt'    => array(
				'exportable' => true,
				'title'      => __( 'Attempt #', 'lifterlms' ),
				'sortable'   => true,
			),
			'grade'      => array(
				'filterable' => llms_get_quiz_attempt_statuses(),
				'exportable' => true,
				'title'      => __( 'Grade', 'lifterlms' ),
				'sortable'   => true,
			),
			'start_date' => array(
				'exportable' => true,
				'title'      => __( 'Start Date', 'lifterlms' ),
				'sortable'   => true,
			),
			'end_date'   => array(
				'exportable' => true,
				'title'      => __( 'End Date', 'lifterlms' ),
				'sortable'   => true,
			),
		);

		return $cols;
	}
}
