<?php
/**
 * LifterLMS Course Instructors
 *
 * [lifterlms_course_instructors]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Course_Instructors
 *
 * @since [version]
 */
class LLMS_Shortcode_Course_Instructors extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_course_instructors';

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_default_attributes() {
		return array();
	}

	/**
	 * Call the template function for the course element
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function template_function() {

		llms_template_instructors();
	}
}

return LLMS_Shortcode_Course_Instructors::instance();
