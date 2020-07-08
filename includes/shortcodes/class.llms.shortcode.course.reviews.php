<?php
/**
 * LifterLMS Course Reviews notice Shortcode
 *
 * [lifterlms_course_reviews]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 3.6.0
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Course_Reviews
 *
 * @since 3.6.0
 */
class LLMS_Shortcode_Course_Reviews extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_course_reviews';

	/**
	 * Call the template function for the course element
	 *
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	protected function template_function() {

		lifterlms_template_single_reviews();

	}

}

return LLMS_Shortcode_Course_Reviews::instance();
