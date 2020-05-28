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

function llms_update_3390_remove_session_options() {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_wp_session_%" );
}

function llms_update_3390_clear_session_cron() {
	wp_clear_scheduled_hook( 'wp_session_garbage_collection' );
}

function llms_update_3390_update_db_version() {
	LLMS_Install::update_db_version( '3.39.0' );
}
