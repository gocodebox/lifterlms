<?php
/**
 * Addon List class file
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.1
 */

namespace LifterLMS\CLI\Commands\AddOn;

use WP_CLI\Formatter;

/**
 * AddOn List command
 *
 * "List" is a php reserved keyword, so we enumerate instead.
 *
 * @since 0.0.1
 *
 * @link https://www.php.net/manual/en/reserved.keywords.php
 */
trait Enumerate {

	/**
	 * Gets a list of add-ons.
	 *
	 * Displays a list of add-ons with their activation status,
	 * license status, current version, update availability, etc...
	 *
	 * ## OPTIONS
	 *
	 * [--<field>=<value>]
	 * : Filter results based on the value of a field.
	 *
	 * [--field=<field>]
	 * : Prints the value of a single field for each add-on.
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
	 *   - count
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each add-on:
	 *
	 * * name
	 * * status
	 * * update
	 * * version
	 *
	 * These fields are optionally available:
	 *
	 * * update_version
	 * * license
	 * * title
	 * * channel
	 * * type
	 * * file
	 *
	 * ## EXAMPLES
	 *
	 *     # List all add-ons.
	 *     $ wp llms addon list
	 *
	 *     # List all add-ons in JSON format.
	 *     $ wp llms addon list --format=json
	 *
	 *     # List all add-ons by name only.
	 *     $ wp llms addon list --field=name
	 *
	 *     # List all add-ons with all available fields.
	 *     $ wp llms addon list --fields=all
	 *
	 *     # List all add-ons with a custom fields list.
	 *     $ wp llms addon list --fields=title,status,version
	 *
	 *     # List currently activated add-ons.
	 *     $ wp llms addon list --status=active
	 *
	 *     # List all theme add-ons.
	 *     $ wp llms addon list --type=theme
	 *
	 *     # List all add-ons with available updates.
	 *     $ wp llms addon list --update=available
	 *
	 *     # List all add-ons licensed on the site.
	 *     $ wp llms addon list --license=active
	 *
	 * @since 0.0.1
	 *
	 * @param array $args       Indexed array of positional command arguments.
	 * @param array $assoc_args Associative array of command options.
	 * @return null
	 */
	public function list( $args, $assoc_args ) {

		$fields     = array( 'name', 'status', 'update', 'version' );
		$all_fields = array_merge( $fields, array( 'update_version', 'license', 'title', 'channel', 'type', 'file' ) );

		// Determine if there's a user filter submitted through`--<field>=<value>`.
		$filter_field = array_values( array_intersect( $all_fields, array_keys( $assoc_args ) ) );

		$list = $this->get_filtered_items( $assoc_args, ! empty( $filter_field ) ? $filter_field[0] : '' );

		if ( ! empty( $assoc_args['fields'] ) && 'all' === $assoc_args['fields'] ) {
			$assoc_args['fields'] = $all_fields;
		}

		$formatter = new Formatter( $assoc_args, $fields );
		return $formatter->display_items( $list );

	}

}
