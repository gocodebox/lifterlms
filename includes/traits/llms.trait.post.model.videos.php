<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS Post Model video functions
 * @since    [version]
 * @version  [version]
 */
trait LLMS_Trait_Post_Model_Videos {

	/**
	 * Attempt to get oEmbed for a video provider
	 * Falls back to the [video] shortcode if the oEmbed fails
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_video() {

		$ret = '';

		if ( isset( $this->video_embed ) ) {

			$ret = wp_oembed_get( $this->get( 'video_embed' ) );

			if ( ! $ret ) {

				$ret = do_shortcode( '[video src="' . $this->get( 'video_embed' ) . '"]' );

			}
		}

		return apply_filters( 'llms_' . $this->model_post_type . '_get_video', $ret, $this );

	}

}
