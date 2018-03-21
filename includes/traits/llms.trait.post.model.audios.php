<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * LifterLMS Post Model Audio functions
 * @since    [version]
 * @version  [version]
 */
trait LLMS_Trait_Post_Model_Audios {

	/**
	 * Attempt to get oEmbed for a audio provider
	 * Falls back to the [audio] shortcode if the oEmbed fails
	 * @return   string
	 * @since    [version]
	 * @version  [version]
	 */
	public function get_audio() {

		$ret = '';

		if ( isset( $this->audio_embed ) ) {

			$ret = wp_oembed_get( $this->get( 'audio_embed' ) );

			if ( ! $ret ) {

				$ret = do_shortcode( '[audio src="' . $this->get( 'audio_embed' ) . '"]' );

			}
		}

		return apply_filters( 'llms_' . $this->model_post_type . '_get_audio', $ret, $this );

	}

}
