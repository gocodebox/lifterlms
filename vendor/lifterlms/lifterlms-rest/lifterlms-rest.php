<?php
/**
 * LifterLMS REST API Plugin
 *
 * @package  LifterLMS_REST_API/Main
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 *
 * Plugin Name: LifterLMS REST API
 * Plugin URI: https://lifterlms.com/
 * Description: REST API feature plugin for the LifterLMS Core.
 * Version: 1.0.0-beta.6
 * Author: LifterLMS
 * Author URI: https://lifterlms.com/
 * Text Domain: lifterlms
 * Domain Path: /i18n
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * LifterLMS Minimum Version: 3.32.0
 */

defined( 'ABSPATH' ) || exit;

// Don't load the REST API.
if ( defined( 'LLMS_REST_DISABLE' ) && LLMS_REST_DISABLE ) {
	return;
}

// @todo handle this better.
if ( version_compare( phpversion(), '7.1', '<' ) ) {
	return;
}

// Define Constants.
if ( ! defined( 'LLMS_REST_API_PLUGIN_FILE' ) ) {
	define( 'LLMS_REST_API_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'LLMS_REST_API_PLUGIN_DIR' ) ) {
	define( 'LLMS_REST_API_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
}

if ( ! defined( 'LLMS_REST_API_PLUGIN_URL' ) ) {
	define( 'LLMS_REST_API_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

if ( ! defined( 'LLMS_REST_WEBHOOK_DELIVERY_LOGGING' ) ) {
	define( 'LLMS_REST_WEBHOOK_DELIVERY_LOGGING', true );
}

// Load Plugin.
if ( ! class_exists( 'LifterLMS_REST_API' ) ) {

	require_once LLMS_REST_API_PLUGIN_DIR . 'class-lifterlms-rest-api.php';

	// phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	/**
	 * Main Plugin Instance
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return LLMS_REST_API
	 */
	function LLMS_REST_API() {
		return LifterLMS_REST_API::instance();
	}
}

return LLMS_REST_API();
// phpcs:enable
