<?php
/**
 * Course Short Description
 *
 * @author 		codeBOX
 * @category 	Admin
 * @package 	lifterLMS/Admin/Meta Boxes
 * @version     0.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * LLMS_Meta_Box_Course_Video Embed
 */
class LLMS_Meta_Box_Course_Video {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
		global $post;

		$video_embed = get_post_meta( $post->ID, '_video_embed', true );

		$html = '';
		$html .= '<label class="screen-reader-text" for="_video_embed">' . __( 'Video Embed Code', 'lifterlms' ) . '</label>';
		$html .= '<textarea class="large-text llms-large-text code" name="_video_embed" tabindex="6" id="_video-embed">' . $video_embed . '</textarea>';
		$html .= '<p>' .  __( 'Paste the embed code for your Wistia, Vimeo or Youtube videos in the box above.', 'lifterlms' ) . '</p>';

		echo $html;
	}

	public static function save( $post_id, $post ) {
		global $wpdb;

		if ( isset( $_POST['_video_embed'] ) )
			update_post_meta( $post_id, '_video_embed', ( $_POST['_video_embed'] === '' ) ? '' : $_POST['_video_embed'] );
	}

}