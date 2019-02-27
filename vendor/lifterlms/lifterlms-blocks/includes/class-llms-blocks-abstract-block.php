<?php
/**
 * Common block registration methods.
 *
 * @package  LifterLMS_Blocks/Abstracts
 * @since    1.0.0
 * @version  1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Block class.
 */
abstract class LLMS_Blocks_Abstract_Block {

	/**
	 * Block vendor ID.
	 *
	 * @var string
	 */
	protected $vendor = 'llms';

	/**
	 * Block ID.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Is block dynamic (rendered in PHP).
	 *
	 * @var bool
	 */
	protected $is_dynamic = false;

	/**
	 * Constructor.
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function __construct() {

		if ( $this->is_dynamic ) {

			register_block_type(
				$this->get_block_id(),
				array(
					'attributes'      => $this->get_attributes(),
					'render_callback' => array( $this, 'render_callback' ),
				)
			);

		}

		$this->register_meta();

	}

	/**
	 * Add hooks stub.
	 * Extending classes can use this class to add hooks attached to the render function action.
	 *
	 * @param   array  $attributes Optional. Block attributes. Default empty array.
	 * @param   string $content    Optional. Block content. Default empty string.
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function add_hooks( $attributes = array(), $content = '' ) {}

	/**
	 * Retrieve custom block attributes.
	 * Necessary to override when creating ServerSideRender blocks.
	 *
	 * @return  array
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_attributes() {
		return LLMS_Blocks_Visibility::get_attributes();
	}

	/**
	 * Retrieve the ID/Name of the block.
	 *
	 * @return  string
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_block_id() {
		return sprintf( '%1$s/%2$s', $this->vendor, $this->id );
	}

	/**
	 * Output a message when no HTML was rendered
	 *
	 * @return  string
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_empty_render_message() {
		return __( 'No HTML was returned.', 'lifterlms' );
	}

	/**
	 * Retrieve a string which can be used to render the block.
	 *
	 * @return  string
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_render_hook() {
		return sprintf( '%1$s_%2$s_block_render', $this->vendor, $this->id );
	}

	/**
	 * Removed hooks stub.
	 * Extending classes can use this class to remove hooks attached to the render function action.
	 *
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function remove_hooks() {}

	/**
	 * Renders the block type output for given attributes.
	 *
	 * @param   array  $attributes Optional. Block attributes. Default empty array.
	 * @param   string $content    Optional. Block content. Default empty string.
	 * @return  string
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function render_callback( $attributes = array(), $content = '' ) {

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
	 * @return  void
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_meta() {}

}
