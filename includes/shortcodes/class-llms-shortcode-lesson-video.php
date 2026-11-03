<?php
/**
 * LifterLMS Lesson Video Shortcode
 *
 * [lifterlms_lesson_video]
 *
 * @package LifterLMS/Shortcodes/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Outputs the lesson's featured video where the shortcode (or the Lesson Video block) is placed.
 *
 * Only the first shortcode or block in the lesson content is rendered.
 *
 * @since [version]
 */
class LLMS_Shortcode_Lesson_Video extends LLMS_Shortcode {

	/**
	 * Shortcode tag.
	 *
	 * @var string
	 */
	public $tag = 'lifterlms_lesson_video';

	/**
	 * Featured media type output by the shortcode.
	 *
	 * @var string
	 */
	protected $type = 'video';

	/**
	 * Media already rendered during the current `the_content` pass, keyed by type and lesson ID.
	 *
	 * @var array
	 */
	private static $rendered = array();

	/**
	 * Reset the rendered media list at the start of each `the_content` pass.
	 *
	 * Content can be filtered more than once per request (excerpts, SEO plugins), and
	 * each pass needs to render its first shortcode or block.
	 *
	 * @since [version]
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function reset_rendered( $content ) {
		self::$rendered = array();
		return $content;
	}

	/**
	 * Retrieve the actual content of the shortcode.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	protected function get_output() {

		$key = $this->type . '-' . get_the_ID();
		if ( isset( self::$rendered[ $key ] ) ) {
			return '';
		}
		self::$rendered[ $key ] = true;

		ob_start();
		llms_template_single_lesson_media( $this->type, 'inline' );
		return ob_get_clean();
	}
}

add_filter( 'the_content', array( 'LLMS_Shortcode_Lesson_Video', 'reset_rendered' ), 1 );

return LLMS_Shortcode_Lesson_Video::instance();
