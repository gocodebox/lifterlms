<?php
/**
 * Addon Update class file
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.1
 */

namespace LifterLMS\CLI\Commands\AddOn;

use WP_CLI\Formatter;

/**
 * AddOn Update command
 *
 * @since 0.0.1
 */
trait Update {

	/**
	 * Update one of more add-ons.
	 *
	 * ## OPTIONS
	 *
	 * [<slug>...]
	 * : The slug of one or more add-on to update.
	 *
	 * [--exclude]
	 * : A comma-separated list of add-on slugs which should be excluded from updating.
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
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * [--dry-run]
	 * : Preview which plugins would be updated.
	 *
	 * @since 0.0.1
	 *
	 * @param array $include    List of add-on slugs to be updated.
	 * @param array $assoc_args Associative array of command options.
	 * @return null
	 */
	public function update( $include, $assoc_args ) {

		$include = array_map( array( $this, 'prefix_slug' ), $include );

		$fields = array( 'name', 'status', 'version', 'update_version' );

		$exclude = ! empty( $assoc_args['exclude'] ) ? array_map( array( $this, 'prefix_slug' ), explode( ',', $assoc_args['exclude'] ) ) : array();

		// Retrieve all available updates and we'll filter it down.
		$list = \WP_CLI::runcommand(
			"llms addon list --format=json {$fieldopt}--update=available --fields=name,status,version,update_version",
			array(
				'return' => true,
			)
		);
		$list = array_filter(
			json_decode( $list, true ),
			function( $item ) use ( $include, $exclude ) {
				// Add-on is active and an update is available.
				return // Add-on is installed.
					in_array( $item['status'], array( 'active', 'inactive' ), true ) &&
					// Not excluded.
					! in_array( $item['name'], $exclude, true ) &&
					// No add-ons specified or the add-on is in the specified list.
					( empty( $include ) || in_array( $item['name'], $include, true ) );
			}
		);

		// WP-CLI `wp plugin update` shows a string when displaying table and no output for other formats.
		if ( empty( $list ) ) {
			if ( 'table' === $assoc_args['format'] ) {
				return \WP_CLI::log( 'No add-on updates available.' );
			}
			return;
		}

		/**
		 * The WP Core upgrader pulls information from the site transient.
		 * If the update check cron or a manual visit to an update screen on the admin panel
		 * hasn't recently occurred the transient won't be set and we'll know there's an update
		 * but the transient will not and the upgrader won't be able to upgrade.
		 *
		 * So we'll force a redundant check to take place here to ensure that we can upgrade.
		 */
		wp_update_plugins();
		wp_update_themes();

		if ( empty( $assoc_args['dry-run'] ) ) {

			$fields = array( 'name', 'status', 'old_version', 'new_version' );

			$errors    = 0;
			$successes = 0;
			foreach ( $list as &$item ) {

				if ( $this->update_one( $item ) ) {
					$successes++;
				} else {
					$errors++;
				}
			}

			\WP_CLI\Utils\report_batch_operation_results( 'add-on', 'update', count( $list ), $successes, $errors );

		}

		$formatter = new Formatter( $assoc_args, $fields );
		return $formatter->display_items( $list );

	}


	/**
	 * Update a single add-on
	 *
	 * @since 0.0.1
	 *
	 * @param array $item Associative array of add-on data.
	 * @return boolean Returns `false` when an error is encountered and `true` otherwise.
	 */
	private function update_one( &$item ) {

		$addon = $this->get_addon( $item['name'] );

		\WP_CLI::log( sprintf( 'Updating add-on: %s...', $addon->get( 'title' ) ) );
		$res = $addon->update();
		if ( is_wp_error( $res ) ) {
			\WP_CLI::warning( $res );
			return false;
		}

		$item['old_version'] = $item['version'];
		$item['new_version'] = $item['update_version'];

		\WP_CLI::log( $res );
		return true;

	}

}
