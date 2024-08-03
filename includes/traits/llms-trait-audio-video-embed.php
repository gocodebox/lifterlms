<?php
/**
 * LifterLMS audio video embed trait
 *
 * @package LifterLMS/Traits
 *
 * @since 5.3.0
 * @version 5.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS audio video embed trait.
 *
 * **Classes that use this trait must call {@see LLMS_Trait_Audio_Video_Embed::construct_audio_video_embed()}
 * in their constructor.**
 *
 * @since 5.3.0
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
	 * @since 5.3.0
	 */
	protected function construct_audio_video_embed() {

		$this->add_properties(
			array(
				'audio_embed' => 'url',
				'video_embed' => 'url',
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
	 * @since 5.3.0 Refactored from `LLMS_Course` and `LLMS_Lesson`.
	 *
	 * @return string
	 */
	public function get_audio() {
		return $this->get_embed( 'audio' );
	}

	/**
	 * @inheritdoc
	 */
	abstract protected function get_embed( $type = 'video', $prop = '' );

	/**
	 * Attempt to get oEmbed for a video provider.
	 *
	 * Falls back to the [video] shortcode if the oEmbed fails.
	 *
	 * @since 1.0.0
	 * @since 3.17.0 Unknown.
	 * @since 5.3.0 Refactored from `LLMS_Course` and `LLMS_Lesson`.
	 *
	 * @return string
	 */
	public function get_video() {
		return $this->get_embed( 'video' );
	}
}
