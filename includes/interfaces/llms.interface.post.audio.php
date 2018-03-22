<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS Post Model Audio Embeds
 * @since    [version]
 * @version  [version]
 */
interface LLMS_Interface_Post_Audio {

	/**
	 * Attempt to get oEmbed for an audio provider
	 * Falls back to the [audio] shortcode if the oEmbed fails
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_audio();

}
