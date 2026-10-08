<?php
/**
 * LifterLMS REST API Plugin
 *
 * @package  LifterLMS_REST_API/Main
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.26
 *
 * REST API for the LifterLMS Core.
 * Bundled as a library within LifterLMS core; not installable as a standalone plugin.
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
	define( 'LLMS_REST_API_PLUGIN_DIR', __DIR__ . '/' );
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
	 * @return LifterLMS_REST_API
	 */
	function LLMS_REST_API() {
		return LifterLMS_REST_API::instance();
	}
}

return LLMS_REST_API();
// phpcs:enable
