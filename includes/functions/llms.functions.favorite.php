<?php
/**
 * LifterLMS Favorite Functions
 *
 * @package LifterLMS/Functions
 *
 * @since 7.5.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Favorites Count.
 *
 * @since 7.5.0
 *
 * @param bool|int $object_id WP Post ID of the Lesson. If not supplied it will default to the current post ID.
 * @return int
 */
function llms_get_object_total_favorites( $object_id = false ) {

	global $wpdb;

	// Getting ID from Global Post object.
	if ( ! $object_id ) {
		$object_id = get_the_ID();
	}

	$res = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(DISTINCT meta_id) FROM {$wpdb->prefix}lifterlms_user_postmeta
				WHERE post_id = %d AND meta_key = %s ORDER BY updated_date DESC",
			$object_id,
			'_favorite'
		)
	); // db call ok; no-cache ok.

	return $res;

}

/**
 * Filter Hook to enable the Favorite feature.
 *
 * @since 7.5.0
 *
 * @return bool True if favorites are enabled, false otherwise.
 */
function llms_is_favorites_enabled() {

	$favorite_enabled = llms_parse_bool( get_option( 'lifterlms_favorites', 'no' ) );

	/**
	 * Filter to enable/disable the Favorite feature.
	 *
	 * @since 7.5.0
	 *
	 * @param bool $favorite_enabled True if favorites are enabled, false otherwise.
	 */
	return apply_filters( 'llms_favorites_enabled', $favorite_enabled );
}
