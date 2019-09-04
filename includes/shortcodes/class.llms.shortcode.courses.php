<?php
/**
 * LifterLMS Courses Shortcode
 *
 * [lifterlms_courses]
 *
 * @since 3.14.0
 * @version 3.31.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Courses Shortcode class.
 *
 * @since 3.14.0
 * @since 3.30.2 Output a message instead of the entire course catalog when "mine" is used and and current student is not enrolled in any courses.
 * @since 3.31.0 Adjusted several private methods to be protected.
 */
class LLMS_Shortcode_Courses extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'lifterlms_courses';

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @return   array
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	protected function get_default_attributes() {
		return array(
			'category'       => '',
			'hidden'         => 'yes',
			'id'             => '', // allow comma-separated list of course ids
			'mine'           => 'no',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'orderby'        => 'title',
		);
	}

	/**
	 * Retrieve an array of post ids based on submitted ID parameter and the mine parameter
	 *
	 * @since 3.14.0
	 * @since 3.31.0 Changed access from private to protected.
	 *
	 * @return   array
	 */
	protected function get_post__in() {

		$ids     = array();
		$post_id = $this->get_attribute( 'id' );
		if ( $post_id ) {
			$ids = explode( ',', $post_id ); // allow multiple ids to be passed
			$ids = array_map( 'trim', $ids );
		}

		$student = llms_get_student();

		$mine = $this->get_attribute( 'mine' );
		if ( in_array( $mine, array( 'any', 'cancelled', 'enrolled', 'expired' ) ) ) {

			$courses = $student->get_courses(
				array(
					'limit'  => 1000,
					'status' => $this->get_attribute( 'mine' ),
				)
			);

			$ids = $ids ? array_intersect( $ids, $courses['results'] ) : $courses['results'];

		}

		return $ids;
	}

	/**
	 * Retrieve the tax query based on submitted category & visibility
	 *
	 * @since 3.14.0
	 * @since 3.31.0 Changed access from private to protected.
	 *
	 * @return   array|string
	 */
	protected function get_tax_query() {

		$has_tax_query = false;

		$tax_query = array(
			'relation' => 'AND',
		);

		$category = $this->get_attribute( 'category' );
		if ( $category ) {
			$tax_query[]   = array(
				'taxonomy' => 'course_cat',
				'field'    => 'slug',
				'terms'    => $category,
			);
			$has_tax_query = true;
		}

		$hidden = $this->get_attribute( 'hidden' );
		if ( 'no' === $hidden ) {

			$terms = wp_list_pluck(
				get_terms(
					array(
						'taxonomy'   => 'llms_product_visibility',
						'hide_empty' => false,
					)
				),
				'term_taxonomy_id',
				'name'
			);

			$tax_query[]   = array(
				'field'    => 'term_taxonomy_id',
				'operator' => 'NOT IN',
				'taxonomy' => 'llms_product_visibility',
				'terms'    => array( $terms['hidden'] ),
			);
			$has_tax_query = true;
		}

		return $has_tax_query ? $tax_query : '';

	}

	/**
	 * Retrieve a WP_Query based on all submitted parameters
	 *
	 * @since 3.14.0
	 * @since 3.31.0 Changed access from private to protected.
	 *
	 * @return mixed WP_Query
	 */
	protected function get_wp_query() {

		$args = array(
			'paged'          => get_query_var( 'paged' ),
			'post__in'       => $this->get_post__in(),
			'post_type'      => 'course',
			'post_status'    => $this->get_attribute( 'post_status' ),
			'tax_query'      => $this->get_tax_query(),
			'posts_per_page' => $this->get_attribute( 'posts_per_page' ),
			'order'          => $this->get_attribute( 'order' ),
			'orderby'        => $this->get_attribute( 'orderby' ),
		);

		return new WP_Query( $args );

	}

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @since 3.14.0
	 * @since 3.30.2 Output a message instead of the entire course catalog when "mine" is used and and current student is not enrolled in any courses.
	 *
	 * @return string
	 */
	protected function get_output() {

		$this->enqueue_script( 'llms-jquery-matchheight' );

		ob_start();

		// if we're outputting a "My Courses" list and we don't have a student output login info
		if ( 'no' !== $this->get_attribute( 'mine' ) && ! llms_get_student() ) {

			printf(
				__( 'You must be logged in to view this information. Click %1$shere%2$s to login.', 'lifterlms' ),
				'<a href="' . llms_get_page_url( 'myaccount' ) . '">',
				'</a>'
			);

		} else {

			if ( 'no' !== $this->get_attribute( 'mine' ) && ! $this->get_post__in() ) {

				printf( '<p>%s</p>', __( 'No courses found.', 'lifterlms' ) );

			} else {

				lifterlms_loop( $this->get_wp_query() );

			}
		}

		return ob_get_clean();

	}

}

return LLMS_Shortcode_Courses::instance();
