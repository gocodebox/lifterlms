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

class LLMS_Shortcode_Course_Syllabus extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 * @var  string
	 */
	public $tag = 'lifterlms_course_syllabus';

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

		ob_start();
		lifterlms_template_single_syllabus();
		return ob_get_clean();

	}

}

return LLMS_Shortcode_Course_Syllabus::instance();


