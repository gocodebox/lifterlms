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
 * @since [version]
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
			'certificate-title' => null,
		);

		foreach ( $blocks as $id => &$block ) {
			$block = is_null( $block ) ? LLMS_PLUGIN_DIR . 'blocks/' . $id : $block;
		}

		return $blocks;

	}

	/**
	 * Register all blocks in the LifterLMS block library.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function register() {

		$registry = WP_Block_Type_Registry::get_instance();

		foreach ( $this->get_blocks() as $id => $block ) {

			if ( $registry->is_registered( 'llms/' . $id ) ) {
				continue;
			}

			register_block_type( $block );

		}

	}

}

return new LLMS_Block_Library();
