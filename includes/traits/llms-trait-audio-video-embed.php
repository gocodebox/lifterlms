<?php
/**
 * LifterLMS audio video embed trait
 *
 * @package LifterLMS/Traits
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS audio video embed trait.
 *
 * **Classes that use this trait must call {@see LLMS_Trait_Audio_Video_Embed::construct_audio_video_embed()}
 * in their constructor.**
 *
 * @since [version]
 *
 * @property string $audio_embed URL to an oEmbed enable audio URL.
 * @property string $video_embed URL to an oEmbed enable video URL.
 */
trait LLMS_Trait_Audio_Video_Embed {
	/**
	 * @inheritdoc
	 */
	abstract protected function add_properties( $props = array() );

	/**
	 * Setup properties used by this trait.
	 *
	 * **Must be called by the constructor of the class that uses this trait.**
	 *
	 * @since [version]
	 */
	protected function construct_audio_video_embed() {

		$this->add_properties(
			array(
				'audio_embed' => 'text',
				'video_embed' => 'text',
			)
		);
	}

	/**
	 * @inheritdoc
	 */
	abstract public function get( $key, $raw = false );

	/**
	 * Attempt to get oEmbed for an audio provider.
	 *
	 * Falls back to the [audio] shortcode if the oEmbed fails.
	 *
	 * @since 1.0.0
	 * @since 3.17.0 Unknown.
	 * @since [version] Refactored from `LLMS_Course` and `LLMS_Lesson`.
	 *
	 * @return string
	 */
	public function get_audio() {
		return $this->get_embed( 'audio' );
	}

	/**
	 * Get media embeds.
	 *
	 * @since 3.17.0
	 * @since 3.17.5 Unknown.
	 * @since [version] Refactored from `LLMS_Post_Model`.
	 *
	 * @param string $type Optional. Embed type ['video'|'audio']. Default is 'video'.
	 * @param string $prop Optional. Postmeta property name. Default is empty string.
	 *                     If not supplied it will default to {$type}_embed.
	 * @return string
	 */
	protected function get_embed( $type = 'video', $prop = '' ) {

		$ret = '';

		$prop = $prop ? $prop : $type . '_embed';
		$url  = $this->get( $prop );
		if ( $url ) {

			$ret = wp_oembed_get( $url );

			if ( ! $ret ) {

				$ret = do_shortcode( sprintf( '[%1$s src="%2$s"]', $type, $url ) );

			}
		}

		/**
		 * Filters the embed HTML.
		 *
		 * The first dynamic portion of this hook, `$this->model_post_type`, refers to the model's post type.
		 * For example "course", "lesson", "membership", etc...
		 * The second dynamic portion of this hook, `$type`, refers to the embed type ['video'|'audio'].
		 *
		 * @since Unknown
		 *
		 * @param array           $embed     The embed html.
		 * @param LLMS_Post_Model $llms_post The LLMS_Post_Model instance.
		 * @param string          $type      Embed type ['video'|'audio'].
		 * @param string          $prop      Postmeta property name.
		 */
		return apply_filters( "llms_{$this->model_post_type}_{$type}", $ret, $this, $type, $prop );
	}

	/**
	 * Attempt to get oEmbed for a video provider.
	 *
	 * Falls back to the [video] shortcode if the oEmbed fails.
	 *
	 * @since 1.0.0
	 * @since 3.17.0 Unknown.
	 * @since [version] Refactored from `LLMS_Course` and `LLMS_Lesson`.
	 *
	 * @return string
	 */
	public function get_video() {
		return $this->get_embed( 'video' );
	}
}
