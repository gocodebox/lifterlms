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
 * @param WP_Post|int|false $object_id  Lesson post object or id. If `false` uses the global `$post` object.
 * @return int
 */
function get_total_favorites( $object_id = false ) {

	global $wpdb;

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
