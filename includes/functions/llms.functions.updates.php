<?php
/**
 * LifterLMS Update Functions
 *
 * Functions here are used by the background updater during db updates.
 *
 * @package LifterLMS/Functions
 *
 * @since 3.4.3
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

// Include all update function files.
foreach ( glob( LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-*.php' ) as $filename ) {
	require_once $filename;
}

/**
 * Get the number of items per page used in paginated migration queries.
 *
 * @since 6.0.0
 *
 * @return int
 */
function llms_update_util_get_items_per_page() {
	/**
	 * Filters the number of items per page in migration queries.
	 *
	 * This filter exists primarily to allow phpunit tests for migration
	 * functions and queries to reduce the number of items per query. In this
	 * way pagination functionality can be tested without having to tests
	 * a large number of items.
	 *
	 * @since 6.0.0
	 *
	 * @param int $per_page Number of items per page.
	 */
	return apply_filters( 'llms_update_items_per_page', 50 );
}

/**
 * Duplicate a WP Post & all relate metadata.
 *
 * @since 3.16.0
 *
 * @param int $id WP Post ID.
 * @return int WP Post ID of the new duplicate.
 */
function llms_update_util_post_duplicator( $id ) {

	$copy = (array) get_post( $id );
	unset( $copy['ID'] );
	$new_id = wp_insert_post( $copy );
	foreach ( get_post_custom( $id ) as $key => $values ) {
		foreach ( $values as $value ) {
			add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
		}
	}

	return $new_id;

}

/**
 * Update the key of a postmeta item
 *
 * @since 3.4.3
 *
 * @param string $post_type Post type.
 * @param string $new_key   New postmeta key.
 * @param string $old_key   Old postmeta key.
 * @return void
 */
function llms_update_util_rekey_meta( $post_type, $new_key, $old_key ) {

	global $wpdb;

	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->prefix}postmeta AS m
		 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
		 SET m.meta_key = %s
	 	 WHERE p.post_type = %s AND m.meta_key = %s;",
			array( $new_key, $post_type, $old_key )
		)
	); // no-cache ok.

}
