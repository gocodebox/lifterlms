<?php
/**
 * LifterLMS Instructor class
 * Manages data and interactions with a LifterLMS Instructor or Instructor's Assistant
 * @since   [version]
 * @version [version]
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Instructor extends LLMS_Abstract_User_Data {

	/**
	 * Retrieve instructor's courses
	 * @uses     $this->get_posts()
	 * @param    array      $args    query argument, see $this->get_posts()
	 * @param    string     $return  return format, see $this->get_posts()
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_courses( $args = array(), $return = 'llms_posts' ) {

		$args = wp_parse_args( $args, array(
			'post_type' => 'course'
		) );
		return $this->get_posts( $args, $return );

	}

	/**
	 * Retrieve instructor's memberships
	 * @uses     $this->get_posts()
	 * @param    array      $args    query argument, see $this->get_posts()
	 * @param    string     $return  return format, see $this->get_posts()
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_memberships( $args = array(), $return = 'llms_posts' ) {

		$args = wp_parse_args( $args, array(
			'post_type' => 'llms_membership'
		) );
		return $this->get_posts( $args, $return );

	}

	/**
	 * Retrieve instructor's posts (courses and memberships, mixed)
	 * @param    array      $args    query arguments passed to WP_Query
	 * @param    string     $return  return format [llms_posts|ids|posts|query]
	 * @return   mixed
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_posts( $args = array(), $return = 'llms_posts' ) {

		$args = wp_parse_args( $args, array(
			'post_type' => array( 'course', 'llms_membership' ),
			'post_status' => 'publish',
			'author' => $this->get_id(),
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
		} elseif ( 'posts' ) {
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
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_students( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'post_id' => $this->get_posts( array( 'posts_per_page' => -1 ), 'ids' ),
		) );

		return new LLMS_Student_Query( $args );

	}

}
