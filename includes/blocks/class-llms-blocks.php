<?php
defined( 'ABSPATH' ) || exit;

/**
 * Gutenberg Blocks
 * @since    [version]
 * @version  [version]
 */
class LLMS_Blocks {

	public function __construct() {

		if ( function_exists( 'register_block_type' ) ) {

			add_action( 'init', array( $this, 'register_blocks' ) );

		}

	}

}

return new LLMS_Blocks;
