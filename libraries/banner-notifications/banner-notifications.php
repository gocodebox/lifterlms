<?php
/**
 * Gocodebox Notifications Plugin
 *
 * @package Gocodebox_Blocks/Main
 *
 * Admin banner notifications library.
 * Bundled as a library within LifterLMS core; not installable as a standalone plugin.
 */

// Restrict Direct Access.
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'GOCODEBOX_BANNER_NOTIFICATIONS_PLUGIN_FILE' ) ) {
	define( 'GOCODEBOX_BANNER_NOTIFICATIONS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'GOCODEBOX_BANNER_NOTIFICATIONS_PLUGIN_DIR' ) ) {
	define( 'GOCODEBOX_BANNER_NOTIFICATIONS_PLUGIN_DIR', dirname( GOCODEBOX_BANNER_NOTIFICATIONS_PLUGIN_FILE ) );
}

if ( ! defined( 'GOCODEBOX_BANNER_NOTIFICATIONS_PLUGIN_DIR_URL' ) ) {
	define( 'GOCODEBOX_BANNER_NOTIFICATIONS_PLUGIN_DIR_URL', plugin_dir_url( GOCODEBOX_BANNER_NOTIFICATIONS_PLUGIN_FILE ) );
}

// Start.
require_once GOCODEBOX_BANNER_NOTIFICATIONS_PLUGIN_DIR . '/src/notifications.php';
