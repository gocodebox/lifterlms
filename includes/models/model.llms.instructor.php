<?php
/**
 * LifterLMS Instructor class
 * Manages data and interactions with a LifterLMS Instructor or Instructor's Assistant.
 *
 * @package  LifterLMS/Models
 * @since   3.13.0
 * @version 3.16.11
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Instructor model.
 */
class LLMS_Instructor extends LLMS_Abstract_User_Data {

	/**
	 * Add a parent instructor to an assistant instructor
	 * @param    mixed     $parent_id  WP User ID of the parent instructor or array of User IDs to add multiple
	 * @return   boolean
	 * @since    3.13.0
	 * @version  3.14.4
	 */
	public function add_parent( $parent_ids ) {

		// get existing parents
		$parents = $this->get( 'parent_instructors' );

		// no existing, use an empty array as the default
		if ( ! $parents ) {
			$parents = array();
		}

		if ( ! is_array( $parent_ids ) ) {
			$parent_ids = array( $parent_ids );
		}

		// make ints
		$parent_ids = array_map( 'absint', $parent_ids );

		// add the new parents
		$parents = array_unique( array_merge( $parents, $parent_ids ) );

		// remove duplicates and save
		return $this->set( 'parent_instructors', array_unique( $parents ) );

	}

	/**
	 * Retrieve an array of user ids for assistant instructors attached to the current instructor
	 * @return   array
	 * @since    3.14.4
	 * @version  3.14.4
	 */
	public function get_assistants() {

		global $wpdb;
		$results = $wpdb->get_col( $wpdb->prepare(
			"SELECT user_id FROM {$wpdb->usermeta}
			 WHERE meta_key = 'llms_parent_instructors'
			   AND meta_value LIKE %s;",
			'%i:' . get_current_user_id() . ';%'
		) );

		return $results;

	}

	/**
	 * Retrieve instructor's courses
	 * @uses     $this->get_posts()
	 * @param    array      $args    query argument, see $this->get_posts()
	 * @param    string     $return  return format, see $this->get_posts()
	 * @return   mixed
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_courses( $args = array(), $return = 'llms_posts' ) {

		$args = wp_parse_args( $args, array(
			'post_type' => 'course',
		) );
		return $this->get_posts( $args, $return );

	}

	/**
	 * Retrieve instructor's memberships
	 * @uses     $this->get_posts()
	 * @param    array      $args    query argument, see $this->get_posts()
	 * @param    string     $return  return format, see $this->get_posts()
	 * @return   mixed
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_memberships( $args = array(), $return = 'llms_posts' ) {

		$args = wp_parse_args( $args, array(
			'post_type' => 'llms_membership',
		) );
		return $this->get_posts( $args, $return );

	}

	/**
	 * Retrieve instructor's posts (courses and memberships, mixed)
	 * @param    array      $args    query arguments passed to WP_Query
	 * @param    string     $return  return format [llms_posts|ids|posts|query]
	 * @return   mixed
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_posts( $args = array(), $return = 'llms_posts' ) {

		$serialized_id = serialize( array(
			'id' => $this->get_id(),
		) );
		$serialized_id = str_replace( array( 'a:1:{', '}' ), '', $serialized_id );

		$args = wp_parse_args( $args, array(
			'post_type' => array( 'course', 'llms_membership' ),
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'compare' => 'LIKE',
					'key' => '_llms_instructors',
					'value' => $serialized_id,
				),
			),
		) );

		$query = new WP_Query( $args );

		if ( 'llms_posts' === $return ) {
			$ret = array();
			foreach ( $query->posts as $post ) {
				$ret[] = llms_get_post( $post );
			}
			return $ret;
		} elseif ( 'ids' === $return ) {
			return wp_list_pluck( $query->posts, 'ID' );
		} elseif ( 'posts' === $return ) {
			return $query->posts;
		}

		// if 'query' === $return
		return $query;

	}

	/**
	 * Retrieve instructor's students
	 * @uses     LLMS_Student_Query
	 * @param    array      $args  array of args passed to LLMS_Student_Query
	 * @return   obj
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_students( $args = array() ) {

		$ids = $this->get_posts( array(
			'posts_per_page' => -1,
		), 'ids' );

		$args = wp_parse_args( $args, array(
			'post_id' => $ids,
		) );

		$query = new LLMS_Student_Query( $args );

		// if there's no post ids "hack" the response
		// @todo add an instructor query parameter to the student query
		if ( ! $ids ) {
			$query->results = array();
		}

		return $query;

	}

	/**
	 * Determine if the user is an instructor on a post
	 * @param    int     $post_id  WP Post ID of a course or membership
	 * @return   boolean
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function is_instructor( $post_id = null ) {

		$ret = false;

		// use current post if no post is set
		if ( ! $post_id ) {
			global $post;
			if ( ! $post ) {
				return apply_filters( 'llms_instructor_is_instructor', $ret, $post_id, $this );
			}
			$post_id = $post->ID;
		}

		$check_id = false;

		switch ( get_post_type( $post_id ) ) {

			case 'course':
				$check_id = $post_id;
			break;

			case 'llms_membership':
				$check_id = $post_id;
			break;

			case 'llms_question':
				$question = llms_get_post( $post_id );
				$check_id = array();
				foreach ( $question->get_quizzes() as $qid ) {
					$course = llms_get_post_parent_course( $qid );
					if ( $course ) {
						$check_id[] = $course->get( 'id' );
					}
				}
			break;

			default:
				$course = llms_get_post_parent_course( $post_id );
				if ( $course ) {
					$check_id = $course->get( 'id' );
				}
		}

		if ( $check_id ) {

			$check_ids = ! is_array( $check_id ) ? array( $check_id ) : $check_id;

			$query = $this->get_posts( array(
				'post__in' => $check_ids,
				'posts_per_page' => 1,
	 		), 'query' );

	 		$ret = $query->have_posts();

		}

		return apply_filters( 'llms_instructor_is_instructor', $ret, $post_id, $check_id, $this );

	}

	/**
	 * Used by exporter / cloner to get instructor data
	 * @return   array
	 * @since    3.16.11
	 * @version  3.16.11
	 */
	public function toArray() {
		return array(
			'descrpition' => $this->get( 'description' ),
			'email' => $this->get( 'user_email' ),
			'first_name' => $this->get( 'first_name' ),
			'id' => $this->get_id(),
			'last_name' => $this->get( 'last_name' ),
		);
	}

}
