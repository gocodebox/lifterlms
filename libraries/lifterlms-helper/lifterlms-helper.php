<?php
/**
 * LifterLMS Helper main plugin file
 *
 * @package LifterLMS_Helper/Main
 *
 * @since 1.0.0
 * @version 3.3.0
 *
 * Plugin Name: LifterLMS Helper
 * Plugin URI: https://lifterlms.com/
 * Description: Update, install, and beta test LifterLMS and LifterLMS add-ons
 * Version: 3.5.4
 * Author: LifterLMS
 * Author URI: https://lifterlms.com
 * Text Domain: lifterlms
 * Domain Path: /i18n
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires LifterLMS: 3.22.0
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
