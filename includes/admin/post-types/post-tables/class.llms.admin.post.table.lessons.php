<?php
/**
 * Add, Customize, and Manage LifterLMS Coupon Post Table Columns
 *
 * @since    3.2.3
 * @version  3.2.3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Post_Table_Lessons {

	/**
	 * Constructor
	 * @return  void
	 * @since    3.2.3
	 * @version  3.2.3
	 */
	public function __construct() {

		add_filter( 'manage_lesson_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_lesson_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

	}

	/**
	 * Add Custom lesson Columns
	 * @param   array  $columns  array of default columns
	 * @return  array
	 * @since    3.2.3
	 * @version  3.2.3
	 */
	public function add_columns( $columns ) {

		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Lesson Title', 'lifterlms' ),
			'course' => __( 'Course', 'lifterlms' ),
			'section' => __( 'Section', 'lifterlms' ),
			'prereq' => __( 'Prerequisite', 'lifterlms' ),
			'date' => __( 'Date', 'lifterlms' ),
		);

		return $columns;
	}


	/**
	 * Manage content of custom lesson columns
	 * @param  string $column   column key/name
	 * @param  int    $post_id  WP Post ID of the lesson for the row
	 * @return void
	 * @since    3.2.3
	 * @version  3.2.3
	 */
	public function manage_columns( $column, $post_id ) {

		$l = new LLMS_Lesson( $post_id );

		switch ( $column ) {

			case 'course' :

				$course = $l->get_parent_course();
				$edit_link = get_edit_post_link( $course );

				if ( ! empty( $course ) ) {
					printf( __( '<a href="%1$s">%2$s</a>' ), $edit_link , get_the_title( $course ) );
				}

			break;

			case 'section' :

				$section = $l->get_parent_section();

				$edit_link = get_edit_post_link( $section );

				if ( ! empty( $section ) ) {
					printf( __( '<a href="%1$s">%2$s</a>' ), $edit_link, get_the_title( $section ) );
				}

			break;

			case 'prereq' :

				if ( $l->has_prerequisite() ) {

					$prereq = $l->get( 'prerequisite' );
					$edit_link = get_edit_post_link( $prereq );

					if ( $prereq ) {

						printf( __( '<a href="%1$s">%2$s</a>' ), $edit_link, get_the_title( $prereq ) );

					} else {

						echo '&ndash;';

					}

				} else {

					echo '&ndash;';

				}

			break;

		}

	}

}
return new LLMS_Admin_Post_Table_Lessons();
