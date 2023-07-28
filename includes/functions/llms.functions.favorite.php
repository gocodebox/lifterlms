<?php
/**
 * LifterLMS Favorite Functions
 *
 * @package LifterLMS/Functions
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Favorites Count.
 *
 * @since [version]
 *
 * @param int $object_id WP Post ID of the Lesson.
 * @return int
 */
function llms_get_object_total_favorites( $object_id = false ) {

	global $wpdb;

	// Getting ID from Global Post object.
	if ( ! $object_id ) {
		$object_id = get_the_ID();
	}

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$res = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta
				WHERE post_id = %d AND meta_key = %s ORDER BY updated_date DESC",
			$object_id,
			'_favorite'
		)
	);
	// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	return count( $res );

}

/**
 * Filter Hook to enable the Favorite feature.
 *
 * @since [version]
 *
 * @return bool True if favorites are enabled, false otherwise.
 */
function llms_is_favorites_enabled() {

	$favorite_enabled = llms_parse_bool( get_option( 'lifterlms_favorites', 'no' ) );

	/**
	 * Filter to enable/disable the Favorite feature.
	 *
	 * @since [version]
	 *
	 * @param bool $favorite_enabled True if favorites are enabled, false otherwise.
	 */
	return apply_filters( 'llms_favorites_enabled', $favorite_enabled );
}
