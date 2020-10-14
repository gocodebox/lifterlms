<?php
/**
 * Add, Customize, and Manage LifterLMS Lesson posts table Columns
 *
 * @package LifterLMS/Admin/PostTypes/PostTables/Classes
 *
 * @since 3.2.3
 * @version 4.5.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Post_Table_Lessons class
 *
 * @since 3.2.3
 * @since 3.24.0 Unknown.
 */
class LLMS_Admin_Post_Table_Lessons {

	/**
	 * Constructor
	 *
	 * @since 3.2.3
	 * @since 3.12.0 Unknown.
	 *
	 * @return void
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
	 * @since 3.2.3
	 * @since 3.12.0 Unknown.
	 *
	 * @param array $columns Array of default columns.
	 * @return array
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
	 * @since 3.12.0
	 *
	 * @param string $post_type Post Type of the current posts table.
	 * @param string $which     Positioning of the filters [top|bottom].
	 * @return void
	 */
	public function add_filters( $post_type, $which ) {

		// Only for the correct post type & position.
		if ( 'lesson' !== $post_type || 'top' !== $which ) {
			return;
		}

		$selected = isset( $_GET['llms_filter_course_id'] ) ? absint( $_GET['llms_filter_course_id'] ) : false;
		echo LLMS_Admin_Post_Tables::get_post_type_filter_html( 'llms_filter_course_id', 'course', $selected );

	}

	/**
	 * Manage content of custom lesson columns
	 *
	 * @since 3.2.3
	 * @since 3.24.0 Unknown.
	 *
	 * @param string $column  Column key/name.
	 * @param int    $post_id WP Post ID of the lesson for the row.
	 * @return void
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

		}

	}

	/**
	 * Modify the main WP Query
	 *
	 * @since 3.12.0
	 * @since 4.5.1 Bail early if the query has no `post_type` property set.
	 *
	 * @param WP_Query $query The WordPress Query.
	 * @return WP_Query
	 */
	public function parse_query_filters( $query ) {

		// Only modify admin & main query.
		if ( ! ( is_admin() && $query->is_main_query() ) ) {
			return $query;
		}

		// Don't proceed if it's not our post type.
		if ( ! isset( $query->query['post_type'] ) || 'lesson' !== $query->query['post_type'] ) {
			return $query;
		}

		// If none of our custom filters are set, don't proceed.
		if ( ! isset( $_REQUEST['llms_filter_course_id'] ) ) {
			return $query;
		}

		// Get the query or a default to work with.
		$meta_query = $query->get( 'meta_query' );
		if ( ! $meta_query ) {
			$meta_query = array();
		}

		/**
		 * Set an and relation for our filters
		 * if other filters already exist, we'll ensure we obey them as well this way.
		 */
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
