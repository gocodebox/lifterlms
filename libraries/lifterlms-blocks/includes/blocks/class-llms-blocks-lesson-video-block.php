<?php
/**
 * Lesson Video block.
 *
 * @package LifterLMS_Blocks/Blocks
 *
 * @since [version]
 * @version [version]
 *
 * @render_hook llms_lesson-video-block_render
 */

defined( 'ABSPATH' ) || exit;

/**
 * Outputs the lesson's featured video where the block is placed instead of at the top of the lesson.
 *
 * @since [version]
 */
class LLMS_Blocks_Lesson_Video_Block extends LLMS_Blocks_Abstract_Block {

	/**
	 * Block ID.
	 *
	 * @var string
	 */
	protected $id = 'lesson-video';

	/**
	 * Is block dynamic (rendered in PHP).
	 *
	 * @var bool
	 */
	protected $is_dynamic = true;

	/**
	 * Shortcode class used to render the block.
	 *
	 * @var string
	 */
	protected $shortcode = 'LLMS_Shortcode_Lesson_Video';

	/**
	 * Add actions attached to the render function action.
	 *
	 * @since [version]
	 *
	 * @param array  $attributes Optional. Block attributes. Default empty array.
	 * @param string $content    Optional. Block content. Default empty string.
	 * @return void
	 */
	public function add_hooks( $attributes = array(), $content = '' ) {
		add_action( $this->get_render_hook(), array( $this, 'output' ), 10 );
	}

	/**
	 * Output the featured media through its shortcode so the block and shortcode share the "first one only" rule.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function output() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oEmbed HTML from the lesson template.
		echo call_user_func( array( $this->shortcode, 'instance' ) )->output();
	}

	/**
	 * Remove actions attached to the render function action.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function remove_hooks() {
		remove_action( $this->get_render_hook(), array( $this, 'output' ), 10 );
	}

	/**
	 * Retrieve custom block attributes.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function get_attributes() {
		return array_merge(
			parent::get_attributes(),
			array(
				'post_id' => array(
					'type'    => 'int',
					'default' => 0,
				),
			)
		);
	}

	/**
	 * Message shown in the editor when the lesson has no featured video.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_empty_render_message() {
		if ( ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return '';
		}
		return __( 'Add a Video Embed URL in the Lesson Settings to show the lesson video here.', 'lifterlms' );
	}
}

return new LLMS_Blocks_Lesson_Video_Block();
