<?php
/**
 * Update functions for version 3.39.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 3.39.0
 * @version 3.39.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove session data stored on the options table by removed the WP Session Manager library
 *
 * @since [version]
 *
 * @return void
 */
function llms_update_3390_remove_session_options() {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_wp_session_%';" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching.
}

/**
 * Clear cron hook used by the WP Session Manager library to cleanup expired sessions
 *
 * @since [version]
 *
 * @return void
 */
function llms_update_3390_clear_session_cron() {
	wp_clear_scheduled_hook( 'wp_session_garbage_collection' );
}

/**
 * Update db version to 3.39.0
 *
 * @since [version]
 *
 * @return void
 */
function llms_update_3390_update_db_version() {
	LLMS_Install::update_db_version( '3.39.0' );
}
