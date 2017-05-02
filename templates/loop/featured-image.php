<?php
/**
 * Display a Featured Image on the Loop Tile
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // End if().

global $post;

// short circuit if the featured video tile option is enabled for a course
if ( 'course' === $post->post_type ) {
	$course = llms_get_post( $post );
	if ( 'yes' === $course->get( 'tile_featured_video' ) && $course->get( 'video_embed' ) ) {
		return;
	}
}

if ( has_post_thumbnail( $post->ID ) ) {
	echo llms_featured_img( $post->ID, 'full' );
} elseif ( llms_placeholder_img_src() ) {
	echo llms_placeholder_img();
}
