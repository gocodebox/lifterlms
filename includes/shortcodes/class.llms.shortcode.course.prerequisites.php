<?php
/**
 * LifterLMS Course Prerequisites notice Shortcode
 *
 * [lifterlms_course_prerequisites]
 *
 * @since    3.6.0
 * @version  3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Shortcode_Course_Prerequisites extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'lifterlms_course_prerequisites';

	/**
	 * Call the template function for the course element
	 *
	 * @return   void
	 * @since    3.6.0
	 * @version  3.6.0
	 */
	protected function template_function() {

		lifterlms_template_single_prerequisites();

	}

}

return LLMS_Shortcode_Course_Prerequisites::instance();
