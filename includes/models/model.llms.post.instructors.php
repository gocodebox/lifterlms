<?php
/**
 * LLMS Post Instructors
 *
 * Allow interactions with the custom multi-author functionality
 * currently enabled for Courses and Memberships only.
 *
 * Rather than instantiating this class directly
 * you should use LLMS_Course->instructors() or LLMS_Membership()->instructors()
 *
 * @package LifterLMS/Models
 *
 * @since 3.13.0
 * @version 3.28.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Post_Instructors class.
 *
 * @since 3.13.0
 * @since 3.30.3 Explicitly define class properties.
 */
class LLMS_Post_Instructors {

	/**
	 * WP Post ID
	 *
	 * @var int
	 * @since 3.13.0
	 */
	public $id;

	/**
	 * @var LLMS_Post_Model
	 * @since 3.13.0
	 */
	public $post;

	/**
	 * Constructor
	 *
	 * @param    mixed $post  (obj) LLMS_Post_Model
	 *                        (obj) WP_Post
	 *                        (int) WP_Post ID
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function __construct( $post ) {

		// setup a post if post id of WP_Post is passed in
		if ( is_numeric( $post ) || is_a( $post, 'WP_Post' ) ) {

			$post = llms_get_post( $post );

		}

		// double check we have an LLMS_Post
		if ( is_subclass_of( $post, 'LLMS_Post_Model' ) ) {

			$this->post = $post;
			$this->id   = $post->get( 'id' );

		}

	}

	/**
	 * Retrieve the default attributes for a new post instructor
	 *
	 * @return     array
	 * @since      3.13.0
	 * @version    3.28.0
	 * @deprecated 3.25.0
	 */
	public function get_defaults() {
		llms_deprecated_function( 'LLMS_Post_Instructors::get_defaults()', '3.25.0', 'llms_get_instructors_defaults()' );
		return llms_get_instructors_defaults();
	}

	/**
	 * Retrieve course instructor information
	 *
	 * @param    boolean $exclude_hidden  if true, excludes hidden instructors from the return array
	 * @return   array
	 * @since    3.13.0
	 * @version  3.23.0
	 */
	public function get_instructors( $exclude_hidden = false ) {

		$instructors = $this->post->get( 'instructors' );

		// if empty, respond with the course author in an array
		if ( ! $instructors ) {
			$instructors = array(
				wp_parse_args(
					array(
						'id' => $this->post->get( 'author' ),
					),
					llms_get_instructors_defaults()
				),
			);
		}

		if ( $exclude_hidden ) {
			foreach ( $instructors as $key => $instructor ) {
				if ( 'hidden' === $instructor['visibility'] ) {
					unset( $instructors[ $key ] );
				}
			}
		}

		return $instructors;

	}

	/**
	 * Format an instructors array for saving to the db.
	 *
	 * @param   array $instructors  array of full (or partial) instructor data
	 * @return  array
	 * @since   3.25.0
	 * @version 3.25.0
	 */
	public function pre_set_instructors( $instructors = array() ) {

		// we cannot allow no instructors to exist...
		// so we'll revert to the default current post_author
		if ( ! $instructors ) {

			// clear so the getter will retrieve the default author
			$this->post->set( 'instructors', array() );
			$instructors = $this->get_instructors();

		}

		// allow partial arrays to be passed & we'll fill em up with defaults
		foreach ( $instructors as $i => &$instructor ) {

			$instructor       = wp_parse_args( $instructor, llms_get_instructors_defaults() );
			$instructor['id'] = absint( $instructor['id'] );

			// remove instructors without an ID
			if ( empty( $instructor['id'] ) ) {
				unset( $instructors[ $i ] );
			}
		}

		return array_values( $instructors );

	}

	/**
	 * Save instructor information
	 *
	 * @param    array $instructors  array of course instructor information
	 * @since    3.13.0
	 * @version  3.25.0
	 */
	public function set_instructors( $instructors = array() ) {

		$instructors = $this->pre_set_instructors( $instructors );

		// set the post_author to be the first author in the array
		$this->post->set( 'author', $instructors[0]['id'] );

		// save the instructors array
		$this->post->set( 'instructors', $instructors );

		// return the instructors array
		return $instructors;

	}

}
