<?php
/**
 * Update functions for version 4.0.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 4.0.0
 * @version 4.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove session data stored on the options table by removed the WP Session Manager library
 *
 * @since 4.0.0
 *
 * @return void
 */
function llms_update_400_remove_session_options() {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_wp_session_%';" ); // db call ok; no cache ok.
}

/**
 * Clear cron hook used by the WP Session Manager library to cleanup expired sessions
 *
 * @since 4.0.0
 *
 * @return void
 */
function llms_update_400_clear_session_cron() {
	wp_clear_scheduled_hook( 'wp_session_garbage_collection' );
}

/**
 * Update db version to 4.0.0
 *
 * @since 4.0.0
 *
 * @return void
 */
function llms_update_400_update_db_version() {
	LLMS_Install::update_db_version( '4.0.0' );
}
