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
	 * Returns an awarded engagement or an engagement template, based on a LLMS_Post_Model object, for the given post or false if not found.
	 *
	 * @since [version]
	 *
	 * @param WP_Post|int|null $post       A WP_Post object or a WP_Post ID. A falsy value will use
	 *                                     the current global `$post` object (if one exists).
	 * @param int              $is_awarded If true, returns an awarded engagement, else an engagement template.
	 * @return LLMS_Abstract_User_Engagement|false
	 */
	protected function get_user_engagement( $post, $is_awarded ) {

		$is_awarded = $is_awarded ? 'my_' : null;
		$post       = get_post( $post );
		if ( ! $post || "llms_{$is_awarded}{$this->engagement_type}" !== $post->post_type ) {
			return false;
		}

		$class = 'LLMS_User_' . ucwords( $this->engagement_type, '_' );

		return new $class( $post );
	}
}
