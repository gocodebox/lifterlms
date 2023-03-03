<?php
/**
 * Enqueue assets
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @package LifterLMS_Blocks/Main
 *
 * @since 1.0.0
 * @version 1.8.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue assets
 *
 * @since 1.0.0
 * @since 1.4.1 Fix double slash in asset path; remove invalid frontend css dependency.
 * @since 1.8.0 Update asset paths & remove redundant CSS from frontend.
 */
class LLMS_Blocks_Assets {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 * @since 1.8.0 Stop outputting editor CSS on the frontend.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ), 999 );
	}

	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 *
	 * @since 1.0.0
	 * @since 1.4.1 Fix double slash in asset path.
	 * @since 1.8.0 Update asset paths and improve script dependencies.
	 *
	 * @return void
	 */
	public function editor_assets() {

		$asset = include LLMS_BLOCKS_PLUGIN_DIR . '/assets/js/llms-blocks.asset.php';

		wp_enqueue_script(
			'llms-blocks-editor',
			LLMS_BLOCKS_PLUGIN_DIR_URL . 'assets/js/llms-blocks.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'llms-blocks-editor',
			LLMS_BLOCKS_PLUGIN_DIR_URL . 'assets/css/llms-blocks.css',
			array( 'wp-edit-blocks' ),
			$asset['version']
		);

	}

}

return new LLMS_Blocks_Assets();
