<?php
/**
 * Addon Get class file
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.1
 */

namespace LifterLMS\CLI\Commands\AddOn;

use WP_CLI\Formatter;

/**
 * AddOn Get command
 *
 * @since 0.0.1
 */
trait Get {

	/**
	 * Get information about an add-on.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The slug of the add-on to get information about.
	 *
	 * ## OPTIONS
	 *
	 * [--field=<field>]
	 * : Retrieve a single piece of information about the add-on.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to only the specified fields. Use "all" to display all available fields.
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
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each add-on:
	 *
	 * * name
	 * * title
	 * * version
	 * * description
	 * * status
	 *
	 * These fields are optionally available:
	 *
	 * * update
	 * * update_version
	 * * license
	 * * title
	 * * channel
	 * * type
	 * * file
	 * * permalink
	 * * changelog
	 * * documentation
	 *
	 * @since 0.0.1
	 *
	 * @param array $args       Indexed array of positional command arguments.
	 * @param array $assoc_args Associative array of command options.
	 * @return null
	 */
	public function get( $args, $assoc_args ) {

		$addon      = $this->get_addon( $args[0], true );
		$fields     = array( 'name', 'title', 'version', 'description', 'status' );
		$all_fields = array_merge( $fields, array( 'update', 'update_version', 'license', 'title', 'channel', 'type', 'file', 'permalink', 'changelog', 'documentation' ) );

		if ( ! empty( $assoc_args['fields'] ) ) {
			$assoc_args['fields'] = 'all' === $assoc_args['fields'] ? $all_fields : $assoc_args['fields'];
		} else {
			$assoc_args['fields'] = $fields;
		}

		// Get formatted item.
		$item = $this->format_item( $addon );

		// Put the keys in the order defined by input args.
		$item = array_merge( array_flip( $assoc_args['fields'] ), $item );

		// Pass the item as an array and all fields for proper formatting when --field=<field> is passed.
		$list          = array( $item );
		$format_fields = $all_fields;

		// Format when displaying multiple fields.
		if ( empty( $assoc_args['field'] ) ) {

			$list = array();
			foreach ( $item as $Field => $Value ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
				if ( ! in_array( $Field, $assoc_args['fields'], true ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
					continue;
				}
				$list[] = compact( 'Field', 'Value' );
			}
			$format_fields = array( 'Field', 'Value' );
			unset( $assoc_args['fields'] );

		}

		$formatter = new Formatter( $assoc_args, $format_fields );
		return $formatter->display_items( $list );

	}

}
