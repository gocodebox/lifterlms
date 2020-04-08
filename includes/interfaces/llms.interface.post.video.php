<?php
/**
 * LifterLMS Post Model Video Embeds
 *
 * @package LifterLMS/Interfaces
 *
 * @since 3.17.0
 * @version 3.17.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Interface_Post_Video
 *
 * @since 3.17.0
 */
interface LLMS_Interface_Post_Video {

	/**
	 * Attempt to get oEmbed for an video provider
	 *
	 * Falls back to the [video] shortcode if the oEmbed fails
	 *
	 * @since 3.17.0
	 *
	 * @return string
	 */
	public function get_video();

}
