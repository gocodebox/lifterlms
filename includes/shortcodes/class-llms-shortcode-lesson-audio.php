<?php
/**
 * LifterLMS Lesson Audio Shortcode
 *
 * [lifterlms_lesson_audio]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Outputs the lesson's featured audio where the shortcode (or the Lesson Audio block) is placed.
 *
 * @since [version]
 */
class LLMS_Shortcode_Lesson_Audio extends LLMS_Shortcode_Lesson_Video {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_lesson_audio';

	/**
	 * Featured media type output by the shortcode.
	 *
	 * @var string
	 */
	protected $type = 'audio';
}

return LLMS_Shortcode_Lesson_Audio::instance();
