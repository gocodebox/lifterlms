<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS Post Model Video Embeds
 * @since    [version]
 * @version  [version]
 */
interface LLMS_Interface_Post_Video {

	/**
	 * Attempt to get oEmbed for an video provider
	 * Falls back to the [video] shortcode if the oEmbed fails
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_video();

}
