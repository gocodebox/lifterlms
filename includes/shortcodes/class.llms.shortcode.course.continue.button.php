<?php
/**
 * LifterLMS Course Continue Button
 *
 * [lifterlms_course_continue_button]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 3.11.1
 * @version 3.11.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Course_Element
 *
 * @since 3.11.1
 */
class LLMS_Shortcode_Course_Continue_Button extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_course_continue_button';

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 *
	 * @since 3.11.1
	 *
	 * @return array
	 */
	protected function get_default_attributes() {
		return array(
			'course_id' => get_the_ID(),
		);
	}

	/**
	 * Call the template function for the course element
	 *
	 * @since 3.11.1
	 *
	 * @return void
	 */
	protected function template_function() {

		lifterlms_course_continue_button( $this->get_attribute( 'course_id' ) );

	}

}

return LLMS_Shortcode_Course_Continue_Button::instance();
