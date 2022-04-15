<?php
/**
 * LifterLMS Instructor
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.13.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Instructor model class.
 *
 * Manages data and interactions with a LifterLMS Instructor or Instructor's Assistant.
 *
 * @since 3.13.0
 * @since 3.30.3 Fixed typo in "description" key of the the toArray() method.
 * @since 3.32.0 Add validation to data passed into the `get_students()` method.
 * @since 3.34.0 Fix issue causing `get_assistants()` to return assistants to the currently logged in user instead of using the user id of the current object.
 *               Add `has_student()` method.
 */
class LLMS_Instructor extends LLMS_Abstract_User_Data {

	/**
	 * Add a parent instructor to an assistant instructor.
	 *
	 * @since 3.13.0
	 * @since [version] Remove duplicates just once.
	 *
	 * @param mixed $parent_ids WP User ID of the parent instructor or array of User IDs to add multiple
	 * @return boolean
	 */
	public function add_parent( $parent_ids ) {

		// Get existing parents.
		$parents = $this->get( 'parent_instructors' );

		// No existing, use an empty array as the default.
		if ( ! $parents ) {
			$parents = array();
		}

		if ( ! is_array( $parent_ids ) ) {
			$parent_ids = array( $parent_ids );
		}

		// Make ints.
		$parent_ids = array_map( 'absint', $parent_ids );

		// Add the new parents, removing duplicates.
		$parents = array_unique( array_merge( $parents, $parent_ids ) );

		// Save.
		return $this->set( 'parent_instructors', $parents );

	}

