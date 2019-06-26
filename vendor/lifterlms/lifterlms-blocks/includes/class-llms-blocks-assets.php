<?php
/**
 * Enqueue assets
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @package LifterLMS_Blocks/Main
 *
 * @since 1.0.0
 * @version 1.4.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue assets
 *
 * @since 1.0.0
 * @since 1.4.1 Fix double slash in asset path; remove invalid frontend css dependency.
 */
class LLMS_Blocks_Assets {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'enqueue_block_assets', array( $this, 'block_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_assets' ), 999 );

	}

	/**
	 * Enqueue Gutenberg block assets for both frontend + backend.
	 *
	 * `wp-blocks`: includes block type registration and related functions.
	 *
	 * @since 1.0.0
	 * @since 1.4.1 Fix double slash in asset path & remove invalid dependency.
	 *
	 * @return void
	 */
	public function block_assets() {

		wp_enqueue_style(
			'llms-blocks',
			LLMS_BLOCKS_PLUGIN_DIR_URL . 'dist/blocks.style.build.css',
			array(),
			LLMS_BLOCKS_VERSION
		);

	}

	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 *
	 * `wp-blocks`: includes block type registration and related functions.
	 * `wp-element`: includes the WordPress Element abstraction for describing the structure of your blocks.
	 * `wp-i18n`: To internationalize the block's text.
	 *
	 * @since 1.0.0
	 * @since 1.4.1 Fix double slash in asset path.
	 *
	 * @return void
	 */
	public function editor_assets() {

		wp_enqueue_script(
			'lifterlms_blocks-cgb-block-js',
			LLMS_BLOCKS_PLUGIN_DIR_URL . 'dist/blocks.build.js',
			array( 'wp-blocks', 'wp-i18n', 'wp-element' ),
			LLMS_BLOCKS_VERSION,
			true
		);

		wp_enqueue_style(
			'lifterlms_blocks-cgb-block-editor-css',
			LLMS_BLOCKS_PLUGIN_DIR_URL . 'dist/blocks.editor.build.css',
			array( 'wp-edit-blocks' ),
			LLMS_BLOCKS_VERSION
		);

	}

}

return new LLMS_Blocks_Assets();
