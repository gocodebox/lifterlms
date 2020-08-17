<?php
/**
 * Common Shortcode for course element templates
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.6.0
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Common Shortcode for course element templates abstract class
 *
 * @since 3.6.0
 */
abstract class LLMS_Shortcode_Course_Element extends LLMS_Shortcode {

	/**
	 * Call the template function for the course element
	 *
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	abstract protected function template_function();

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @return   array
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	protected function get_default_attributes() {
		return array(
			'course_id' => get_the_ID(),
		);
	}

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @return   string
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	protected function get_output() {

		// Get a reference to the current page where the shortcode is displayed.
		global $post;
		$current_post = $post;

		$course = get_post( $this->get_attribute( 'course_id' ) );

		// We don't have a post object to proceed with.
		if ( ! $course ) {
			return '';
		}

		if ( 'course' !== $course->post_type ) {
			// Get the parent.
			$parent = llms_get_post_parent_course( $course );

			// Post type doesn't have a parent so we can't display a syllabus.
			if ( ! $parent ) {
				return '';
			}

			// We have a course.
			$course = $parent->post;

		}

		ob_start();

		// Hack the global so our syllabus template works.
		if ( $course->ID != $current_post->ID ) {
			$post = $course;
		}

		$this->template_function();

		// Restore the global.
		if ( $course->ID != $current_post->ID ) {
			$post = $current_post;
		}

		return ob_get_clean();

	}

}
