<?php
/**
 * Base engagement class
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Abstract_Engagement
 *
 * @since [version]
 */
abstract class LLMS_Abstract_Engagement {

	/**
	 * Return the engagement type
	 *
	 * The core engagements types are "achievement" and "certificate".
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	abstract protected function get_type();

	/**
	 * Initialize the engagement
	 *
	 * This function is called by the private constructor and should include any
	 * additional files and setup class variables.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	abstract public function init();

	/**
	 * Retrieves an instance of an engagement subclass identified by $type
	 *
	 * @since [version]
	 *
	 * @param string $type Engagement subclass type. This is an optional forward compatible variable as LifterLMS only supports "user" achievements.
	 * @return object
	 */
	abstract protected function get_sub_class( $type = '' );

	/**
	 * Private constructor
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->init();
	}

	/**
	 * Retrieve the path to the default image used for achievements when no image is stored.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_default_image() {

		$type = $this->get_type();

		$src = LLMS_PLUGIN_URL . "assets/images/optional_{$type}.png";

		// Deprecated filter which was previously utilized by both achievements and certificates.
		$src = apply_filters_deprecated( 'lifterlms_placeholder_img_src', array( $src ), '[version]', "llms_default_{$type}_img_src" );

		/**
		 * Filter the default engagement image used for when no image is explicitly stored.
		 *
		 * The dynamic portion of this filter `{$type}` refers to the engagement type,
		 * typically either "achievement" or "certificate".
		 *
		 * @since [version]
		 *
		 * @param string $src Path to the default image.
		 */
		return apply_filters( "llms_default_{$type}_img_src", $src );

	}


	/**
	 * Trigger generation of the engagement for a user based on a template
	 *
	 * @since [version]
	 *
	 * @param int $person_id       WP_User ID.
	 * @param int $template_id     WP_Post ID of the engagement template.
	 * @param int $related_post_id WP_Post ID of the related post, for example a lesson id.
	 * @return void
	 */
	public function trigger_engagement( $person_id, $template_id, $related_post_id ) {
		$this->get_sub_class()->trigger( $person_id, $template_id, $related_post_id );
	}

}
