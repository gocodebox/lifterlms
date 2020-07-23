<?php
/**
 * LifterLMS Course Syllabus Shortcode
 *
 * [lifterlms_course_syllabus]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 3.6.0
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Course_Syllabus
 *
 * @since 3.6.0
 */
class LLMS_Shortcode_Course_Syllabus extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_course_syllabus';

	/**
	 * Call the template function for the course element
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	protected function template_function() {

		lifterlms_template_single_syllabus();

	}

}

return LLMS_Shortcode_Course_Syllabus::instance();
