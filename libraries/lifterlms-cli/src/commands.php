<?php
/**
 * Load LifterLMS CLI classes
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.1
 */

namespace LifterLMS\CLI;

use WP_CLI;
use LifterLMS\CLI\Commands\Restful\Runner;

/**
 * Root Command
 *
 * @since 0.0.1
 */
WP_CLI::add_command( 'llms', 'LifterLMS\CLI\Commands\Root' );

/**
 * Add-on Command
 *
 * @since 0.0.1
 */
WP_CLI::add_command( 'llms addon', 'LifterLMS\CLI\Commands\AddOn\Main' );

/**
 * License Command
 *
 * @since 0.0.1
 */
WP_CLI::add_command( 'llms license', 'LifterLMS\CLI\Commands\License' );

/**
 * Restful Commands
 *
 * @since 0.0.1
 */
Runner::after_wp_load();
