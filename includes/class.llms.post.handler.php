<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Post Handler Class
 *
 * Main Handler for post management in LifterLMS
 *
 * @author codeBOX
 */
class LLMS_Post_Handler {

	/**
	 * Create Post
	 *
	 * @param  string $type [optional: a post type]
	 * @param  string $title [optional: a title for the post]
	 * @return int [id of section]
	 */
	public static function create( $type = 'post', $title = '', $excerpt = '' ) {

		if ( empty( $title ) ) {
			$title = 'Section 1';
		}

		// create section post
		$post_data = apply_filters(
			'lifterlms_new_post',
			array(
				'post_type'    => $type,
				'post_title'   => $title,
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
				'post_excerpt' => $excerpt,
			)
		);

		$post_id = wp_insert_post( $post_data, true );

		// check for error in update
		if ( is_wp_error( $post_id ) ) {
			// for now just log the error and set $post_id to 0 (false)
			llms_log( $post_id->get_error_message() );
			$post_id = 0;
		}

		return $post_id;

	}

	public static function update_title( $post_id, $title ) {

		$post_data = array(
			'ID'         => $post_id,
			'post_title' => $title,
		);

		// Update the post into the database
		$updated_post_id = wp_update_post( $post_data );

		if ( $updated_post_id ) {
			return array(
				'id'    => $updated_post_id,
				'title' => $title,
			);
		}

	}

	public static function update_excerpt( $post_id, $excerpt ) {

		$post_data = array(
			'ID'           => $post_id,
			'post_excerpt' => $excerpt,
		);

		// Update the post into the database
		$updated_post_id = wp_update_post( $post_data );

		if ( $updated_post_id ) {
			return array(
				'id'           => $updated_post_id,
				'post_excerpt' => $excerpt,
			);
		}

	}

	/**
	 * Creates a new Section
	 *
	 * @param  [int]  $course_id [the parent course id]
	 * @param  string $title    [optional: a title for the section]
	 * @return [int]            [post id of section]
	 */
	public static function create_section( $course_id, $title = '' ) {

		// no course id? no new section!
		if ( ! isset( $course_id ) ) {
			return;
		}

		// set the section_order variable
		// get the count of sections in the course and add 1
		$course        = new LLMS_Course( $course_id );
		$sections      = $course->get_sections( 'posts' );
		$section_order = count( $sections ) + 1;

		$title = isset( $title ) ? $title : 'New Section';

		$post_id = self::create( 'section', $title );

		// if post created set parent course and order to order determined above
		if ( $post_id ) {
			update_post_meta( $post_id, '_llms_order', $section_order );

			$section               = new LLMS_Section( $post_id );
			$updated_parent_course = $section->set_parent_course( $course_id );
		}

		return $post_id;
	}

	/**
	 * Creates a new Lesson
	 *
	 * @param  [int]  $course_id [the parent course id]
	 * @param  string $title    [optional: a title for the lesson]
	 * @param  string $excerpt  [optional: a desc for the lesson]
	 * @return [int]            [post id of lesson]
	 */
	public static function create_lesson( $course_id, $section_id, $title = '', $excerpt = '' ) {

		// no course id or section id? no new lesson!
		if ( ! isset( $course_id ) || ! isset( $course_id ) ) {
			return;
		}

		// set the lesson_order variable
		// get the count of lessons in the section
		$section      = new LLMS_Section( $section_id );
		$lesson_order = $section->get_next_available_lesson_order();

		$title = isset( $title ) ? $title : 'New Lesson';

		$post_id = self::create( 'lesson', $title, $excerpt );

		// if post created set parent section, parent course and order determined above
		if ( $post_id ) {
			update_post_meta( $post_id, '_llms_order', $lesson_order );

			$lesson                 = new LLMS_Lesson( $post_id );
			$updated_parent_section = $lesson->set_parent_section( $section_id );
			$updated_parent_course  = $lesson->set_parent_course( $course_id );

		}

		return $post_id;
	}

	public static function get_posts( $type = 'post' ) {

		$args      = array(
			'posts_per_page'   => 1000,
			'post_type'        => $type,
			'nopaging'         => true,
			'post_status'      => 'publish',
			'orderby'          => 'post_title',
			'order'            => 'ASC',
			'suppress_filters' => true,
		);
		$postslist = get_posts( $args );

		if ( ! empty( $postslist ) ) {

			foreach ( $postslist as $key => $value ) {
				$value->edit_url = get_edit_post_link( $value->ID, false );
			}
		}

		return $postslist;

	}

	public static function get_lesson_options_for_select_list() {

		$lessons = self::get_posts( 'lesson' );

		$options = array();

		if ( ! empty( $lessons ) ) {

			foreach ( $lessons as $key => $value ) {

				// get parent course if assigned
				$parent_course = get_post_meta( $value->ID, '_llms_parent_course', true );

				if ( $parent_course ) {
					$title = $value->post_title . ' ( ' . get_the_title( $parent_course ) . ' )';
				} else {
					$title = $value->post_title . ' ( ' . __( 'unassigned', 'lifterlms' ) . ' )';
				}

				$options[ $value->ID ] = $title;

			}
		}

		return $options;

	}

	public static function get_prerequisite( $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( 'course' === $post_type ) {
			$course = new LLMS_Course( $post_id );
			return $course->get_prerequisite();

		} elseif ( 'lesson' === $post_type ) {
			$lesson = new LLMS_Lesson( $post_id );
			return $lesson->get_prerequisite();
		}
	}

} //end LLMS_POST_Handler
