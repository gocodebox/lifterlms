<?php
/**
 * LifterLMS Course Syllabus Shortcode
 *
 * [lifterlms_course_syllabus]
 *
 * @since    3.6.0
 * @version  3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Shortcode_Course_Syllabus extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 * @var  string
	 */
	public $tag = 'lifterlms_course_syllabus';

	/**
	 * Call the template function for the course element
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	protected function template_function() {

		lifterlms_template_single_syllabus();

	}

}

return LLMS_Shortcode_Course_Syllabus::instance();
