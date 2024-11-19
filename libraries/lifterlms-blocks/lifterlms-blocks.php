<?php
/**
 * LifterLMS Blocks Plugin
 *
 * @package LifterLMS_Blocks/Main
 *
 * @since 1.0.0
 * @version 2.0.0
 *
 * @wordpress-plugin
 * Plugin Name: LifterLMS Blocks
 * Plugin URI: https://github.com/gocodebox/lifterlms-blocks
 * Description: WordPress Editor (Gutenberg) blocks for LifterLMS.
 * Version: 2.5.8
 * Author: LifterLMS
 * Author URI: https://lifterlms.com/
 * Text Domain: lifterlms
 * Domain Path: /i18n
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 5.5
 * Tested up to: 6.4
 */

// Restrict Direct Access.
defined( 'ABSPATH' ) || exit;

// Define Constants.
if ( ! defined( 'LLMS_BLOCKS_VERSION' ) ) {
	define( 'LLMS_BLOCKS_VERSION', '2.5.8' );
}

/**
 * Allows disabling the blocks plugin & functionality.
 *
 * @since 1.0.0
 *
 * @param boolean $load Whether the plugin should be loaded. Defaults to `true`.
 */
if ( ! apply_filters( 'llms_load_blocks_plugin', true ) ) {
	return;
}


// Load only when the block editor is present.
if ( function_exists( 'has_blocks' ) ) {

	if ( ! defined( 'LLMS_BLOCKS_PLUGIN_FILE' ) ) {
		define( 'LLMS_BLOCKS_PLUGIN_FILE', __FILE__ );
	}

	if ( ! defined( 'LLMS_BLOCKS_PLUGIN_DIR' ) ) {
		define( 'LLMS_BLOCKS_PLUGIN_DIR', dirname( LLMS_BLOCKS_PLUGIN_FILE ) );
	}

	if ( ! defined( 'LLMS_BLOCKS_PLUGIN_DIR_URL' ) ) {
		define( 'LLMS_BLOCKS_PLUGIN_DIR_URL', plugin_dir_url( LLMS_BLOCKS_PLUGIN_FILE ) );
	}

	// Start.
	require_once LLMS_BLOCKS_PLUGIN_DIR . '/includes/class-llms-blocks.php';

}
