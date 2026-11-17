<?php
/**
 * PHP Template block class file.
 *
 * @package LifterLMS_Blocks/Blocks
 *
 * @since 1.0.0
 * @version 2.3.0
 *
 * @render_hook llms_php-template_render
 */

defined( 'ABSPATH' ) || exit;

/**
 * PHP Template block.
 *
 * @since 2.3.0
 */
class LLMS_Blocks_PHP_Template_Block extends LLMS_Blocks_Abstract_Block {

	/**
	 * Block ID.
	 *
	 * @var string
	 */
	protected $id = 'php-template';

	/**
	 * Is block dynamic (rendered in PHP).
	 *
	 * @var bool
	 */
	protected $is_dynamic = true;

	/**
	 * Templates map, where the keys are the template attribute value and the values are the php file names (w/o extension).
	 *
	 * @var array
	 */
	protected $templates = array(
		'archive-course'             => 'loop-main',
		'archive-llms_membership'    => 'loop-main',
		'taxonomy-course_cat'        => 'loop-main',
		'taxonomy-course_difficulty' => 'loop-main',
		'taxonomy-course_tag'        => 'loop-main',
		'taxonomy-course_track'      => 'loop-main',
		'taxonomy-membership_cat'    => 'loop-main',
		'taxonomy-membership_tag'    => 'loop-main',
		'single-certificate'         => 'content-certificate',
		'single-no-access'           => 'content-no-access',
	);

	/**
	 * Add actions attached to the render function action.
	 *
	 * @since 2.3.0
	 *
	 * @param array  $attributes Optional. Block attributes. Default empty array.
	 * @param string $content    Optional. Block content. Default empty string.
	 * @return void
	 */
	public function add_hooks( $attributes = array(), $content = '' ) {

		add_action( $this->get_render_hook(), array( $this, 'output' ), 10 );

	}

	/**
	 * Retrieve custom block attributes.
	 *
	 * Necessary to override when creating ServerSideRender blocks.
	 *
	 * @since 2.3.0
	 *
	 * @return array
	 */
	public function get_attributes() {
		return array(
			'template' => array(
				'type'    => 'string',
				'default' => '',
			),
			'title'    => array(
				'type'    => 'string',
				'default' => '',
			),
		);
	}

	/**
	 * Output the template.
	 *
	 * @since 2.3.0
	 *
	 * @param array $attributes Optional. Block attributes. Default empty array.
	 * @return void
	 */
	public function output( $attributes = array() ) {

		if ( empty( $attributes['template'] ) ) {
			return;
		}

		/**
		 * Filters the php templates that can be render via this block.
		 *
		 * @since 2.3.0
		 *
		 * @param array $templates Templates map, where the keys are the template attribute value and the values are the php file names (w/o extension).
		 */
		$templates = apply_filters( 'llms_blocks_php_templates_block', $this->templates );

		if ( ! array_key_exists( $attributes['template'], $templates ) ) {
			return;
		}

		ob_start();

		llms_get_template( "{$templates[$attributes['template']]}.php" );

		$block_content = ob_get_clean();

		/**
		 * Filters the block html.
		 *
		 * @since 2.3.0
		 *
		 * @param string                         $block_content The block's html.
		 * @param array                          $attributes    The block's array of attributes.
		 * @param array                          $template      The template file basename to be rendered.
		 * @param LLMS_Blocks_PHP_Template_Block $block         This block object.
		 */
		$block_content = apply_filters( 'llms_blocks_render_php_template_block', $block_content, $attributes, $templates[ $attributes['template'] ], $this );

		if ( $block_content ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $block_content;
		}

	}

}

return new LLMS_Blocks_PHP_Template_Block();
