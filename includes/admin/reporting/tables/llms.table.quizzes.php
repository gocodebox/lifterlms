<?php
/**
 * Quizzes Reporting Table.
 *
 * @since 3.16.0
 * @version 3.36.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Quizzes Reporting Table class.
 *
 * @since 3.16.0
 * @since 3.36.1 Fixed an issue that allow instructors, who can only see their own reports,
 *               to see all the quizzes when they had no courses or courses with no lessons.
 */
class LLMS_Table_Quizzes extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 *
	 * @var  string
	 */
	protected $id = 'quizzes';

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
	protected $filterby = 'instructor';

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
	protected $orderby = 'title';

	/**
	 * Retrieve data for a cell
	 *
	 * @since 3.16.0
	 * @since 3.24.0 Unknown.
	 *
	 * @param    string $key   the column id / key
	 * @param    mixed  $data  object / array of data that the function can use to extract the data
	 * @return   mixed
	 */
	protected function get_data( $key, $data ) {

		$quiz = llms_get_post( $data );

		switch ( $key ) {

			case 'attempts':
				$query = new LLMS_Query_Quiz_Attempt(
					array(
						'quiz_id'  => $quiz->get( 'id' ),
						'per_page' => 1,
					)
				);

				$url   = LLMS_Admin_Reporting::get_current_tab_url(
					array(
						'tab'     => 'quizzes',
						'stab'    => 'attempts',
						'quiz_id' => $quiz->get( 'id' ),
					)
				);
				$value = '<a href="' . $url . '">' . $query->found_results . '</a>';

				break;

			case 'average':
				$grade = 0;
				$query = new LLMS_Query_Quiz_Attempt(
					array(
						'quiz_id'  => $quiz->get( 'id' ),
						'per_page' => 1000,
					)
				);

				$attempts = count( $query->results );

				if ( ! $attempts ) {
					$value = 0;
				} else {

					foreach ( $query->get_attempts() as $attempt ) {
						$grade += $attempt->get( 'grade' );
					}

					$value = round( $grade / $attempts, 3 ) . '%';

				}

				break;

			case 'course':
				$value  = '&mdash;';
				$course = $quiz->get_course();
				if ( $course ) {
					$url   = LLMS_Admin_Reporting::get_current_tab_url(
						array(
							'tab'       => 'courses',
							'course_id' => $course->get( 'id' ),
						)
					);
					$value = '<a href="' . esc_url( $url ) . '">' . $course->get( 'title' ) . '</a>';
				}
				break;

			case 'id':
				$value = $this->get_post_link( $quiz->get( 'id' ) );
				break;

			case 'lesson':
				$value  = '&mdash;';
				$lesson = $quiz->get_lesson();
				if ( $lesson ) {
					$value = $lesson->get( 'title' );
				}
				break;

			case 'title':
				$value = $quiz->get( 'title' );
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

		}// End switch().

		return $this->filter_get_data( $value, $key, $data );

	}

	/**
	 * Retrieve a list of Instructors to be used for Filtering
	 *
	 * @since 3.16.0
	 *
	 * @return array
	 */
	private function get_instructor_filters() {

		$query = get_users(
			array(
				'fields'   => array( 'ID', 'display_name' ),
				'meta_key' => 'last_name',
				'orderby'  => 'meta_value',
				'role__in' => array( 'administrator', 'lms_manager', 'instructor', 'instructors_assistant' ),
			)
		);

		$instructors = wp_list_pluck( $query, 'display_name', 'ID' );

		return $instructors;

	}

	/**
	 * Execute a query to retrieve results from the table.
	 *
	 * @since 3.16.0
	 * @since 3.36.1 Fixed an issue that allow instructors, who can only see their own reports,
	 *                to see all the quizzes when they had no courses or courses with no lessons.
	 *
	 * @param array $args Array of query args.
	 * @return void
	 */
	public function get_results( $args = array() ) {

		$this->title = __( 'Quizzes', 'lifterlms' );

		$args = $this->clean_args( $args );

		if ( isset( $args['page'] ) ) {
			$this->current_page = absint( $args['page'] );
		}

		$per = apply_filters( 'llms_reporting_' . $this->id . '_per_page', 25 );

		$this->order   = isset( $args['order'] ) ? $args['order'] : $this->order;
		$this->orderby = isset( $args['orderby'] ) ? $args['orderby'] : $this->orderby;

		$this->filter   = isset( $args['filter'] ) ? $args['filter'] : $this->get_filter();
		$this->filterby = isset( $args['filterby'] ) ? $args['filterby'] : $this->get_filterby();

		$query_args = array(
			'order'          => $this->order,
			'orderby'        => $this->orderby,
			'paged'          => $this->current_page,
			'post_status'    => 'publish',
			'post_type'      => 'llms_quiz',
			'posts_per_page' => $per,
		);

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

			$lessons = array();
			$courses = $instructor->get_courses(
				array(
					'posts_per_page' => -1,
				)
			);
			foreach ( $courses as $course ) {
				$lessons = array_merge( $lessons, $course->get_lessons( 'ids' ) );
			}

			if ( empty( $lessons ) ) {
				return;
			}

			$query_args['meta_query'] = array(
				array(
					'compare' => 'IN',
					'key'     => '_llms_lesson_id',
					'value'   => $lessons,
				),
			);
			$query                    = new WP_Query( $query_args );

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
	 *
	 * @since 3.16.0
	 *
	 * @return   string
	 */
	public function get_table_search_form_placeholder() {
		return apply_filters( 'llms_table_get_' . $this->id . '_search_placeholder', __( 'Search quizzes...', 'lifterlms' ) );
	}

	/**
	 * Define the structure of arguments used to pass to the get_results method
	 *
	 * @since 3.16.0
	 *
	 * @return   array
	 */
	public function set_args() {
		return array();
	}

	/**
	 * Define the structure of the table
	 *
	 * @since 3.16.0
	 * @since 3.16.10 Unknown.
	 *
	 * @return   array
	 */
	protected function set_columns() {
		return array(
			'id'       => array(
				'exportable' => true,
				'title'      => __( 'ID', 'lifterlms' ),
				'sortable'   => true,
			),
			'title'    => array(
				'exportable' => true,
				'title'      => __( 'Title', 'lifterlms' ),
				'sortable'   => true,
			),
			'course'   => array(
				'exportable' => true,
				'title'      => __( 'Course', 'lifterlms' ),
				'sortable'   => false,
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
