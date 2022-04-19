<?php
/**
 * LLMS_Block_Library class file.
 *
 * @package LifterLMS/Classes
 *
 * @since 6.0.0
 * @version 6.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load the LifterLMS block library.
 *
 * @since 6.0.0
 */
class LLMS_Block_Library {

	/**
	 * Constructor.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register' ) );

		add_filter( 'block_editor_settings_all', array( $this, 'modify_editor_settings' ), 100, 2 );

	}

	/**
	 * Retrieves a list of blocks to register.
	 *
	 * @since 6.0.0
	 *
	 * @return string[] A list of directory paths that can individually be passed to `register_block_type()`.
	 */
	private function get_blocks() {

		$blocks = array();

		if ( llms_is_block_editor_supported_for_certificates() ) {
			$blocks['certificate-title'] = array(
				'path'       => null,
				'post_types' => array(
					'llms_certificate',
					'llms_my_certificate',
				),
			);
		}

		// Add default path to all blocks.
		foreach ( $blocks as $id => &$block ) {
			$block['path'] = is_null( $block['path'] ) ? LLMS_PLUGIN_DIR . 'blocks/' . $id : $block['path'];
		}

		return $blocks;

	}

	/**
	 * Loads custom fonts for the llms/certificate-title block.
	 *
	 * @since 6.0.0
	 *
	 * @param array                   $settings Editor settings.
	 * @param WP_Block_Editor_Context $context  Current block editor context.
	 * @return array
	 */
	public function modify_editor_settings( $settings, $context ) {

		// Only load fonts when in post editor context for a certificate post type.
		if ( ! empty( $context->post ) && in_array( $context->post->post_type, array( 'llms_certificate', 'llms_my_certificate' ), true ) ) {

			$theme_fonts = $settings['__experimentalFeatures']['typography']['fontFamilies']['theme'] ?? array();

			$fonts        = llms_get_certificate_fonts();
			$custom_fonts = array_map(
				function( $slug, $font_data ) {
					unset( $font_data['href'] );
					$font_data['slug'] = $slug;
					return $font_data;
				},
				array_keys( $fonts ),
				$fonts
			);

			_wp_array_set(
				$settings,
				array(
					'__experimentalFeatures',
					'blocks',
					'llms/certificate-title',
					'typography',
					'fontFamilies',
					'custom',
				),
				array_merge( $theme_fonts, array_filter( $custom_fonts ) )
			);

		}

		return $settings;

	}

	/**
	 * Register all blocks in the LifterLMS block library.
	 *
	 * @since 6.0.0
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
	 * @since 6.0.0
	 * @since 6.4.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
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
			$id        = llms_filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );
			$post_type = $id ? get_post_type( $id ) : $post_type;
		} elseif ( 'post-new.php' === $pagenow ) {
			$post_type = llms_filter_input( INPUT_GET, 'post_type' );
			$post_type = $post_type ? $post_type : 'post'; // If `$_GET` is not set it's because it's a basic post.
		}

		if ( ! is_null( $post_type ) && in_array( $post_type, $block['post_types'], true ) ) {
			return true;
		}

		return false;

	}

}

return new LLMS_Block_Library();
