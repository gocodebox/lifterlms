<?php
/**
 * LifterLMS REST API Uninstall
 *
 * @package LifterLMS_REST/Uninstall
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

// If uninstall not called from WordPress exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

/**
 * Only actually delete LifterLMS and Related Data when constant is defined
 * This will prevent data loss when a plugin is deactivated
 */
if ( ! defined( 'LLMS_REMOVE_ALL_DATA' ) || true !== LLMS_REMOVE_ALL_DATA ) {
	exit();
}

global $wpdb;

// Delete options.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'lifterlms\_rest\_%';" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'llms\_rest\_%';" );

// drop tables.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}lifterms_api_keys" );
