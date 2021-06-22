<?php
/**
 * LifterLMS Update Functions
 *
 * Functions here are used by the background updater during db updates.
 *
 * @package LifterLMS/Functions
 *
 * @since 3.4.3
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

require_once 'updates/llms-functions-updates-300.php';
require_once 'updates/llms-functions-updates-303.php';
require_once 'updates/llms-functions-updates-343.php';
require_once 'updates/llms-functions-updates-3120.php';
require_once 'updates/llms-functions-updates-360.php';
require_once 'updates/llms-functions-updates-380.php';
require_once 'updates/llms-functions-updates-3130.php';
require_once 'updates/llms-functions-updates-3160.php';
require_once 'updates/llms-functions-updates-3280.php';
require_once 'updates/llms-functions-updates-400.php';
require_once 'updates/llms-functions-updates-450.php';
require_once 'updates/llms-functions-updates-4150.php';
require_once 'updates/llms-functions-updates-500.php';

/**
 * Duplicate a WP Post & all relate metadata
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