	/**
	 * Retrieve an array of user ids for assistant instructors attached to the instructor
	 *
	 * @since 3.14.4
	 * @since 3.34.0 Uses object ID instead of current user id.
	 *
	 * @return int[]
	 */
	public function get_assistants() {

		global $wpdb;
		$results = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT user_id FROM {$wpdb->usermeta}
			 WHERE meta_key = 'llms_parent_instructors'
			   AND meta_value LIKE %s;",
				'%i:' . $this->get_id() . ';%'
			)
		); // db call ok; no-cache ok.

		return $results;

	}

	/**
	 * Retrieve instructor's courses
	 *
	 * @uses     $this->get_posts()
	 * @param    array  $args    query argument, see $this->get_posts()
	 * @param    string $return  return format, see $this->get_posts()
	 * @return   mixed
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_courses( $args = array(), $return = 'llms_posts' ) {

		$args = wp_parse_args(
			$args,
			array(
				'post_type' => 'course',
			)
		);
		return $this->get_posts( $args, $return );

	}

	/**
	 * Retrieve instructor's memberships
	 *
	 * @uses     $this->get_posts()
	 * @param    array  $args    query argument, see $this->get_posts()
	 * @param    string $return  return format, see $this->get_posts()
	 * @return   mixed
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_memberships( $args = array(), $return = 'llms_posts' ) {

		$args = wp_parse_args(
			$args,
			array(
				'post_type' => 'llms_membership',
			)
		);
		return $this->get_posts( $args, $return );

	}

	/**
	 * Retrieve instructor's posts (courses and memberships, mixed).
	 *
	 * @since 3.13.0
	 * @since [version] Added `$include_assistant` parameter.
	 *
	 * @param array   $args              Query arguments passed to WP_Query.
	 * @param string  $return            Return format [llms_posts|ids|posts|query].
	 * @param boolean $include_assistant Include posts whose instructor is just a parent of this instructor.
	 * @return mixed
	 */
	public function get_posts( $args = array(), $return = 'llms_posts', $include_assistant = false ) {

		$parent_instructors = $include_assistant ? $this->get( 'parent_instructors' ) : array();
		$instructor_ids     = array_merge(
			$parent_instructors,
			array(
				$this->get_id(),
			)
		);

		$serialized_ids = array();
		foreach ( $instructor_ids as $id ) {
			$serialized_id    = serialize( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				array(
					'id' => $id,
				)
			);
			$serialized_ids[] = str_replace( array( 'a:1:{', '}' ), '', $serialized_id );
		}

		if ( 1 === count( $serialized_ids ) ) {
			$meta_query = array(
				array(
					'compare' => 'LIKE',
					'key'     => '_llms_instructors',
					'value'   => $serialized_id,
				),
			);
		} else {
			$meta_query = array(
				array(
					'compare' => 'REGEXP',
					'key'     => '_llms_instructors',
					'value'   => '(' . implode( '|', $serialized_ids ) . ')',
				),
			);
		}

		$args = wp_parse_args(
			$args,
			array(
				'post_type'   => array( 'course', 'llms_membership' ),
				'post_status' => 'publish',
				'meta_query'  => $meta_query,
			)
		);

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

		// If 'query' === $return.
		return $query;

	}

	/**
	 * Retrieve instructor's students
	 *
	 * @since 3.13.0
	 * @since 3.32.0 Validate `post_id` data passed into this function to ensure only students
	 *               in courses/memberships for this instructor are returned.
	 * @since 6.0.0 Don't access `LLMS_Student_Query` properties directly.
	 *
	 * @see LLMS_Student_Query
	 *
	 * @param array $args Array of args passed to LLMS_Student_Query.
	 * @return LLMS_Student_Query
	 */
	public function get_students( $args = array() ) {

		$ids = $this->get_posts(
			array(
				'posts_per_page' => -1,
			),
			'ids'
		);

		// If post IDs were passed we need to verify they're IDs that the instructor has access to.
		if ( ! empty( $args['post_id'] ) ) {
			$args['post_id'] = ! is_array( $args['post_id'] ) ? array( $args['post_id'] ) : $args['post_id'];
			$args['post_id'] = array_intersect( $args['post_id'], $ids );
		} else {
			// No post IDs passed in, query all of the instructor's posts.
			$args['post_id'] = $ids;
		}
		// The instructor has no posts, so we want to force no results.
		// @todo add an instructor query parameter to the student query.
		if ( empty( $args['post_id'] ) ) {
			$args['per_page']      = 0;
			$args['no_found_rows'] = true;
		}

		return new LLMS_Student_Query( $args );

	}

	/**
	 * Determines if the instructor is an instructor to a specific student.
	 *
	 * @since 3.34.0
	 *
	 * @param LLMS_Student|WP_User|int $student Student or user object or WP User ID.
	 * @return bool
	 */
	public function has_student( $student ) {

		$student = llms_get_student( $student );
		if ( ! $student ) {
			return false;
		}

		$ids = $this->get_posts(
			array(
				'posts_per_page' => -1,
			),
			'ids'
		);

		if ( ! $ids ) {
			return false;
		}

		return $student->is_enrolled( $ids, 'any' );

	}

	/**
	 * Determine if the user is an instructor on a post.
	 *
	 * @since 3.13.0
	 * @since [version] Added `$include_assistant` parameter.
	 *
	 * @param int     $post_id      WP Post ID of a course or membership.
	 * @param boolean $as_assistant Whether to check the current instructor is only an assistant of the post's instructor.
	 * @return boolean
	 */
	public function is_instructor( $post_id = null, $as_assistant = false ) {

		$ret = false;

		// Use current post if no post is set.
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

			$query = $this->get_posts(
				array(
					'post__in'       => $check_ids,
					'posts_per_page' => 1,
				),
				'query',
				$as_assistant
			);

			$ret = $query->have_posts();

		}

		return apply_filters( 'llms_instructor_is_instructor', $ret, $post_id, $check_id, $this );

	}

	/**
	 * Used by exporter / cloner to get instructor data
	 *
	 * @since 3.16.11
	 * @since 3.30.3 Renamed "descrpition" key to "description".
	 *
	 * @return array
	 */
	public function toArray() {
		return array(
			'description' => $this->get( 'description' ),
			'email'       => $this->get( 'user_email' ),
			'first_name'  => $this->get( 'first_name' ),
			'id'          => $this->get_id(),
			'last_name'   => $this->get( 'last_name' ),
		);
	}

}
