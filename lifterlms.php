<?php
/**
 * Main LifterLMS plugin file
 *
 * @package LifterLMS/Main
 *
 * @since 1.0.0
 * @version 5.3.0
 *
 * Plugin Name: LifterLMS
 * Plugin URI: https://lifterlms.com/
 * Description: Complete e-learning platform to sell online courses, protect lessons, offer memberships, and quiz students.
 * Version: 7.1.1
 * Author: LifterLMS
 * Author URI: https://lifterlms.com/
 * Text Domain: lifterlms
 * Domain Path: /languages
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.6
 * Tested up to: 6.1
 * Requires PHP: 7.4
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

if ( ! defined( 'LLMS_PLUGIN_FILE' ) ) {
	define( 'LLMS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'LLMS_PLUGIN_DIR' ) ) {
	define( 'LLMS_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
}

// Autoloader.
require_once LLMS_PLUGIN_DIR . 'vendor/autoload.php';
require_once LLMS_PLUGIN_DIR . 'includes/class-llms-loader.php';

if ( ! class_exists( 'LifterLMS' ) ) {
	require_once LLMS_PLUGIN_DIR . 'class-lifterlms.php';
}

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
