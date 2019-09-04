<?php
defined( 'ABSPATH' ) || exit;

/**
 * Add, Customize, and Manage LifterLMS Lesson posts table Columns
 *
 * @since    3.2.3
 * @version  3.24.0
 */
class LLMS_Admin_Post_Table_Lessons {

	/**
	 * Constructor
	 *
	 * @return  void
	 * @since    3.2.3
	 * @version  3.12.0
	 */
	public function __construct() {

		add_filter( 'manage_lesson_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_lesson_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

		add_action( 'restrict_manage_posts', array( $this, 'add_filters' ), 10, 2 );
		add_filter( 'parse_query', array( $this, 'parse_query_filters' ), 10, 1 );

	}

	/**
	 * Add Custom lesson Columns
	 *
	 * @param   array $columns  array of default columns
	 * @return  array
	 * @since    3.2.3
	 * @version  3.12.0
	 */
	public function add_columns( $columns ) {

		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'title'   => __( 'Lesson Title', 'lifterlms' ),
			'course'  => __( 'Course', 'lifterlms' ),
			'section' => __( 'Section', 'lifterlms' ),
			'prereq'  => __( 'Prerequisite', 'lifterlms' ),
			'author'  => __( 'Author', 'lifterlms' ),
			'date'    => __( 'Date', 'lifterlms' ),
		);

		return $columns;
	}

	/**
	 * Add filters to the top of the post table
	 *
	 * @param    string $post_type  Post Type of the current posts table
	 * @param    string $which      positioning of the filters [top|bottom]
	 * @since    3.12.0
	 * @version  3.12.0
	 */
	public function add_filters( $post_type, $which ) {

		// only for the correct post type & position
		if ( 'lesson' !== $post_type || 'top' !== $which ) {
			return;
		}

		$selected = isset( $_GET['llms_filter_course_id'] ) ? absint( $_GET['llms_filter_course_id'] ) : false;
		echo LLMS_Admin_Post_Tables::get_post_type_filter_html( 'llms_filter_course_id', 'course', $selected );

	}

	/**
	 * Manage content of custom lesson columns
	 *
	 * @param    string $column   column key/name
	 * @param    int    $post_id  WP Post ID of the lesson for the row
	 * @return   void
	 * @since    3.2.3
	 * @version  3.24.0
	 */
	public function manage_columns( $column, $post_id ) {

		$lesson = llms_get_post( $post_id );
		if ( ! $lesson ) {
			return;
		}

		switch ( $column ) {

			case 'course':
				$course    = $lesson->get_parent_course();
				$edit_link = get_edit_post_link( $course );

				if ( ! empty( $course ) ) {
					printf( '<a href="%1$s">%2$s</a>', $edit_link, get_the_title( $course ) );
				}

				break;

			case 'section':
				$section = $lesson->get_parent_section();
				if ( ! empty( $section ) ) {
					echo get_the_title( $section );
				}

				break;

			case 'prereq':
				if ( $lesson->has_prerequisite() ) {

					$prereq    = $lesson->get( 'prerequisite' );
					$edit_link = get_edit_post_link( $prereq );

					if ( $prereq ) {

						printf( '<a href="%1$s">%2$s</a>', $edit_link, get_the_title( $prereq ) );

					} else {

						echo '&ndash;';

					}
				} else {

					echo '&ndash;';

				}

				break;

		}// End switch().

	}

	/**
	 * Modify the main WP Query
	 *
	 * @param    obj $query  WP_Query
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
		if ( 'lesson' !== $query->query['post_type'] ) {
			return $query;
		}

		// if none of our custom filters are set, don't proceed
		if ( ! isset( $_REQUEST['llms_filter_course_id'] ) ) {
			return $query;
		}

		// get the query or a default to work with
		$meta_query = $query->get( 'meta_query' );
		if ( ! $meta_query ) {
			$meta_query = array();
		}

		// set an and relation for our filters
		// if other filters already exist, we'll ensure we obey them as well this way
		$meta_query['relation'] = 'AND';

		$meta_query[] = array(
			'compare' => '=',
			'key'     => '_llms_parent_course',
			'value'   => absint( $_REQUEST['llms_filter_course_id'] ),
		);

		$query->set( 'meta_query', $meta_query );

		return $query;

	}

}
return new LLMS_Admin_Post_Table_Lessons();
