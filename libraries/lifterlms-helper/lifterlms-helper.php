<?php
/**
 * LifterLMS Helper main plugin file
 *
 * @package LifterLMS_Helper/Main
 *
 * @since 1.0.0
 * @version 3.3.0
 *
 * Update, install, and beta test LifterLMS and LifterLMS add-ons.
 * Bundled as a library within LifterLMS core; not installable as a standalone plugin.
 */

defined( 'ABSPATH' ) || exit;

// Allow the helper to be disabled via constant when loaded as a library within the LifterLMS core.
if ( defined( 'LLMS_HELPER_LIB' ) && defined( 'LLMS_HELPER_DISABLE' ) && LLMS_HELPER_DISABLE ) {
	return;
}

if ( ! defined( 'LLMS_HELPER_PLUGIN_FILE' ) ) {
	define( 'LLMS_HELPER_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'LLMS_HELPER_PLUGIN_DIR' ) ) {
	define( 'LLMS_HELPER_PLUGIN_DIR', __DIR__ . '/' );
}

if ( ! defined( 'LLMS_HELPER_PLUGIN_URL' ) ) {
	define( 'LLMS_HELPER_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
}

if ( ! class_exists( 'LifterLMS_Helper' ) ) {

	require_once LLMS_HELPER_PLUGIN_DIR . 'class-lifterlms-helper.php';

	/**
	 * Returns the main instance of the LifterLMS_Helper class
	 *
	 * @since 3.2.0
	 * :
	 * @return LifterLMS_Helper
	 */
	function llms_helper() {
		return LifterLMS_Helper::instance();
	}
}

return llms_helper();
