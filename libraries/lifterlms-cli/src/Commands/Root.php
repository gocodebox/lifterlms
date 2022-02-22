<?php
/**
 * LLMS_CLI_Command_Root file.
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.3
 */

namespace LifterLMS\CLI\Commands;

/**
 * Manage LifterLMS.
 *
 * ## BETA WARNING
 *
 * The LLMS-CLI is currently in early release as an open public beta. Commands
 * are subject to change without warning. Please pay close attention to the
 * changelog as we continue to develop and improve the CLI.
 *
 * If you encounter any issues or wish to provide feedback on the LLMS-CLI
 * please get in touch at https://github.com/gocodebox/lifterlms-cli.
 *
 * @since 0.0.1
 */
class Root extends AbstractCommand {

	/**
	 * Display the version of LifterLMS or the specified LifterLMS add-on.
	 *
	 * ## OPTIONS
	 *
	 * [<slug>]
	 * : The slug of the LifterLMS plugin or theme. Default: lifterlms.
	 *
	 * ## EXAMPLES
	 *
	 *     # Show the LifterLMS core plugin version
	 *     wp llms version
	 *
	 *     # Show the LifterLMS core plugin version
	 *     wp llms version core
	 *
	 *     # Show an add-on version without the "lifterlms-" prefix.
	 *     wp llms version groups
	 *
	 *     # Show an add-on version with the "lifterlms-" prefix.
	 *     wp llms version lifterlms-assignments
	 *
	 * @since 0.0.1
	 * @since 0.0.2 Remove `--db` option. This will be implemented in a separate command.
	 *
	 * @param array $args       Indexed array of positional command arguments.
	 * @param array $assoc_args Associative array of command options.
	 * @return null
	 */
	public function version( $args, $assoc_args ) {

		$slug = empty( $args[0] ) ? 'core' : $args[0];
		if ( in_array( $slug, array( 'core', 'lifterlms' ), true ) ) {
			return \WP_CLI::log( llms()->version );
		}

		$addon = $this->get_addon( $slug );
		if ( empty( $addon ) ) {
			return \WP_CLI::error( 'Invalid slug.' );
		}

		if ( $addon->is_installed() ) {
			return \WP_CLI::log( $addon->get_installed_version() );
		}

		return \WP_CLI::error(
			sprintf(
				"The requested add-on is not installed. Run 'wp llms addon install %s.' to install it.",
				$args[0]
			)
		);

	}

}
