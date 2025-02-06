<?php
/**
 * Lesson Progression block.
 *
 * Render hook: llms_lesson-progression-block_render
 *
 * @package  LifterLMS_Blocks/Blocks
 *
 * @since 1.0.0
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lesson progression block
 *
 * @since 1.0.0
 * @since 1.1.0 Unknown
 * @since 1.7.0 Don't output an empty render message for free lessons.
 * @since 1.8.0 Register meta data used by the block editor.
 */
class LLMS_Blocks_Lesson_Progression_Block extends LLMS_Blocks_Abstract_Block {

	/**
	 * Block ID.
	 *
	 * @var string
	 */
	protected $id = 'lesson-progression';

	/**
	 * Is block dynamic (rendered in PHP).
	 *
	 * @var bool
	 */
	protected $is_dynamic = true;

	/**
	 * Add actions attached to the render function action.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Unknown.
	 *
	 * @param array  $attributes Optional. Block attributes. Default empty array.
	 * @param string $content    Optional. Block content. Default empty string.
	 * @return void
	 */
	public function add_hooks( $attributes = array(), $content = '' ) {

		add_action( $this->get_render_hook(), 'lifterlms_template_complete_lesson_link', 10 );

	}

	/**
	 * Output a message when no HTML was rendered
	 *
	 * @since 1.7.0
	 * @since 2.0.0 Ensure the queried object is an `LLMS_Lesson` before checking if it's free.
	 *
	 * @return string
	 */
	public function get_empty_render_message() {
		$lesson = llms_get_post( get_the_ID() );
		if ( $lesson && is_a( $lesson, 'LLMS_Lesson' ) && $lesson->is_free() ) {
			return '';
		}
		return parent::get_empty_render_message();
	}

	/**
	 * Retrieve custom block attributes.
	 *
	 * Necessary to override when creating ServerSideRender blocks.
	 *
	 * @since 1.0.0
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
	 * Register meta attributes.
	 *
	 * Called after registering the block type.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_meta() {

		register_meta(
			'post',
			'_llms_quiz',
			array(
				'object_subtype'    => 'lesson',
				'sanitize_callback' => 'absint',
				'auth_callback'     => '__return_true',
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
			)
		);

	}

}

return new LLMS_Blocks_Lesson_Progression_Block();
