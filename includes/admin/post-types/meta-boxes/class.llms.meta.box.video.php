<?php
defined( 'ABSPATH' ) || exit;

/**
 * Meta Box Video
 * displays text input for oembed video
 *
 * @since    ??
 * @version  3.24.0
 * @deprecated 3.35.0
 */
class LLMS_Meta_Box_Video {

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
		llms_deprecated_function( 'LLMS_Meta_Box_Video::output()', '3.0.0' );
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
		llms_deprecated_function( 'LLMS_Meta_Box_Video::save()', '3.0.0' );
	}

}
