<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * LifterLMS Post Model Video Embeds
 *
 * @since    3.17.0
 * @version  3.17.0
 */
interface LLMS_Interface_Post_Video {

	/**
	 * Attempt to get oEmbed for an video provider
	 * Falls back to the [video] shortcode if the oEmbed fails
	 *
	 * @return   string
	 * @since    3.17.0
	 * @version  3.17.0
	 */
	public function get_video();

}
