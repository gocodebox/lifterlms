<?php
/**
 * LifterLMS Course Tracks
 * @since    3.0.0
 * @version  3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Track {

	public $taxonomy = 'course_track';

	/**
	 * Constructor
	 *
	 * @param    int|string|obj     $term   term_id, term_slug, or instance of a WP_Term
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function __construct( $term ) {

		if ( is_numeric( $term ) ) {

			$this->term = get_term( $term, $this->taxonomy );

		} elseif ( is_string( $term ) ) {

			$this->term = get_term_by( 'slug', $term, $this->taxonomy );

		} elseif ( $term instanceof WP_Term ) {

			$this->term = $term;

		}

	}

	/**
	 * Get an array of WP Posts for the courses in the track
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_courses() {

		// no posts in the term, return an empty array
		if ( 0 === $this->term->count ) {
			return array();
		}

		// get posts
		$q = new WP_Query( array(
			'post_status' => 'publish',
			'post_type' => 'course',
			'posts_per_page' => -1,
			'tax_query' => array(
				array(
					'field' => 'id',
					'include_children' => false,
					'taxonomy' => $this->taxonomy,
					'terms' => $this->term->term_id,
				),
			),
		) );

		// return posts
		if ( $q->have_posts() ) {
			return $q->posts;
		} else {
			return array();
		}
	}

	/**
	 * Get a permalink to the track's archive page
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function get_permalink() {
		return get_term_link( $this->term->term_id, $this->taxonomy );
	}

	/**
	 * Get the track's title
	 * @return   string
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public function get_title() {
		return $this->term->name;
	}

}
