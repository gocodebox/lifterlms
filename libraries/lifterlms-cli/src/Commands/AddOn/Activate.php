<?php
/**
 * Addon Activate class file
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.2
 */

namespace LifterLMS\CLI\Commands\AddOn;

/**
 * AddOn Activation command
 *
 * @since 0.0.1
 */
trait Activate {

	/**
	 * Activate one or more add-ons.
	 *
	 * ## OPTIONS
	 *
	 * [<slug>...]
	 * : The slug of one or more LifterLMS add-on to install.
	 *
	 * [--all]
	 * : If set, all of the LifterLMS add-ons installed on the site will be activated.
	 *
	 * ## EXAMPLES
	 *
	 *     # Activate the LifterLMS Groups add-on.
	 *     $ wp llms addon activate lifterlms-groups
	 *
	 *     # Activate an add-on without using the `lifterlms-` prefix.
	 *     $ wp llms addon activate advanced-videos
	 *
	 *     # Activate multiple LifterLMS add-ons.
	 *     $ wp llms addon activate lifterlms-groups lifterlms-assignments lifterlms-pdfs
	 *
	 *     # Activate all installed LifterLMS add-ons.
	 *     $ wp llms addon activate --all
	 *
	 * @since 0.0.1
	 *
	 * @param array $args       Indexed array of positional command arguments.
	 * @param array $assoc_args Associative array of command options.
	 * @return null
	 */
	public function activate( $args, $assoc_args ) {

		if ( ! empty( $assoc_args['all'] ) ) {
			$args = $this->get_available_addons( 'inactive', false );
			if ( empty( $args ) ) {
				return \WP_CLI::warning( 'No add-ons to activate.' );
			}
		}

		$results = $this->loop( $args, $assoc_args, 'activate_one' );
		if ( ! $this->chaining ) {
			\WP_CLI\Utils\report_batch_operation_results( 'add-on', 'activate', count( $args ), $results['successes'], $results['errors'] );
		}

	}

	/**
	 * Loop callback function for activate()
	 *
	 * Ensures add-on can be activated and actually activates the add-on.
	 *
	 * @since 0.0.1
	 * @since 0.0.2 Fixed unmerged placeholder in warning message when add-on is not installed.
	 *
	 * @param string      $slug       Add-on slug.
	 * @param LLMS_Add_On $addon      Add-on object.
	 * @param array       $assoc_args Associative array of command options.
	 * @return null|true Returns `null` if an error is encountered and `true` on success.
	 */
	private function activate_one( $slug, $addon, $assoc_args ) {

		if ( $addon->is_active() ) {
			return \WP_CLI::warning( sprintf( 'Add-on "%s" is already active.', $slug ) );
		}

		if ( ! $addon->is_installed() ) {
			return \WP_CLI::warning( sprintf( 'Add-on "%1$s" is not installed. Run \'wp llms addon install %s\' to install it.', $slug ) );
		}

		$res = $addon->activate();
		if ( is_wp_error( $res ) ) {
			return \WP_CLI::warning( $res );
		}

		\WP_CLI::log( $res );

		return true;

	}

}
