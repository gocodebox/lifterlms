<?php
/**
 * Add, Customize, and Manage LifterLMS question posts table Columns
 * @since    3.12.0
 * @version  3.12.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Post_Table_Quiz_Questions {

	/**
	 * Constructor
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function __construct() {

		add_filter( 'manage_llms_question_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_llms_question_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

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
			'title' => __( 'Question Title', 'lifterlms' ),
			'quiz' => __( 'Quiz', 'lifterlms' ),
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
		if ( 'llms_question' !== $post_type || 'top' !== $which ) {
			return;
		}

		$quiz_id = isset( $_GET['llms_filter_quiz_id'] ) ? absint( $_GET['llms_filter_quiz_id'] ) : false;
		echo LLMS_Admin_Post_Tables::get_post_type_filter_html( 'llms_filter_quiz_id', 'llms_quiz', $quiz_id );

	}

	/**
	 * Manage content of custom question columns
	 * @param    string $column   column key/name
	 * @param    int    $post_id  WP Post ID of the question for the row
	 * @return   void
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function manage_columns( $column, $post_id ) {

		$question = llms_get_post( $post_id );
		if ( ! $question ) {
			return;
		}

		switch ( $column ) {

			case 'quiz' :
				$quizzes = $question->get_quizzes();
				$total = count( $quizzes );
				foreach ( $quizzes as $i => $quiz_id ) {

					printf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_post_link( $quiz_id ) ), get_the_title( $quiz_id ) );
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
		if ( 'llms_question' !== $query->query['post_type'] ) {
			return $query;
		}

		// if none of our custom filters are set, don't proceed
		if ( ! isset( $_REQUEST['llms_filter_quiz_id'] ) ) {
			return $query;
		}

		$quiz = new LLMS_QQuiz( absint( $_REQUEST['llms_filter_quiz_id'] ) );
		$query->set( 'post__in', $quiz->get_questions( 'ids' ) );

		return $query;

	}

}
return new LLMS_Admin_Post_Table_Quiz_Questions();
