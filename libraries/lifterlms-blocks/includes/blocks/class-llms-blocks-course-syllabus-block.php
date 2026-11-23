<?php
/**
 * Course syllabus block.
 *
 * @package LifterLMS_Blocks/Blocks
 *
 * @since 1.0.0
 * @version 2.5.0
 * @deprecated 2.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course syllabus block class.
 */
class LLMS_Blocks_Course_Syllabus_Block extends LLMS_Blocks_Abstract_Block {

	/**
	 * Block ID.
	 *
	 * @var string
	 */
	protected $id = 'course-syllabus';

	/**
	 * Is block dynamic (rendered in PHP).
	 *
	 * @since 2.5.0 Changed to `false` to prevent the block from being registered.
	 *
	 * @var bool
	 */
	protected $is_dynamic = false;

	/**
	 * Add actions attached to the render function action.
	 *
	 * @since 1.0.0
	 * @deprecated 2.5.0
	 *
	 * @param array  $attributes Optional. Block attributes. Default empty array.
	 * @param string $content    Optional. Block content. Default empty string.
	 * @return void
	 */
	public function add_hooks( $attributes = array(), $content = '' ) {
		llms_deprecated_function( __METHOD__, '2.5.0' );

		add_action( $this->get_render_hook(), 'lifterlms_template_single_syllabus', 10 );
	}

	/**
	 * Retrieve custom block attributes.
	 * Necessary to override when creating ServerSideRender blocks.
	 *
	 * @since 1.0.0
	 * @deprecated 2.5.0
	 *
	 * @return array
	 */
	public function get_attributes() {
		llms_deprecated_function( __METHOD__, '2.5.0' );

		return array_merge(
			parent::get_attributes(),
			array(
				'course_id' => array(
					'type'    => 'int',
					'default' => 0,
				),
			)
		);
	}

	/**
	 * Retrieve the ID/Name of the block.
	 *
	 * @since 1.0.0
	 * @deprecated 2.5.0
	 *
	 * @return string
	 */
	public function get_block_id() {
		llms_deprecated_function( __METHOD__, '2.5.0' );

		return sprintf( '%1$s/%2$s', $this->vendor, $this->id );
	}

	/**
	 * Output a message when no HTML was rendered.
	 *
	 * @since 1.0.0
	 * @since 1.8.0 Don't output empty render messages on the frontend.
	 * @deprecated 2.5.0
	 *
	 * @return string
	 */
	public function get_empty_render_message() {
		llms_deprecated_function( __METHOD__, '2.5.0' );

		if ( ! is_admin() ) {
			return '';
		}

		return __( 'No HTML was returned.', 'lifterlms' );
	}

	/**
	 * Retrieve a string which can be used to render the block.
	 *
	 * @since 1.0.0
	 * @deprecated 2.5.0
	 *
	 * @return string
	 */
	public function get_render_hook() {
		llms_deprecated_function( __METHOD__, '2.5.0' );

		return sprintf( '%1$s_%2$s_block_render', $this->vendor, $this->id );
	}

	/**
	 * Removed hooks stub.
	 *
	 * Extending classes can use this class to remove hooks attached to the render function action.
	 *
	 * @since 1.0.0
	 * @deprecated 2.5.0
	 *
	 * @return void
	 */
	public function remove_hooks() {
		llms_deprecated_function( __METHOD__, '2.5.0' );
	}

	/**
	 * Renders the block type output for given attributes.
	 *
	 * @since 1.0.0
	 * @deprecated 2.5.0
	 *
	 * @param array  $attributes Optional. Block attributes. Default empty array.
	 * @param string $content    Optional. Block content. Default empty string.
	 * @return string
	 */
	public function render_callback( $attributes = array(), $content = '' ) {
		llms_deprecated_function( __METHOD__, '2.5.0' );

		$this->add_hooks( $attributes, $content );

		ob_start();
		do_action( $this->get_render_hook(), $attributes, $content );
		$ret = ob_get_clean();

		$this->remove_hooks();

		if ( ! $ret ) {
			$ret = $this->get_empty_render_message();
		}

		return $ret;
	}

	/**
	 * Register meta attributes stub.
	 *
	 * Called after registering the block type.
	 *
	 * @since 1.0.0
	 * @deprecated 2.5.0
	 *
	 * @return void
	 */
	public function register_meta() {
		llms_deprecated_function( __METHOD__, '2.5.0' );
	}
}
