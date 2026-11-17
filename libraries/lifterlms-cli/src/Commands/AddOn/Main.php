<?php
/**
 * LLMS_CLI_Command_Add_On file.
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.1
 */

namespace LifterLMS\CLI\Commands\AddOn;

use LifterLMS\CLI\Commands\AbstractCommand;
use WP_CLI\Formatter;

/**
 * Manage LifterLMS add-on plugins and themes.
 *
 * @since 0.0.1
 */
class Main extends AbstractCommand {

	// Include subcommands.
	use Activate,
		ChannelSet,
		Deactivate,
		Enumerate,
		Get,
		Install,
		Uninstall,
		Update;

	/**
	 * Accepts an add-on array and converts it to the format used by the output method
	 *
	 * @since 0.0.1
	 *
	 * @param array|LLMS_Add_On $item_or_addon Add-on object or add-on item array array from `llms_get_add_ons()`.
	 * @return array Associative array containing all possible fields as used by the output method.
	 */
	private function format_item( $item_or_addon ) {

		$addon = is_array( $item_or_addon ) ? llms_get_add_on( $item_or_addon ) : $item_or_addon;

		$formatted = array(
			'name'           => $addon->get( 'slug' ),
			'description'    => $addon->get( 'description' ),
			'status'         => $addon->get_status(),
			'license'        => str_replace( 'license_', '', $addon->get_license_status() ),
			'update'         => $addon->has_available_update() ? 'available' : 'none',
			'version'        => $addon->is_installed() ? $addon->get_installed_version() : 'N/A',
			'update_version' => $addon->get( 'version' ),
			'title'          => $addon->get( 'title' ),
			'channel'        => $addon->get_channel_subscription(),
			'type'           => $addon->get( 'type' ),
			'file'           => $addon->get( 'update_file' ),
			'permalink'      => $addon->get( 'permalink' ),
			'changelog'      => $addon->get( 'changelog' ),
			'documentation'  => $addon->get( 'documentation' ),
		);

		return $formatted;
	}

	/**
	 * Retrieve an array of available add-on slugs based on the supplied query criteria.
	 *
	 * This function passes data to `wp llms addon list` with specific filters and returns an associative
	 * array of add-on slugs from that list.
	 *
	 * This is used, mostly, to generate a list of available addons for various commands which provide an `--all` flag/option.
	 *
	 * @since 0.0.1
	 *
	 * @param string $status        Add-on status, passed as the `--status` option to `llms addon list`.
	 * @param bool   $check_license Whether or not the add-on should be licensed. This is used to determine what is installable / upgradeable.
	 * @param string $type          Add-on type. Accepts 'all' (default), 'plugin' or 'theme'.
	 * @return string[] Array of add-on slugs meeting the specified filters.
	 */
	private function get_available_addons( $status, $check_license, $type = 'all' ) {

		$list = \WP_CLI::runcommand(
			"llms addon list --format=json --status={$status} --fields=name,license,type",
			array(
				'return' => true,
			)
		);
		$list = array_filter(
			json_decode( $list, true ),
			function( $item ) use ( $check_license, $type ) {
				return ( ( $check_license && 'active' === $item['license'] ) || ! $check_license ) && ( 'all' === $type || $type === $item['type'] );
			}
		);

		return wp_list_pluck( $list, 'name' );

	}

	/**
	 * Retrieves an optionally filtered list of add-ons for use in the `list` command.
	 *
	 * @since 0.0.1
	 *
	 * @param array  $assoc_args   Associative array of command options.
	 * @param string $filter_field The optional name of the field to filter results by.
	 * @return array[] Array of add-on items.
	 */
	private function get_filtered_items( $assoc_args, $filter_field = '' ) {

		$addons = llms_get_add_ons();

		$list = array_filter(
			$addons['items'],
			function( $item ) {
				return // Skip anything without a slug.
				! empty( $item['slug'] ) &&
				// Skip the LifterLMS core.
				'lifterlms' !== $item['slug'] &&
				// Skip third party add-ons.
				! in_array( 'third-party', array_keys( $item['categories'] ), true );
			}
		);

		// Format remaining items.
		$list = array_map( array( $this, 'format_item' ), $list );

		// Filter by field value.
		if ( $filter_field ) {
			$field_val = $assoc_args[ $filter_field ];
			$list      = array_filter(
				$list,
				function( $item ) use ( $filter_field, $field_val ) {
					return $item[ $filter_field ] === $field_val;
				}
			);
		}

		// Alpha sort the list by slug.
		usort(
			$list,
			function( $a, $b ) {
				return strcmp( $a['name'], $b['name'] );
			}
		);

		return $list;

	}

	/**
	 * Reusable loop function for handling commands which accept one or more slugs as the commands first argument
	 *
	 * @since 0.0.1
	 *
	 * @param string[] $slugs      Array of add-on slugs, with or without the `lifterlms-` prefix.
	 * @param array    $assoc_args Associative array of command options from the original command.
	 * @param string   $callback   Name of the method to use for handling a single add-on for the given command.
	 *                             The callback should accept three arguments:
	 *                              + @type string      $slug       Add-on slug for the current item.
	 *                              + @type LLMS_Add_On $addon      Add-on object for the current item.
	 *                              + @type array       $assoc_args Array of arguments from the initial command.
	 *                             The callback should return a truthy to signal success and
	 *                             a falsy to signal an error.
	 * @return array {
	 *     Associative arrays containing details on the errors and successes encountered during the loop.
	 *
	 *     @type int $errors    Number of errors encountered in the loop.
	 *     @type int $successes Number of success encountered in the loop.
	 * }
	 */
	private function loop( $slugs, $assoc_args, $callback ) {

		$successes = 0;
		$errors    = 0;

		foreach ( $slugs as $slug ) {

			if ( empty( $slug ) ) {
				\WP_CLI::warning( 'Ignoring ambiguous empty slug value.' );
				continue;
			}

			$addon = $this->get_addon( $slug, true, 'warning' );
			if ( empty( $addon ) ) {
				$errors++;
				continue;
			}

			if ( ! $this->$callback( $slug, $addon, $assoc_args ) ) {
				$errors++;
				continue;
			}

			$successes++;

		}

		return compact( 'errors', 'successes' );

	}

}
