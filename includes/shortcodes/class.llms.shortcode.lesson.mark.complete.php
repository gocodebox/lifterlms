<?php
/**
 * LifterLMS Hide Content Shortcode
 *
 * [lifterlms_lesson_mark_complete]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 3.11.1
 * @version 3.11.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Lesson_Mark_Complete
 *
 * @since 3.11.1
 */
class LLMS_Shortcode_Lesson_Mark_Complete extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_lesson_mark_complete';

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @since 3.11.1
	 *
	 * @return string
	 */
	protected function get_output() {

		ob_start();
		lifterlms_template_complete_lesson_link();
		return ob_get_clean();

	}

}

return LLMS_Shortcode_Lesson_Mark_Complete::instance();
