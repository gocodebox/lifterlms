<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Lesson Handler Class
 *
 * Main Handler for lesson management in LifterLMS
 *
 * @author codeBOX
 */
class LLMS_Lesson_Handler {

	public function __construct( $lesson ) {}

	public static function get_lesson_options_for_select_list() {

		$lessons = LLMS_Post_Handler::get_posts( 'lesson' );

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

	public static function assign_to_course( $course_id, $section_id, $lesson_id, $duplicate = true, $reset_order = true ) {

		// Get position of next lesson
		$section      = new LLMS_Section( $section_id );
		$lesson_order = $section->get_next_available_lesson_order();

		// first determine if lesson is associated with a course
		// we need to know this because if it is already associated then we duplicate it and assign the dupe
		$parent_course  = get_post_meta( $lesson_id, '_llms_parent_course', true );
		$parent_section = get_post_meta( $lesson_id, '_llms_parent_section', true );

		// parent course exists, lets dupe this baby!
		if ( $parent_course && true == $duplicate ) {
			$lesson_id = self::duplicate_lesson( $course_id, $section_id, $lesson_id );
		} else {
			// add parent section and course to new lesson
			update_post_meta( $lesson_id, '_llms_parent_section', $section_id );
			update_post_meta( $lesson_id, '_llms_parent_course', $course_id );

		}

		if ( $reset_order ) {
			update_post_meta( $lesson_id, '_llms_order', $lesson_order );
		}

		return $lesson_id;

	}

	public static function duplicate_lesson( $course_id, $section_id, $lesson_id ) {

		if ( ! isset( $course_id ) || ! isset( $section_id ) || ! isset( $lesson_id ) ) {
			return false;
		}

		// duplicate the lesson
		$new_lesson_id = self::duplicate( $lesson_id );

		if ( ! $new_lesson_id ) {
			return false;
		}

		// add parent section and course to new lesson
		update_post_meta( $new_lesson_id, '_llms_parent_section', $section_id );
		update_post_meta( $new_lesson_id, '_llms_parent_course', $course_id );

		return $new_lesson_id;

	}

	public static function duplicate( $post_id ) {

		// make sure we have a post id and it returns a post
		if ( ! isset( $post_id ) ) {
			return false;
		}

		$post_obj = get_post( $post_id );
		// last check...
		if ( ! isset( $post_obj ) || null == $post_obj ) {
			return false;
		}

		// no going back now...

		// create duplicate post
		$args = array(
			'comment_status' => $post_obj->comment_status,
			'ping_status'    => $post_obj->ping_status,
			'post_author'    => $post_obj->post_author,
			'post_content'   => $post_obj->post_content,
			'post_excerpt'   => $post_obj->post_excerpt,
			'post_name'      => $post_obj->post_name,
			'post_parent'    => $post_obj->post_parent,
			'post_status'    => 'publish',
			'post_title'     => $post_obj->post_title,
			'post_type'      => $post_obj->post_type,
			'to_ping'        => $post_obj->to_ping,
			'menu_order'     => $post_obj->menu_order,
			'post_password'  => $post_obj->post_password,
		);

		// create the duplicate post
		$new_post_id = wp_insert_post( $args );

		if ( $new_post_id ) {

			// get all current post terms and set them to the new post
			$taxonomies = get_object_taxonomies( $post_obj->post_type );
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms(
					$post_obj->ID,
					$taxonomy,
					array(
						'fields' => 'slugs',
					)
				);
				wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
			}

			// duplicate meta
			$insert_meta = self::duplicate_meta( $post_id, $new_post_id );

		}

		return $new_post_id;

	}

	public static function duplicate_meta( $post_id, $new_post_id ) {
		global $wpdb;

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// duplicate all post meta
		$post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id" );

		if ( count( $post_meta_infos ) != 0 ) {

			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";

			foreach ( $post_meta_infos as $meta_info ) {

				// do not copy the following meta values
				if ( '_llms_parent_section' === $meta_info->meta_key ) {
					$meta_info->meta_value = '';
				}
				if ( '_llms_parent_course' === $meta_info->meta_key ) {
					$meta_info->meta_value = '';
				}
				if ( '_prerequisite' === $meta_info->meta_key ) {
					$meta_info->meta_value = '';
				}
				if ( '_has_prerequisite' === $meta_info->meta_key ) {
					$meta_info->meta_value = '';
				}

				$meta_key        = $meta_info->meta_key;
				$meta_value      = addslashes( $meta_info->meta_value );
				$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";

			}

			$sql_query       .= implode( ' UNION ALL ', $sql_query_sel );
			$insert_post_meta = $wpdb->query( $sql_query );

			return $insert_post_meta;
		}

		// phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

} //end LLMS_POST_Handler
