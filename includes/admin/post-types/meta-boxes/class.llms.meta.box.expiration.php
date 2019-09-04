<?php
/**
 * Meta Box Expiration
 * Displays expiration fields for membership post. Displays only on membership post.
 *
 * @since Unknown
 * @version 3.24.0
 *
 * @deprecated 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Meta_Box_Expiration
 *
 * @since Unknown
 * @since 3.35.0 Verify nonce before processing data; sanitize $_POST data with `llms_filter_input()`.
 */
class LLMS_Meta_Box_Expiration {

	public $prefix = '_llms_';

	/**
	 * Static output class.
	 *
	 * Displays MetaBox
	 * Calls static class metabox_options
	 * Loops through meta-options array and displays appropriate fields based on type.
	 *
	 * @deprecated 3.35.0
	 *
	 * @param  object $post [WP post object]
	 * @return void
	 */
	public static function output( $post ) {
		llms_deprecated_function( 'LLMS_Meta_Box_Expiration::output()', '3.0.0' );
	}

	/**
	 * Builds array of metabox options.
	 * Array is called in output method to display options.
	 * Appropriate fields are generated based on type.
	 *
	 * @deprecated 3.35.0
	 *
	 * @return array [md array of metabox fields]
	 */
	public static function metabox_options() {

		llms_deprecated_function( 'LLMS_Meta_Box_Expiration::metabox_options()', '3.0.0' );
		return array();

	}

	/**
	 * Static save method
	 * cleans variables and saves using update_post_meta
	 *
	 * @deprecated 3.35.0
	 *
	 * @param    int    $post_id  id of post object
	 * @param    object $post     WP post object
	 * @return   void
	 */
	public static function save( $post_id, $post ) {
		llms_deprecated_function( 'LLMS_Meta_Box_Expiration::save()', '3.0.0' );
	}

}
