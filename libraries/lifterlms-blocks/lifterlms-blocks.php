<?php
/**
 * LifterLMS Blocks Plugin
 *
 * @package LifterLMS_Blocks/Main
 *
 * @since 1.0.0
 * @version 2.0.0
 *
 * WordPress Editor (Gutenberg) blocks for LifterLMS.
 * Bundled as a library within LifterLMS core; not installable as a standalone plugin.
 */

// Restrict Direct Access.
defined( 'ABSPATH' ) || exit;

// Define Constants.
if ( ! defined( 'LLMS_BLOCKS_VERSION' ) ) {
	define( 'LLMS_BLOCKS_VERSION', '2.8.0' );
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
