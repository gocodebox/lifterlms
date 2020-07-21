<?php
/**
 * LLMS Post Instructors
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.13.0
 * @version 4.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Post_Instructors class
 *
 * Allow interactions with the custom multi-author functionality
 * currently enabled for Courses and Memberships only.
 *
 * Rather than instantiating this class directly
 * you should use LLMS_Course->instructors() or LLMS_Membership()->instructors()
 *
 * @since 3.13.0
 * @since 3.30.3 Explicitly define class properties.
 * @since 4.0.0  Remove deprecated method `get_defaults()`.
 * @since 4.2.0 Normalized return structure in `get_instructors()` when no instructor set.
 */
class LLMS_Post_Instructors {

	/**
	 * WP Post ID
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Instance of the post (course or membership)
	 *
	 * @var LLMS_Post_Model
	 */
	public $post;

	/**
	 * Constructor
	 *
	 * @since 3.13.0
	 *
	 * @param LLMS_Post_Model|WP_Post|int $post Post object or ID.
	 */
	public function __construct( $post ) {

		// Setup a post if post id of WP_Post is passed in.
		if ( is_numeric( $post ) || is_a( $post, 'WP_Post' ) ) {
			$post = llms_get_post( $post );
		}

		// Double check we have an LLMS_Post.
		if ( is_subclass_of( $post, 'LLMS_Post_Model' ) ) {
			$this->post = $post;
			$this->id   = $post->get( 'id' );
		}

	}

	/**
	 * Retrieve course instructor information
	 *
	 * @since 3.13.0
	 * @since 3.23.0 Unknown.
	 * @since 4.2.0 Normalize return data when no instructor data is saved.
	 *
	 * @param boolean $exclude_hidden If true, excludes hidden instructors from the return array.
	 * @return array[] {
	 *     Array or instructor data arrays.
	 *
	 *     @type int    $id         WP_User ID of the instructor user.
	 *     @type string $visibility Display visibility option for the instructor.
	 *     @type string $label      User input display noun for the instructor. EG: "Author" or "Coach" or "Instructor".
	 *     @type string $name       WP_User Display Name.
	 * }
	 */
	public function get_instructors( $exclude_hidden = false ) {

		$instructors = $this->post->get( 'instructors' );

		// If empty, respond with the course author in an array.
		if ( ! $instructors ) {
			$author_id   = $this->post->get( 'author' );
			$author      = get_userdata( $author_id );
			$instructors = array(
				wp_parse_args(
					array(
						'id'   => $author_id,
						'name' => $author ? $author->display_name : '',
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
	 * @since 3.25.0
	 *
	 * @param array $instructors Array of full (or partial) instructor data.
	 * @return array
	 */
	public function pre_set_instructors( $instructors = array() ) {

		/**
		 * We cannot allow no instructors to exist
		 * so we'll revert to the default `post_author`.
		 */
		if ( ! $instructors ) {
			// Clear so the getter will retrieve the default author.
			$this->post->set( 'instructors', array() );
			$instructors = $this->get_instructors();
		}

		// Allow partial arrays to be passed & we'll fill em up with defaults.
		foreach ( $instructors as $i => &$instructor ) {

			$instructor       = wp_parse_args( $instructor, llms_get_instructors_defaults() );
			$instructor['id'] = absint( $instructor['id'] );

			// Remove instructors without an ID.
			if ( empty( $instructor['id'] ) ) {
				unset( $instructors[ $i ] );
			}
		}

		return array_values( $instructors );

	}

	/**
	 * Save instructor information
	 *
	 * @since 3.13.0
	 * @since 3.25.0 Unknown.
	 *
	 * @param array $instructors Array of course instructor information.
	 */
	public function set_instructors( $instructors = array() ) {

		$instructors = $this->pre_set_instructors( $instructors );

		// Set the post_author to be the first author in the array.
		$this->post->set( 'author', $instructors[0]['id'] );

		// Save the instructors array.
		$this->post->set( 'instructors', $instructors );

		return $instructors;

	}

}
