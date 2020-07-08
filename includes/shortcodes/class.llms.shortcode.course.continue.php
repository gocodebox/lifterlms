<?php
/**
 * LifterLMS Course Progress & Continue Button Shortcode
 *
 * [lifterlms_course_continue]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 3.6.0
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Course_Continue
 *
 * @since 3.6.0
 */
class LLMS_Shortcode_Course_Continue extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_course_continue';

	/**
	 * Call the template function for the course element
	 *
	 * @since 3.6.0
	 *
	 * @return void
	 */
	protected function template_function() {

		lifterlms_template_single_course_progress();

	}

}

return LLMS_Shortcode_Course_Continue::instance();
