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
			'certificate-title' => array(
				'path'       => null,
				'post_types' => array(
					'llms_certificate',
					'llms_my_certificate',
				),
			),
		);

		// Add default path to all blocks.
		foreach ( $blocks as $id => &$block ) {
			$block['path'] = is_null( $block['path'] ) ? LLMS_PLUGIN_DIR . 'blocks/' . $id : $block['path'];
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

		foreach ( $this->get_blocks() as $id => $block ) {

			if ( $this->should_register( $id, $block ) ) {
				register_block_type( $block['path'] );
			}

		}

	}

	/**
	 * Determines whether or not the block should be registered.
	 *
	 * There's no "good" way to register a block only for a specific post type(s) or context (such
	 * as the post editor only and not the widgets editor).
	 *
	 * This method uses the `$pagenow` global and query string variables to interpret the current
	 * screen context and register the block only in the intended context.
	 *
	 * This creates issues if the block list is retrieve via the REST API. But we can't avoid this
	 * given the current APIs. Especially since the WP core throw's a notice on the widgets screen
	 * if a block is registered with a script that relies on `wp-editor` as a dependency.
	 *
	 * See related issue links below.
	 *
	 * @since [version]
	 *
	 * @link https://github.com/WordPress/gutenberg/issues/28517
	 * @link https://github.com/WordPress/gutenberg/issues/12931
	 *
	 * @param string $id    The block's id (without the `llms/` prefix).
	 * @param array  $block Array of block data.
	 * @return boolean
	 */
	private function should_register( $id, $block ) {

		// Prevent errors if the block is already registered.
		$registry = WP_Block_Type_Registry::get_instance();
		if ( $registry->is_registered( 'llms/' . $id ) ) {
			return false;
		}

		// Ensure the block is only registered in the correct context.
		global $pagenow;
		$post_type = null;
		if ( 'post.php' === $pagenow ) {
			$post_type = get_post_type( llms_filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT ) );
		} elseif ( 'post-new.php' === $pagenow ) {
			$post_type = llms_filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING );
			$post_type = $post_type ? $post_type : 'post'; // If `$_GET` is not set it's because it's a basic post.
		}

		if ( ! is_null( $post_type ) && in_array( $post_type, $block['post_types'], true ) ) {
			return true;
		}

		return false;

	}

}

return new LLMS_Block_Library();
