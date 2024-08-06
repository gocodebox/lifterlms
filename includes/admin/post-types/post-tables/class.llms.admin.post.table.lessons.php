<?php
/**
 * Add, Customize, and Manage LifterLMS Lesson posts table Columns
 *
 * @package LifterLMS/Admin/PostTypes/PostTables/Classes
 *
 * @since 3.2.3
 * @version 7.1.0
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
	 * Constructor.
	 *
	 * @since 3.2.3
	 * @since 3.12.0 Unknown.
	 * @since 7.1.0 Added links to the course builder.
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'post_row_actions', array( $this, 'add_links' ), 1, 2 );

		add_filter( 'manage_lesson_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_lesson_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

		add_action( 'restrict_manage_posts', array( $this, 'add_filters' ), 10, 2 );
		add_filter( 'parse_query', array( $this, 'parse_query_filters' ), 10, 1 );
	}

	/**
	 * Add course builder edit link.
	 *
	 * @since 7.1.0
	 *
	 * @param array   $actions Existing actions.
	 * @param WP_Post $post    Lesson's WP_Post object.
	 * @return array
	 */
	public function add_links( $actions, $post ) {

		if ( 'lesson' === $post->post_type && current_user_can( 'edit_lesson', $post->ID ) ) {

			$lesson = llms_get_post( $post->ID );
			if ( ! $lesson ) {
				return $actions;
			}

			$course = $lesson->get( 'parent_course' );
			$url    = add_query_arg(
				array(
					'page'      => 'llms-course-builder',
					'course_id' => $course,
				),
				admin_url( 'admin.php' )
			);
			$url   .= sprintf( '#lesson:%d', $post->ID );

			$actions = array_merge(
				array(
					'llms-builder' => '<a href="' . esc_url( $url ) . '">' . __( 'Builder', 'lifterlms' ) . '</a>',
				),
				$actions
			);

		}

		return $actions;
	}

	/**
	 * Add custom lesson columns.
	 *
	 * @since 3.2.3
	 * @since 3.12.0 Unknown.
	 * @since 7.1.0 Quiz column added.
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
			'quiz'    => __( 'Quiz', 'lifterlms' ),
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
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in the function.
		echo LLMS_Admin_Post_Tables::get_post_type_filter_html( 'llms_filter_course_id', 'course', $selected );
	}

	/**
	 * Manage content of custom lesson columns.
	 *
	 * @since 3.2.3
	 * @since 3.24.0 Unknown.
	 * @since 5.7.0 Replaced the call to the deprecated `LLMS_Lesson::get_parent_course()` method with `LLMS_Lesson::get( 'parent_course' )`.
	 * @since 7.1.0 Implemented content for the quiz column.
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
				$course    = $lesson->get( 'parent_course' );
				$edit_link = get_edit_post_link( $course );

				if ( ! empty( $course ) ) {
					printf( '<a href="%1$s">%2$s</a>', esc_url( $edit_link ), esc_html( get_the_title( $course ) ) );
				}

				break;

			case 'section':
				$section = $lesson->get_parent_section();
				if ( ! empty( $section ) ) {
					echo esc_html( get_the_title( $section ) );
				}

				break;

			case 'prereq':
				if ( $lesson->has_prerequisite() ) {

					$prereq    = $lesson->get( 'prerequisite' );
					$edit_link = get_edit_post_link( $prereq );

					if ( $prereq ) {

						printf( '<a href="%1$s">%2$s</a>', esc_url( $edit_link ), esc_html( get_the_title( $prereq ) ) );

					} else {

						echo '&ndash;';

					}
				} else {

					echo '&ndash;';

				}

				break;

			case 'quiz':
				$course = $lesson->get( 'parent_course' );
				$url    = add_query_arg(
					array(
						'page'      => 'llms-course-builder',
						'course_id' => $course,
					),
					admin_url( 'admin.php' )
				);
				$url   .= sprintf( '#lesson:%d:quiz', $post_id );

				if ( $lesson->has_quiz() ) {

					$label = __( 'Edit Quiz', 'lifterlms' );

				} else {

					$label = __( 'Add Quiz', 'lifterlms' );

				}

				echo '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';

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
