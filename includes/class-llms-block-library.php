<?php
/**
 * LLMS_Block_Library class file.
 *
 * @package LifterLMS/Classes
 * 
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the LifterLMS block library.
 *
 * @sinc [version]
 */
class LLMS_Block_Library {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register' ) );

	}

	/**
	 * Retrieves a list of blocks to register.
	 *
	 * @since [version]
	 *
	 * @return string[] A list of directory paths that can individually be passed to `register_block_type()`.
	 */
	private function get_blocks() {

		$blocks = array(
			'certificate-title',
		);

		$blocks = array_map( function( $id ) {
			return LLMS_PLUGIN_DIR . 'blocks/' . $id;
		}, $blocks );

		/**
		 * Filters the list of blocks to register.
		 *
		 * Each item in the resulting array will be passed to `register_block_type()`.
		 *
		 * @since [version]
		 *
		 * @param string[] $blocks A list of directory paths.
		 */
		return apply_filters( 'llms_block_library', $blocks );

	}

	/**
	 * Register all blocks in the LifterLMS block library.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register() {
		array_map( 'register_block_type', $this->get_blocks() );
	}

}

return new LLMS_Block_Library();
