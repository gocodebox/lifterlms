<?php
/**
 * Addon Install class file
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.1
 */

namespace LifterLMS\CLI\Commands\AddOn;

/**
 * AddOn Installation command
 *
 * @since 0.0.1
 */
trait Install {

	/**
	 * Install one of more add-ons.
	 *
	 * ## OPTIONS
	 *
	 * [<slug>...]
	 * : The slug of one or more add-on to install.
	 *
	 * [--key=<key>]
	 * : If set, will attempt to activate and use the provided license key.
	 *
	 * [--activate]
	 * : If set, the add-on(s) will be activated immediately after install.
	 *
	 * [--all]
	 * : If set, all of the add-ons available to the site will be installed.
	 * All existing license keys stored on the site will be queried for the list of available add-ons.
	 *
	 * [--type=<type>]
	 * : When using '--all', determines the type of add-on to be installed.
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
	public function install( $args, $assoc_args ) {

		// If a key is provided, activate it first.
		if ( ! empty( $assoc_args['key'] ) ) {
			\WP_CLI::runcommand( "llms license activate {$assoc_args['key']}" );
		}

		if ( ! empty( $assoc_args['all'] ) ) {
			$args = $this->get_available_addons( 'uninstalled', true, $assoc_args['type'] );
			if ( empty( $args ) ) {
				return \WP_CLI::warning( 'No add-ons to install.' );
			}
		}

		$results = $this->loop( $args, $assoc_args, 'install_one' );
		\WP_CLI\Utils\report_batch_operation_results( 'add-on', 'install', count( $args ), $results['successes'], $results['errors'] );

	}

	/**
	 * Loop callback function for install()
	 *
	 * Ensures add-on can be installed and actually installs (and maybe activates) the add-on.
	 *
	 * @since 0.0.1
	 *
	 * @param string      $slug       Add-on slug.
	 * @param LLMS_Add_On $addon      Add-on object.
	 * @param array       $assoc_args Associative array of command options.
	 * @return null|true Returns `null` if an error is encountered and `true` on success.
	 */
	private function install_one( $slug, $addon, $assoc_args ) {

		if ( $addon->is_installed() ) {
			return \WP_CLI::warning( sprintf( 'Add-on "%s" is already installed.', $slug ) );
		}

		\WP_CLI::log( sprintf( 'Installing add-on: %s...', $addon->get( 'title' ) ) );
		$res = $addon->install();
		if ( is_wp_error( $res ) ) {
			return \WP_CLI::warning( $res );
		}

		\WP_CLI::log( $res );
		if ( ! empty( $assoc_args['activate'] ) ) {
			$this->chain_command( 'activate', array( $slug ) );
		}

		return true;

	}

}
