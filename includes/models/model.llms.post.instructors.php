<?php
/**
 * LLMS Post Instructors
 *
 * Allow interactions with the custom multi-author functionality
 * currently enabled for Courses and Memberships only
 *
 * Rather than instantiating this class directly
 * you should use LLMS_Course->instructors() or LLMS_Membership()->instructors()
 *
 * @since    3.13.0
 * @version  3.13.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Post_Instructors {

	/**
	 * Constructor
	 * @param    mixed     $post  (obj) LLMS_Post_Model
	 *                            (obj) WP_Post
	 *                            (int) WP_Post ID
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
			$this->id = $post->get( 'id' );

		}

	}

	/**
	 * Retrieve the default attributes for a new post instructor
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_defaults() {

		return apply_filters( 'llms_post_instructors_get_defaults', array(
			'label' => __( 'Author', 'lifterlms' ),
			'visibility' => 'visible',
		), $this );

	}

	/**
	 * Retrieve course instructor information
	 * @param    boolean    $exclude_hidden  if true, excludes hidden instructors from the return array
	 * @return   array
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function get_instructors( $exclude_hidden = false ) {

		$instructors = $this->post->get( 'instructors' );

		// if empty, respond with the course author in an array
		if ( ! $instructors ) {
			$instructors = array(
				wp_parse_args( array(
					'id' => $this->post->get( 'author' ),
				), $this->get_defaults() )
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
	 * Save instructor information
	 * @param    array      $instructors  array of course instructor information
	 * @since    3.13.0
	 * @version  3.13.0
	 */
	public function set_instructors( $instructors = array() ) {

		// we cannot allow no instructors to exist...
		// so we'll revert to the devault current post_author
		if ( ! $instructors ) {

			// clear so the getter will retrieve the default author
			$this->post->set( 'instructors', array() );
			$instructors = $this->get_instructors();

		}

		// allow partial arrays to be passed & we'll fill em up with defaults
		foreach ( $instructors as &$instructor ) {
			$instructor = wp_parse_args( $instructor, $this->get_defaults() );
			$instructor['id'] = absint( $instructor['id'] );
		}

		// set the post_author to be the first author in the array
		$this->post->set( 'author', $instructors[0]['id'] );

		// save the instructors array
		$this->post->set( 'instructors', $instructors );

		// return the instructors array
		return $instructors;

	}

}
