<?php
/**
 * Main LifterLMS plugin file
 *
 * @package LifterLMS/Main
 *
 * @since 1.0.0
 * @version 4.0.0
 *
 * Plugin Name: LifterLMS
 * Plugin URI: https://lifterlms.com/
 * Description: LifterLMS is a powerful WordPress learning management system plugin that makes it easy to create, sell, and protect engaging online courses and training based membership websites.
 * Version: 5.1.2
 * Author: LifterLMS
 * Author URI: https://lifterlms.com/
 * Text Domain: lifterlms
 * Domain Path: /languages
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.4
 * Tested up to: 5.8
 * Requires PHP: 7.3
 *
 * * * * * * * * * * * * * * * * * * * * * *
 *                                         *
 * Reporting a Security Vulnerability      *
 *                                         *
 * Please disclose any security issues or  *
 * vulnerabilities to team@lifterlms.com   *
 *                                         *
 * See our full Security Policy at         *
 * https://lifterlms.com/security-policy   *
 *                                         *
 * * * * * * * * * * * * * * * * * * * * * *
 */

defined( 'ABSPATH' ) || exit;

// Autoloader.
require_once 'vendor/autoload.php';

if ( ! defined( 'LLMS_PLUGIN_FILE' ) ) {
	define( 'LLMS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'LLMS_PLUGIN_DIR' ) ) {
	define( 'LLMS_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
}

if ( ! class_exists( 'LifterLMS' ) ) {
	require_once LLMS_PLUGIN_DIR . 'class-lifterlms.php';
}

/**
 * Allow usage of the deprecated `LLMS()` function.
 *
 * @deprecated 4.0.0 Function `LLMS()` is deprecated in favor of `llms()`.
 */
use function LLMS as llms;

/**
 * Returns the main instance of LifterLMS
 *
 * @since 4.0.0
 *
 * @return LifterLMS
 */
function llms() {
	return LifterLMS::instance();
}
return llms();
