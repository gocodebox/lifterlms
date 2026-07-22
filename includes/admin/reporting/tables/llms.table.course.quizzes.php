<?php
/**
 * Display quizzes belonging to a given course on the course quizzes subtab
 *
 * @package LifterLMS/Admin/Reporting/Tables/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Table_Course_Quizzes class
 *
 * @since [version]
 */
class LLMS_Table_Course_Quizzes extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var string
	 */
	protected $id = 'course-quizzes';

	/**
	 * If true, tfoot will add ajax pagination links
	 *
	 * @var boolean
	 */
	protected $is_paginated = true;

	/**
	 * Results sort order
	 *
	 * @var string
	 */
	protected $order = 'ASC';

	/**
	 * Field results are sorted by
	 *
	 * @var string
	 */
	protected $orderby = 'title';

	/**
	 * Post ID for the current course
	 *
	 * @var int|null
	 */
	public $course_id = null;

	/**
	 * Retrieve data for a cell
	 *
	 * @since [version]
	 *
	 * @param string $key  The column id / key.
	 * @param mixed  $data Object / array of data that the function can use to extract the data.
	 * @return mixed
	 */
	protected function get_data( $key, $data ) {

		$quiz = llms_get_post( $data );

		switch ( $key ) {

			case 'attempts':
				$query = new LLMS_Query_Quiz_Attempt(
					array(
						'quiz_id'    => $quiz->get( 'id' ),
						'count_only' => true,
					)
				);
				$url   = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'tab'     => 'quizzes',
						'stab'    => 'attempts',
						'quiz_id' => $quiz->get( 'id' ),
					)
				);
				$value = '<a href="' . esc_url( $url ) . '">' . $query->get_count_only_result() . '</a>';
				break;

			case 'average':
				$grade = 0;
				$query = new LLMS_Query_Quiz_Attempt(
					array(
						'quiz_id'  => $quiz->get( 'id' ),
						'per_page' => 1000,
					)
				);

				$attempts = $query->get_number_results();

				if ( ! $attempts ) {
					$value = '&ndash;';
				} else {
					foreach ( $query->get_attempts() as $attempt ) {
						$grade += $attempt->get( 'grade' );
					}
					$value = round( $grade / $attempts, llms_get_floats_rounding_precision() ) . '%';
				}
				break;

			case 'lesson':
				$value  = '&mdash;';
				$lesson = $quiz->get_lesson();
				if ( $lesson ) {
					$value = $lesson->get( 'title' );
				}
				break;

			case 'title':
				$url   = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'tab'     => 'quizzes',
						'quiz_id' => $quiz->get( 'id' ),
					)
				);
				$value = '<a href="' . esc_url( $url ) . '">' . $quiz->get( 'title' ) . '</a>';
				break;

			default:
				$value = $key;

		}

		return $this->filter_get_data( $value, $key, $data );
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

		$this->title = __( 'Quizzes', 'lifterlms' );

		if ( ! $args ) {
			$args = $this->get_args();
		}

		$args = $this->clean_args( $args );

		if ( empty( $args['course_id'] ) ) {
			return;
		}

		if ( ! current_user_can( 'view_others_lifterlms_reports' ) && ! current_user_can( 'edit_post', absint( $args['course_id'] ) ) ) {
			return;
		}

		$this->course_id = absint( $args['course_id'] );

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->order;
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->orderby;

		$course = llms_get_post( $this->course_id );
		if ( ! $course || ! is_a( $course, 'LLMS_Course' ) ) {
			return;
		}

		$lessons = $course->get_lessons( 'ids' );
		if ( empty( $lessons ) ) {
			return;
		}

		$per = apply_filters( 'llms_reporting_' . $this->id . '_per_page', 25 );

		$query_args = array(
			'order'          => $this->order,
			'orderby'        => $this->orderby,
			'paged'          => $this->current_page,
			'post_status'    => 'publish',
			'post_type'      => 'llms_quiz',
			'posts_per_page' => $per,
			'meta_query'     => array(
				array(
					'key'     => '_llms_lesson_id',
					'value'   => $lessons,
					'compare' => 'IN',
				),
			),
		);

		$query = new WP_Query( $query_args );

		$this->max_pages = $query->max_num_pages;

		if ( $this->max_pages > $this->current_page ) {
			$this->is_last_page = false;
		}

		$this->tbody_data = $query->posts;
	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @since [version]
	 *
	 * @return array
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
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function set_columns() {
		return array(
			'title'    => array(
				'exportable' => true,
				'title'      => __( 'Title', 'lifterlms' ),
				'sortable'   => true,
			),
			'lesson'   => array(
				'exportable' => true,
				'title'      => __( 'Lesson', 'lifterlms' ),
				'sortable'   => false,
			),
			'attempts' => array(
				'exportable' => true,
				'title'      => __( 'Total Attempts', 'lifterlms' ),
				'sortable'   => false,
			),
			'average'  => array(
				'exportable' => true,
				'title'      => __( 'Average Grade', 'lifterlms' ),
				'sortable'   => false,
			),
		);
	}
}
