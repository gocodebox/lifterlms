<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS Post Model Audio Embeds
 * @since    3.17.0
 * @version  3.17.0
 */
interface LLMS_Interface_Post_Audio {

	/**
	 * Attempt to get oEmbed for an audio provider
	 * Falls back to the [audio] shortcode if the oEmbed fails
	 * @return   string
	 * @since    3.17.0
	 * @version  3.17.0
	 */
	public function get_audio();

}
