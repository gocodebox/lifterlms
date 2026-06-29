<?php
/**
 * Gocodebox Notifications Plugin
 *
 * @package Gocodebox_Blocks/Main
 *
 * @wordpress-plugin
 * Plugin Name: Banner Notifications
 * Plugin URI: https://github.com/gocodebox/banner-notifications
 * Description: Admin banner notifications library.
 * Version: 1.0.1
 * Author: Gocodebox
 * Author URI: https://lifterlms.com/
 * Text Domain: gocodebox-banner-notifications
 * Domain Path: /i18n
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.5
 * Tested up to: 6.8
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
