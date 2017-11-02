<?php
/**
 * LifterLMS Course Continue Button
 *
 * [lifterlms_course_continue_button]
 *
 * @since    3.11.1
 * @version  3.11.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Shortcode_Course_Continue_Button extends LLMS_Shortcode_Course_Element {

	/**
	 * Shortcode tag
	 * @var  string
	 */
	public $tag = 'lifterlms_course_continue_button';

	/**
	 * Retrieves an array of default attributes which are automatically merged
	 * with the user submitted attributes and passed to $this->get_output()
	 * @return   array
	 * @since    3.11.1
	 * @version  3.11.1
	 */
	protected function get_default_attributes() {
		return array(
			'course_id' => get_the_ID(),
		);
	}

	/**
	 * Call the template function for the course element
	 * @return   void
	 * @since    3.11.1
	 * @version  3.11.1
	 */
	protected function template_function() {

		lifterlms_course_continue_button( $this->get_attribute( 'course_id' ) );

	}

}

return LLMS_Shortcode_Course_Continue_Button::instance();
