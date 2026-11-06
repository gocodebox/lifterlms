<?php
/**
 * Lesson Audio block.
 *
 * @package LifterLMS_Blocks/Blocks
 *
 * @since [version]
 * @version [version]
 *
 * @render_hook llms_lesson-audio-block_render
 */

defined( 'ABSPATH' ) || exit;

/**
 * Outputs the lesson's featured audio where the block is placed instead of at the top of the lesson.
 *
 * @since [version]
 */
class LLMS_Blocks_Lesson_Audio_Block extends LLMS_Blocks_Lesson_Video_Block {

	/**
	 * Block ID.
	 *
	 * @var string
	 */
	protected $id = 'lesson-audio';

	/**
	 * Shortcode class used to render the block.
	 *
	 * @var string
	 */
	protected $shortcode = 'LLMS_Shortcode_Lesson_Audio';

	/**
	 * Message shown in the editor when the lesson has no featured audio.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_empty_render_message() {
		if ( ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return '';
		}
		return __( 'Add an Audio Embed URL in the Lesson Settings to show the lesson audio here.', 'lifterlms' );
	}
}

return new LLMS_Blocks_Lesson_Audio_Block();
