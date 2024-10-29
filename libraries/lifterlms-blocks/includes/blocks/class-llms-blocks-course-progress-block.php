<?php
/**
 * Course progress bar block
 *
 * @package LifterLMS_Blocks/Blocks
 *
 * @since 1.9.0
 * @version 1.9.0
 *
 * @render_hook llms_course-progress_block_render
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course progress block class.
 */
class LLMS_Blocks_Course_Progress_Block extends LLMS_Blocks_Abstract_Block {

	/**
	 * Block ID.
	 *
	 * @var string
	 */
	protected $id = 'course-progress';

	/**
	 * Is block dynamic (rendered in PHP).
	 *
	 * @var bool
	 */
	protected $is_dynamic = true;

	/**
	 * Add actions attached to the render function action.
	 *
	 * @since 1.9.0
	 *
	 * @param array  $attributes Optional. Block attributes. Default empty array.
	 * @param string $content    Optional. Block content. Default empty string.
	 * @return void
	 */
	public function add_hooks( $attributes = array(), $content = '' ) {

		add_action( $this->get_render_hook(), array( $this, 'output' ), 10 );
	}

	/**
	 * Output the course progress bar
	 *
	 * @since 1.9.0
	 *
	 * @param array $attributes Optional. Block attributes. Default empty array.
	 * @return void
	 */
	public function output( $attributes = array() ) {

		$block_content = '';
		$progress      = do_shortcode( '[lifterlms_course_progress check_enrollment=1]' );
		$class         = empty( $attributes['className'] ) ? '' : $attributes['className'];

		if ( $progress ) {
			$block_content = sprintf(
				'<div class="wp-block-%1$s-%2$s%3$s">%4$s</div>',
				$this->vendor,
				$this->id,
				// Take into account the custom class attribute.
				empty( $attributes['className'] ) ? '' : ' ' . esc_attr( $attributes['className'] ),
				$progress
			);
		}

		/**
		 * Filters the block html
		 *
		 * @since 1.9.0
		 *
		 * @param string                            $block_content The block's html.
		 * @param array                             $attributes    The block's array of attributes.
		 * @param LLMS_Blocks_Course_Progress_Block $block         This block object.
		 */
		$block_content = apply_filters( 'llms_blocks_render_course_progress_block', $block_content, $attributes, $this );

		if ( $block_content ) {
			echo wp_kses( $block_content, LLMS_ALLOWED_HTML_FORM_FIELDS );
		}
	}
}

return new LLMS_Blocks_Course_Progress_Block();
