<?php
/**
 * LLMS_Trait_User_Engagement_Type definition
 *
 * @package LifterLMS/Traits
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Methods and properties to help with user engagements.
 *
 * @since 6.0.0
 */
trait LLMS_Trait_User_Engagement_Type {

	/**
	 * The type of user engagement, e.g. 'achievement' or 'certificate'.
	 *
	 * @since 6.0.0
	 *
	 * @var string
	 */
	protected $engagement_type;

	/**
	 * Returns the number of user engagements that have been awarded from the template.
	 *
	 * @since 6.0.0
	 *
	 * @param int $template_id The post ID of the template.
	 * @return int
	 */
	protected function count_awarded_engagements( $template_id ) {

		$awarded_engagements_query = new LLMS_Awards_Query(
			array(
				'fields'    => 'ids',
				'templates' => $template_id,
				'per_page'  => 1,
				'status'    => array(
					'draft',
					'future',
					'publish',
				),
				'type'      => $this->engagement_type,
			)
		);

		return $awarded_engagements_query->get_found_results();
	}

	/**
	 * Returns an awarded engagement or an engagement template, based on a LLMS_Post_Model object, for the given post or false if not found.
	 *
	 * @since 6.0.0
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
