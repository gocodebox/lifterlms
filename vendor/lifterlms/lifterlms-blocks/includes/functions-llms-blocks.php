<?php
/**
 * Serverside block compononent registration
 *
 * @package  LifterLMS_Blocks/Functions
 * @since    1.3.0
 * @version  1.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determine if the Classic Editor is enabled for a given post.
 *
 * @param   mixed $post WP_Post or WP_Post ID.
 * @return  boolean
 * @since   1.3.0
 * @version 1.3.0
 */
function llms_blocks_is_classic_enabled_for_post( $post ) {

	$ret = false;

	if ( class_exists( 'Classic_Editor' ) ) {

		$post = get_post( $post );
		if ( $post ) {
			$ret = ( 'classic-editor' === get_post_meta( $post->ID, 'classic-editor-remember', true ) );
		}
	}

	return apply_filters( 'llms_blocks_is_classic_enabled_for_post', $ret, $post );

}
