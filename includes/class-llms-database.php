<?php
/**
 * LLMS_Database class
 *
 * @package LifterLMS/Classes
 *
 * @since 4.4.0
 * @version 4.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Database table installation
 *
 * @since [version]
 */
class LLMS_Database {

	public static function create_tables() {

		global $wpdb;

		$wpdb->hide_errors();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( self::get_schema() );

	}

	private static function get_charset_collate() {

		global $wpdb;
		$collate = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';

		/**
		 * Filters the character collate to be used when creating new tables.
		 *
		 * @since [version]
		 *
		 * @param string $collate The character set collate string. An empty string if the DB doesn't have the `collation` capability.
		 */
		return apply_filters( 'llms_database_get_charset_collate', $collate );

	}

	public static function get_schema() {

		global $wpdb;
		$collate = self::get_charset_collate();

		$schema = '';
		foreach ( self::get_tables() as $name => $def ) {
			$schema .= "CREATE TABLE `{$wpdb->prefix}{$name}` ( {$def} ) {$collate};";
		}

		/**
		 * Filter the database table schema.
		 *
		 * @since 3.34.0
		 * @since [version] Moved from LLMS_Install::get_schema().
		 *
		 * @param string $schema  A semi-colon (`;`) separated list of database table creating commands.
		 * @param string $collate Database collation statement.
		 */
		return apply_filters( 'llms_install_get_schema', $schema, $collate );

	}

	private static function get_tables() {

		$tables = require LLMS_PLUGIN_DIR . 'includes/llms-database-tables.php';

		/**
		 * Filters the list of LifterLMS database tables
		 *
		 * @since [version]
		 *
		 * @param array $tables An array of database table definitions. Each array key is an unprefixed table
		 *                      and the array values are a string representing the database table definition in SQL syntax.
		 */
		return apply_filters( 'llms_database_get_tables', $tables );

	}

}
