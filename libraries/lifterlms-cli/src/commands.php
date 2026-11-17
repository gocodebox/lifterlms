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

/**
 * Course Sub-Resource Commands
 *
 * Adds commands for course sub-resource endpoints (content, enrollments)
 * that are not auto-discovered by the Restful bridge.
 *
 * @since 0.0.6
 */
WP_CLI::add_command( 'llms course', 'LifterLMS\CLI\Commands\Course\Main' );
