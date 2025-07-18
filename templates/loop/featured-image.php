<?php
/**
 * Display a Featured Image on the Loop Tile
 *
 * @package LifterLMS/Templates
 *
 * @since  Unknown
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

// Short circuit if the featured video tile option is enabled for a course or membership.
if ( 'course' === $post->post_type || 'llms_membership' === $post->post_type ) {
	$product = llms_get_post( $post );
	if ( 'yes' === $product->get( 'tile_featured_video' ) && $product->get( 'video_embed' ) ) {
		return;
	}
}

if ( has_post_thumbnail( $post->ID ) ) {
	// Generated HTML is escaped inside the function.
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo llms_featured_img( $post->ID, 'full' );
} elseif ( llms_placeholder_img_src() ) {
	// Generated HTML is escaped inside the function.
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo llms_placeholder_img();
}
