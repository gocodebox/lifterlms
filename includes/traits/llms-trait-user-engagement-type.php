<?php
/**
 * LLMS_Trait_User_Engagement_Type definition
 *
 * @package LifterLMS/Traits
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Methods and properties to help with user engagements.
 *
 * @since [version]
 */
trait LLMS_Trait_User_Engagement_Type {

	/**
	 * The type of user engagement, e.g. 'achievement' or 'certificate'.
	 *
	 * @since [version]
	 *
	 * @var string
	 */
	protected $engagement_type;

	/**
	 * Returns an awarded child LLMS_Abstract_User_Engagement instance for the given post or false if not found.
	 *
	 * @since [version]
	 *
	 * @param WP_Post|int|null $post A WP_Post object or a WP_Post ID. A falsy value will use
	 *                               the current global `$post` object (if one exists).
	 * @return LLMS_Abstract_User_Engagement|false
	 */
	protected function get_awarded_engagement( $post ) {

		$post = get_post( $post );
		if ( ! $post || "llms_my_$this->engagement_type" !== $post->post_type ) {
			return false;
		}

		$class = 'LLMS_User_' . ucwords( $this->engagement_type, '_' );

		return new $class( $post );
	}
}
