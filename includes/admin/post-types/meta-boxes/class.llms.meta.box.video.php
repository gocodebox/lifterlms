<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Video
*
* diplays text input for oembed video
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Video {

	/**
	 * Set up video input
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$video_embed = get_post_meta( $post->ID, '_video_embed', true );

		$html = '';
		$html .= '<label for="_video_embed">' . __( 'Video Embed Code', 'lifterlms' ) . '</label> ';
		$html .= '<input type="text" class="code" name="_video_embed" id="_video-embed" value="' . $video_embed . '"/>';
		$html .= '<p>' .  __( 'Paste the embed code for your Wistia, Vimeo or Youtube videos in the box above.', 'lifterlms' ) . '</p>';

		echo $html;
	}

	public static function save( $post_id, $post ) {
		global $wpdb;

		if ( isset( $_POST['_video_embed'] ) ) {

			$video = ( llms_clean( $_POST['_video_embed']  ) );

			update_post_meta( $post_id, '_video_embed', ( $video === '' ) ? '' : $video );
			
		}
	}

}