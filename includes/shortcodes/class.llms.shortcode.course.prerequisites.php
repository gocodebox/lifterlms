<?php
/**
 * LifterLMS Course Prerequisites notice Shortcode
 *
 * [lifterlms_course_prerequisites]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 3.6.0
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Course_Prerequisites
 *
 * @since 3.6.0
 */
class LLMS_Shortcode_Course_Prerequisites extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_course_prerequisites';

	/**
	 * Call the template function for the course element
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	protected function template_function() {
		lifterlms_template_single_prerequisites();
	}

}

return LLMS_Shortcode_Course_Prerequisites::instance();
