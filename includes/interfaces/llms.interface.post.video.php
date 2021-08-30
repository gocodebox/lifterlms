<?php
/**
 * LifterLMS Post Model Video Embeds
 *
 * @package LifterLMS/Interfaces
 *
 * @since 3.17.0
 * @version 5.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Interface_Post_Video
 *
 * @since 3.17.0
 * @deprecated 5.3.0 Use {@see LLMS_Trait_Audio_Video_Embed}.
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
