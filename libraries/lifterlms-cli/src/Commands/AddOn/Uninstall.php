<?php
/**
 * Addon Uninstall class file
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.1
 */

namespace LifterLMS\CLI\Commands\AddOn;

/**
 * AddOn Uninstall command
 *
 * @since 0.0.1
 */
trait Uninstall {

	/**
	 * Uninstall one of more add-ons.
	 *
	 * ## OPTIONS
	 *
	 * [<slug>...]
	 * : The slug of one or more add-on to install.
	 *
	 * [--deactivate]
	 * : If set, the plugin add-on(s) will be deactivated prior to uninstalling. Default behavior is to warn and skip if the plugin is active.
	 * Themes cannot be deactivated, another theme must be activated and then an add-on theme can be uninstalled.
	 *
	 * [--all]
	 * : If set, all of the add-ons available to the site will be uninstalled.
	 *
	 * [--type=<type>]
	 * : When using '--all', determines the type of add-on to be uninstalled.
	 * ---
	 * default: 'all'
	 * options:
	 *   - all
	 *   - plugin
	 *   - theme
	 * ---
	 *
	 * @since 0.0.1
	 *
	 * @param array $args       Indexed array of positional command arguments.
	 * @param array $assoc_args Associative array of command options.
	 * @return null
	 */
	public function uninstall( $args, $assoc_args ) {

		if ( ! empty( $assoc_args['all'] ) ) {
			$args = $this->get_available_addons( 'inactive', false, $assoc_args['type'] );
			if ( empty( $args ) ) {
				return \WP_CLI::warning( 'No add-ons to uninstall.' );
			}
		}

		$results = $this->loop( $args, $assoc_args, 'uninstall_one' );
		if ( ! $this->chaining ) {
			\WP_CLI\Utils\report_batch_operation_results( 'add-on', 'uninstall', count( $args ), $results['successes'], $results['errors'] );
		}

	}

	/**
	 * Loop callback function for uninstall()
	 *
	 * Ensures add-on can be uninstalled and actually installs (and maybe deactivates) the add-on.
	 *
	 * @since 0.0.1
	 *
	 * @param string      $slug       Add-on slug.
	 * @param LLMS_Add_On $addon      Add-on object.
	 * @param array       $assoc_args Associative array of command options.
	 * @return null|true Returns `null` if an error is encountered and `true` on success.
	 */
	private function uninstall_one( $slug, $addon, $assoc_args ) {

		if ( ! $addon->is_installed() ) {
			return \WP_CLI::warning( sprintf( 'Add-on "%s" is not installed.', $slug ) );
		}

		if ( $addon->is_active() ) {
			if ( ! empty( $assoc_args['deactivate'] ) ) {
				$this->chain_command( 'deactivate', array( $slug ) );
			} else {
				return \WP_CLI::warning( sprintf( 'Add-on "%s" is active.', $slug ) );
			}
		}

		$res = $addon->uninstall();
		if ( is_wp_error( $res ) ) {
			return \WP_CLI::warning( $res );
		}

		\WP_CLI::log( $res );

		return true;

	}

}
