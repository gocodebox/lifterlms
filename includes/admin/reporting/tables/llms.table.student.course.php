<?php
defined( 'ABSPATH' ) || exit;

/**
 * Individual Student's Courses Table
 * @since   3.2.0
 * @version 3.21.0
 */
class LLMS_Table_Student_Course extends LLMS_Admin_Table {

	/**
	 * Unique ID for the Table
	 * @var  string
	 */
	protected $id = 'student-course';

	/**
	 * Stores the current section while building the table
	 * used by $this->output_section_row_html() to determine
	 * if a new section header needs to be output
	 * @var  int
	 */
	private $current_section = null;

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
	public $student = null;

	/**
	 * Retrieve data for the columns
	 * @param    string     $key        the column id / key
	 * @param    int        $lesson     Instance of an LLMS_Lesson
	 * @return   mixed
	 * @since    3.2.0
	 * @version  3.16.0
	 */
	public function get_data( $key, $lesson ) {

		switch ( $key ) {

			case 'completed':
				$date = $this->student->get_completion_date( $lesson->get( 'id' ) );
				$value = $date ? $date : '&ndash;';
			break;

			case 'grade':
				$grade = $this->student->get_grade( $lesson->get( 'id' ) );
				$value = is_numeric( $grade ) ? $grade . '%' : $grade;
			break;

			case 'id':
				$value = $this->get_post_link( $lesson->get( 'id' ) );
			break;

			case 'name':
				$value = $lesson->get( 'title' );
			break;

			case 'quiz':

				$q = $lesson->get( 'quiz' );

				if ( $q ) {

					$url = esc_url( add_query_arg( array(
						'quiz_id' => $q,
						'lesson_id' => $lesson->get( 'id' ),
					) ) );
					$value = '<a href="' . $url . '">' . get_the_title( $q ) . '</a>';

				} else {
					$value = '&ndash;';
				}

			break;

			default:
				$value = $key;

		}// End switch().

		return $this->filter_get_data( $value, $key, $lesson );

	}

	/**
	 * Execute a query to retrieve results from the table
	 * @param    array      $args  array of query args
	 * @return   void
	 * @since    3.2.0
	 * @version  3.2.0
	 */
	public function get_results( $args = array() ) {

		$course = new LLMS_Course( absint( $args['course_id'] ) );

		if ( is_numeric( $args['student'] ) ) {
			$args['student'] = new LLMS_Student( $args['student'] );
		}

		$this->student = $args['student'];

		$this->tbody_data = $course->get_lessons();

	}

	/**
	 * Output a section title row for each course Section
	 * @param    obj     $lesson  the current lesson instance
	 * @return   void
	 * @since    3.2.0
	 * @version  3.21.0
	 */
	public function output_section_row_html( $lesson ) {

		if ( $lesson instanceof LLMS_Lesson ) {

			$sid = $lesson->get_parent_section();

			if ( $this->current_section != $sid ) {
				echo '<tr><td>' . $sid . '</td><td class="section-title" colspan="' . ( $this->get_columns_count() - 1 ) . '">' . sprintf( _x( 'Section: %s', 'section title', 'lifterlms' ), get_the_title( $sid ) ) . '</td></tr>';
				$this->current_section = $sid;
			}
		}

	}

	/**
	 * Allow custom hooks to be registered for use within the class
	 * @return   void
	 * @since    3.2.0
	 * @version  3.21.0
	 */
	protected function register_hooks() {
		add_action( 'llms_table_before_tr', array( $this, 'output_section_row_html' ), 10, 1 );
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
			),
			'quiz' => array(
				'title' => __( 'Quiz', 'lifterlms' ),
			),
			'grade' => array(
				'title' => __( 'Grade', 'lifterlms' ),
			),
			'completed' => array(
				'title' => __( 'Completed', 'lifterlms' ),
			),
		);
	}

}
