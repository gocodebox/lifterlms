<?php
/**
 * LifterLMS CLI Plugin
 *
 * @package LifterLMS/CLI/Main
 *
 * @since 0.0.1
 * @version 0.0.1
 *
 * WP CLI commands for the LifterLMS Core.
 * Bundled as a library within LifterLMS core; not installable as a standalone plugin.
 */

use LifterLMS\CLI\Main;

defined( 'ABSPATH' ) || exit;

// Don't load the CLI.
if ( defined( 'LLMS_CLI_DISABLE' ) && LLMS_CLI_DISABLE ) {
	return;
}

// Only load in CLI context.
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

// Define Constants.
if ( ! defined( 'LLMS_CLI_PLUGIN_FILE' ) ) {
	define( 'LLMS_CLI_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'LLMS_CLI_PLUGIN_DIR' ) ) {
	define( 'LLMS_CLI_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
}

// Autoload.
require_once LLMS_CLI_PLUGIN_DIR . 'vendor/autoload.php';

/**
 * Main Plugin Instance
 *
 * @since 0.0.1
 *
 * @return LLMS_CLI
 */
function llms_cli() {
	return Main::instance();
}

return llms_cli();
