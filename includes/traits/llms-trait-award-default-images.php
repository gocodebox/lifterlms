<?php
/**
 * LLMS_Trait_Award_Default_Images
 *
 * @package LifterLMS/Traits
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default image getters for LifterLMS awards.
 *
 * Classes that utilize this trait should declare a protected class property `$award_type`, which
 * is used to define the award's type ID. Core supported types are 'achievement' and 'certificate'.
 *
 * @since 6.0.0
 */
trait LLMS_Trait_Award_Default_Images {

	/**
	 * Retrieve the default image source for the given engagement type.
	 *
	 * The method name is not a typo. This retrieves the default image used when the default image
	 * option is not set or doesn't exist. Thus, it retrieves the default default. Thumbs up.
	 *
	 * This method retrieves the image stored in the plugin's assets directory. The images
	 * were updated with the release of this method and allows usage of the previous version's
	 * images via the filter {@see llms_use_legacy_engagement_images}.
	 *
	 * The legacy default image URL is returned if the current certificate version is 1 and the
	 * 'llms_has_achievements_with_legacy_default_image' option is 'yes' or the type is achievement and the
	 * 'llms_has_certificates_with_legacy_default_image' option is 'yes. These options are set when LifterLMS is updated
	 * to 6.0.0 and there are legacy user engagements ({@see \LLMS\Updates\Version_6_0_0\_add_legacy_opt()}).
	 *
	 * @since 6.0.0
	 *
	 * @return string The URL to the default image.
	 */
	protected function get_default_default_image_src() {

		$img        = "default-{$this->award_type}.png";
		$use_legacy = false;

		switch ( $this->award_type ) {
			case 'achievement':
				$use_legacy = llms_parse_bool( get_option( 'llms_has_achievements_with_legacy_default_image', 'no' ) );
				break;
			case 'certificate':
				$certificate = llms_get_certificate( null, true );
				if ( $certificate && 1 === $certificate->get_template_version() ) {
					$use_legacy = llms_parse_bool( get_option( 'llms_has_certificates_with_legacy_default_image', 'no' ) );
				}
				break;
		}

		/**
		 * Filter whether or not the legacy default images should be used for achievement and certificates.
		 *
		 * @since 6.0.0
		 *
		 * @example add_filter( 'llms_use_legacy_award_images', '__return_true' );
		 *
		 * @param boolean $use_legacy If `true`, the legacy image will be used.
		 * @param string  $award_type The type of award, either "achievement" or "certificate".
		 */
		if ( apply_filters( 'llms_use_legacy_award_images', $use_legacy, $this->award_type ) ) {
			$img = "optional_{$this->award_type}.png";
		}

		return llms()->plugin_url() . '/assets/images/' . $img;

	}

	/**
	 * Retrieve the default image for a given object.
	 *
	 * @since 6.0.0
	 *
	 * @param int $object_id WP_Post ID of the earned achievement. This is passed so that anyone filtering the default image could
	 *                       provide a different default image based on the achievement.
	 * @return string The full image source url.
	 */
	public function get_default_image( $object_id ) {

		$src = '';

		// Retrieve the stored value from the database.
		$id = $this->get_default_image_id();
		if ( $id ) {
			$src = wp_get_attachment_url( $id );
		}

		// Use the attachment stored for the option in the DB and fallback to the default image from the plugin's assets dir.
		$src = $src ? $src : $this->get_default_default_image_src();

		/**
		 * Filters the default image source for an award.
		 *
		 * The dynamic portion of this hook, {$this->award_type}, refers to the award type, either "achievement" or "certificate".
		 *
		 * @since 2.2.0
		 * @since 6.0.0 Merged achievement and certificate filters into a single dynamic filter.
		 *
		 * @param string $src       The full image source url.
		 * @param int    $object_id The WP_Post ID of the award.
		 */
		return apply_filters(
			"lifterlms_{$this->award_type}_image_placeholder_src",
			$src,
			$object_id
		);
	}

	/**
	 * Retrieve attachment ID of the default achievement image.
	 *
	 * If the attachment post doesn't exist will return false. This would happen
	 * if the post is deleted from the media library.
	 *
	 * @since 6.0.0
	 *
	 * @return int Returns the WP_Post ID of the attachment or `0` if not set.
	 */
	public function get_default_image_id() {
		$id = get_option( "lifterlms_{$this->award_type}_default_img", 0 );
		return $id && get_post( $id ) ? absint( $id ) : 0;
	}

}
