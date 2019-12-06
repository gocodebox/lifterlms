<?php
/**
 * Lesson Progression block.
 *
 * @package  LifterLMS_Blocks/Blocks
 *
 * @since 1.0.0
 * @version 1.7.0
 *
 * @render_hook llms_lesson-progression-block_render
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lesson progression block
 *
 * @since 1.0.0
 * @since 1.1.0 Unknown
 * @since 1.7.0 Don't output an empty render message for free lessons.
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
	 * @param   array  $attributes Optional. Block attributes. Default empty array.
	 * @param   string $content    Optional. Block content. Default empty string.
	 * @return  void
	 * @since   1.0.0
	 * @version 1.1.0
	 */
	public function add_hooks( $attributes = array(), $content = '' ) {

		add_action( $this->get_render_hook(), 'lifterlms_template_complete_lesson_link', 10 );

	}

	/**
	 * Output a message when no HTML was rendered
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	public function get_empty_render_message() {
		$lesson = llms_get_post( get_the_ID() );
		if ( $lesson && $lesson->is_free() ) {
			return '';
		}
		return parent::get_empty_render_message();
	}

	/**
	 * Retrieve custom block attributes.
	 * Necessary to override when creating ServerSideRender blocks.
	 *
	 * @return  array
	 * @since   1.0.0
	 * @version 1.0.0
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

}

return new LLMS_Blocks_Lesson_Progression_Block();
