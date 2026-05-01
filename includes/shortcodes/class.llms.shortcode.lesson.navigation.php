<?php
/**
 * LifterLMS Lesson Navigation Shortcode
 *
 * [lifterlms_lesson_navigation]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since 10.0.0
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Shortcode_Lesson_Navigation
 *
 * @since 10.0.0
 */
class LLMS_Shortcode_Lesson_Navigation extends LLMS_Shortcode {

	/**
	 * Shortcode tag
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_lesson_navigation';

	/**
	 * Retrieve the actual content of the shortcode
	 *
	 * $atts & $content are both filtered before being passed to get_output()
	 * output is filtered so the return of get_output() doesn't need its own filter
	 *
	 * @since 10.0.0
	 *
	 * @return string
	 */
	protected function get_output() {

		ob_start();
		lifterlms_template_lesson_navigation();
		return ob_get_clean();
	}
}

return LLMS_Shortcode_Lesson_Navigation::instance();
