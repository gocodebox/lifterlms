<?php
/**
 * AddOn Deactivate class file
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.2
 */

namespace LifterLMS\CLI\Commands\AddOn;

/**
 * AddOn Activation and deactivation commands
 *
 * @since 0.0.1
 */
trait Deactivate {

	/**
	 * Deactivate one or more plugin add-ons.
	 *
	 * ## OPTIONS
	 *
	 * [<slug>...]
	 * : The slug of one or more add-on to deactivate.
	 *
	 * [--uninstall]
	 * : Uninstall the add-ons after deactivation.
	 *
	 * [--all]
	 * : If set, all of the plugin add-ons installed on the site will be activated.
	 *
	 * ## EXAMPLES
	 *
	 *     # Deactivate the LifterLMS Groups add-on.
	 *     $ wp llms addon deactivate lifterlms-groups
	 *
	 *     # Deactivate an add-on without using the `lifterlms-` prefix.
	 *     $ wp llms addon deactivate advanced-videos
	 *
	 *     # Deactivate multiple LifterLMS add-ons.
	 *     $ wp llms addon deactivate lifterlms-groups lifterlms-assignments lifterlms-pdfs
	 *
	 *     # Deactivate all installed LifterLMS add-ons.
	 *     $ wp llms addon deactivate --all
	 *
	 *     # Deactivate and uninstall the LifterLMS Groups add-on.
	 *     $ wp llms addon deactivate lifterlms-groups --uninstall
	 *
	 * @since 0.0.1
	 * @since 0.0.2 Completion messages use says "deactivate(d)" in favor of "activate(d)".
	 *
	 * @param array $args       Indexed array of positional command arguments.
	 * @param array $assoc_args Associative array of command options.
	 * @return null
	 */
	public function deactivate( $args, $assoc_args ) {

		if ( ! empty( $assoc_args['all'] ) ) {
			$args = $this->get_available_addons( 'active', false, 'plugin' );
			if ( empty( $args ) ) {
				return \WP_CLI::warning( 'No add-ons to deactivate.' );
			}
		}

		$results = $this->loop( $args, $assoc_args, 'deactivate_one' );
		if ( ! $this->chaining ) {
			\WP_CLI\Utils\report_batch_operation_results( 'add-on', 'deactivate', count( $args ), $results['successes'], $results['errors'] );
		}

	}

	/**
	 * Loop callback function for deactivate()
	 *
	 * Ensures add-on can be deactivated and actually deactivates (and maybe uninstalls) the add-on.
	 *
	 * @since 0.0.1
	 *
	 * @param string      $slug       Add-on slug.
	 * @param LLMS_Add_On $addon      Add-on object.
	 * @param array       $assoc_args Associative array of command options.
	 * @return null|true Returns `null` if an error is encountered and `true` on success.
	 */
	private function deactivate_one( $slug, $addon, $assoc_args ) {

		if ( ! $addon->is_installed() ) {
			return \WP_CLI::warning( sprintf( 'Add-on "%1$s" is not installed.', $slug ) );
		}

		if ( ! $addon->is_active() ) {
			return \WP_CLI::warning( sprintf( 'Add-on "%s" is already deactivated.', $slug ) );
		}

		$res = $addon->deactivate();
		if ( is_wp_error( $res ) ) {
			return \WP_CLI::warning( $res );
		}

		if ( ! empty( $assoc_args['uninstall'] ) ) {
			$this->chain_command( 'uninstall', array( $slug ) );
		}

		\WP_CLI::log( $res );

		return true;

	}

}
