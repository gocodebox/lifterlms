<?php
/**
 * LifterLMS Hide Content Shortcode
 *
 * [lifterlms_lesson_mark_complete]
 *
 * @since    3.11.1
 * @version  3.11.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_Shortcode_Lesson_Mark_Complete extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var  string
	 */
	public $tag = 'lifterlms_lesson_mark_complete';

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @return   string
	 * @since    3.11.1
	 * @version  3.11.1
	 */
	protected function get_output() {

		ob_start();
		lifterlms_template_complete_lesson_link();
		return ob_get_clean();

	}

}

return LLMS_Shortcode_Lesson_Mark_Complete::instance();
