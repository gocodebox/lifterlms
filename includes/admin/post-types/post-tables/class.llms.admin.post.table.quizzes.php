<?php
/**
 * Add, Customize, and Manage LifterLMS quiz posts table Columns
 * @since    3.12.0
 * @version  3.12.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Post_Table_Quizzes {

	/**
	 * Constructor
	 * @return  void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function __construct() {

		add_filter( 'manage_llms_quiz_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_llms_quiz_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

		add_action( 'restrict_manage_posts', array( $this, 'add_filters' ), 10, 2 );
		add_filter( 'parse_query', array( $this, 'parse_query_filters' ), 10, 1 );

	}

	/**
	 * Add Custom lesson Columns
	 * @param   array  $columns  array of default columns
	 * @return  array
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function add_columns( $columns ) {

		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Quiz Title', 'lifterlms' ),
			'course' => __( 'Course(s)', 'lifterlms' ),
			'lesson' => __( 'Lesson(s)', 'lifterlms' ),
			'author' => __( 'Author', 'lifterlms' ),
		);

		return $columns;
	}

	/**
	 * Add filters to the top of the post table
	 * @param    string     $post_type  Post Type of the current posts table
	 * @param    string     $which      positioning of the filters [top|bottom]
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function add_filters( $post_type, $which ) {

		// only for the correct post type & position
		if ( 'llms_quiz' !== $post_type || 'top' !== $which ) {
			return;
		}

		$course_id = isset( $_GET['llms_filter_course_id'] ) ? absint( $_GET['llms_filter_course_id'] ) : false;
		echo LLMS_Admin_Post_Tables::get_post_type_filter_html( 'llms_filter_course_id', 'course', $course_id );

		$lesson_id = isset( $_GET['llms_filter_lesson_id'] ) ? absint( $_GET['llms_filter_lesson_id'] ) : false;
		echo LLMS_Admin_Post_Tables::get_post_type_filter_html( 'llms_filter_lesson_id', 'lesson', $lesson_id );

	}

	/**
	 * Manage content of custom quiz columns
	 * @param  string $column   column key/name
	 * @param  int    $post_id  WP Post ID of the quiz for the row
	 * @return void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function manage_columns( $column, $post_id ) {

		$quiz = new LLMS_QQuiz( $post_id );

		switch ( $column ) {

			case 'course' :

				$lessons = $quiz->get_lessons( 'lessons' );
				$total = count( $lessons );
				foreach ( $lessons as $i => $lesson ) {

					$course_id = $lesson->get( 'parent_course' );
					if ( ! $course_id ) {
						$total--;
						continue;
					}
					printf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_post_link( $course_id ) ), get_the_title( $course_id ) );
					if ( $i + 1 < $total ) {
						echo ', ';
					}
				}

			break;

			case 'lesson' :

				$lessons = $quiz->get_lessons( 'ids' );
				$total = count( $lessons );
				foreach ( $lessons as $i => $lesson_id ) {

					printf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_post_link( $lesson_id ) ), get_the_title( $lesson_id ) );
					if ( $i + 1 < $total ) {
						echo ', ';
					}
				}

			break;

		} // End switch().

	}

	/**
	 * Modify the main WP Query
	 * @param    obj     $query  WP_Query
	 * @return   obj
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function parse_query_filters( $query ) {

		// only modify admin & main query
		if ( ! ( is_admin() && $query->is_main_query() ) ) {
			return $query;
		}

		// dont proceed if it's not our post type
		if ( 'llms_quiz' !== $query->query['post_type'] ) {
			return $query;
		}

		// if none of our custom filters are set, don't proceed
		if ( ! isset( $_REQUEST['llms_filter_course_id'] ) && ! isset( $_REQUEST['llms_filter_lesson_id'] ) ) {
			return $query;
		}

		$post_ids = array();

		if ( isset( $_REQUEST['llms_filter_course_id'] ) ) {
			$course = llms_get_post( absint( $_REQUEST['llms_filter_course_id'] ) );
			if ( $course ) {
				$post_ids = array_merge( $post_ids, $course->get_quizzes() );
			}
		}

		if ( isset( $_REQUEST['llms_filter_lesson_id'] ) ) {
			$lesson = llms_get_post( absint( $_REQUEST['llms_filter_lesson_id'] ) );
			if ( $lesson && $lesson->has_quiz() ) {
				$post_ids[] = $lesson->get( 'assigned_quiz' );
			}
		}

		$query->set( 'post__in', array_unique( $post_ids ) );

		return $query;

	}

}
return new LLMS_Admin_Post_Table_Quizzes();
