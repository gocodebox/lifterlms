<?php
/**
 * LLMS_Database class file
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manages custom database tables.
 *
 * @since [version]
 */
class LLMS_Database {

	use LLMS_Trait_Singleton;

	/**
	 * Array of the LifterLMS core plugin's tables.
	 *
	 * @var array
	 */
	protected array $core_tables = array(
		'events',
		'events_open_sessions',
		'notifications',
		'product_to_voucher',
		'quiz_attempts',
		'sessions',
		'user_postmeta',
		'voucher_code_redemptions',
		'vouchers_codes',
	);

	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	protected string $prefix = 'lifterlms_';

	/**
	 * List of directories where schema files are stored.
	 *
	 * @var array
	 */
	protected $schema_paths = array(
		LLMS_PLUGIN_DIR . 'includes/schemas/database/',
	);

	/**
	 * An array of registered table schemas.
	 *
	 * @var array[]
	 */
	protected $tables = array();

	/**
	 * The database table options string.
	 *
	 * @var string
	 */
	protected ?string $table_options;

	/**
	 * References the global `$wpdb` instance.
	 *
	 * @var wpdb
	 */
	protected $wpdb;

	/**
	 * Constructor.
	 *
	 * @since [version]
	 */
	private function __construct() {

		global $wpdb;
		$this->wpdb = $wpdb;

	}

	/**
	 * Retrieves a list of the plugin's core tables.
	 *
	 * @since [version]
	 *
	 * @param bool $tables If `true` Returns an array of table objects, otherwise
	 *                     returns only the unprefixed table names.
	 * @return string[]|LLMS_Database_Table[]
	 */
	public function get_core_tables( bool $tables = false ): array {
		$ids = $this->core_tables;
		if ( ! $tables ) {
			return $ids;
		}
		return array_map(
			array( $this, 'get_table' ),
			$ids
		);
	}

	/**
	 * Retrieves the internal table prefix.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_prefix(): string {
		return $this->prefix;
	}

	/**
	 * Retrieves a table's schema.
	 *
	 * @since [version]
	 *
	 * @param string $table_name The unprefixed table name.
	 * @return bool|array
	 */
	public function get_schema( string $table_name ): bool|array {

		if ( $this->is_table_registered( $table_name ) ) {
			return $this->tables[ $table_name ];
		}

		$schema_file = $this->locate_schema_file( $table_name );
		if ( ! $schema_file ) {
			return false;
		}

		$schema = require $schema_file;
		return $schema;

	}

	/**
	 * Retrieves the table object for a registered table.
	 *
	 * @since [version]
	 *
	 * @param string $table_name The table's name.
	 * @ LLMS_Database_Table|bool Returns the table object or `false` if the
	 *                                  table's schema cannot be found.
	 */
	public function get_table( string $table_name ): LLMS_Database_Table|bool {
		$schema = $this->get_schema( $table_name );
		if ( ! $schema ) {
			return false;
		}
		return new LLMS_Database_Table( $schema );
	}

	/**
	 * Retrieves the database table options SQL string.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_table_options(): string {

		if ( isset( $this->table_options ) ) {
			return $this->table_options;
		}

		if ( ! $this->wpdb->has_cap( 'collation' ) ) {
			$this->table_options = '';
			return $this->table_options;
		}

		$opts = array();
		if ( ! empty( $this->wpdb->charset ) ) {
			$opts[] = "DEFAULT CHARACTER SET {$this->wpdb->charset}";
		}
		if ( ! empty( $this->wpdb->collate ) ) {
			$opts[] = "COLLATE {$this->wpdb->collate}";
		}

		$this->table_options = implode( ' ', $opts );
		return $this->table_options;

	}

	/**
	 * Locates a schema file for the specified table.
	 *
	 * @since [version]
	 *
	 * @param string $table_name The table name.
	 * @return bool|string The full path to the schema file or `false` if the
	 *                     no schema file could be found.
	 */
	protected function locate_schema_file( string $table_name ): bool|string {
		$table_name = str_replace( '_', '-', $table_name );
		foreach ( $this->schema_paths as $path ) {
			$file = $path . $table_name . '.php';
			if ( file_exists( $file ) ) {
				return $file;
			}
		}
		return false;
	}

	/**
	 * Determines if the specified table is registered.
	 *
	 * @since [version]
	 *
	 * @param string $table_name The table name.
	 * @return bool
	 */
	public function is_table_registered( string $table_name ): bool {
		return array_key_exists( $table_name, $this->tables );
	}

	/**
	 * Adds a directory to the list of directories to use for schema file lookups.
	 *
	 * @since [version]
	 *
	 * @param string $path The directory path.
	 */
	public function register_schema_path( string $path ): void {
		$this->schema_paths[] = trailingslashit( $path );
	}

	/**
	 * Registers a table by name.
	 *
	 * @since [version]
	 *
	 * @param string $table_name The name of the table.
	 * @return bool|null Returns `true` on success, `false` if the schema doesn't
	 *                   exist, and `null` if the table is already registered.
	 */
	public function register_table( string $table_name ): ?bool {

		// Table is already registered.
		if ( $this->is_table_registered( $table_name ) ) {
			return null;
		}

		$schema = $this->get_schema( $table_name );
		// Schema definition doesn't exist for the table.
		if ( ! $schema ) {
			return false;
		}

		// Register with LifterLMS.
		$this->tables[ $table_name ] = $schema;

		// Register the table with the WordPress database.
		$prefixed              = $this->prefix . $table_name;
		$this->wpdb->$prefixed = $this->wpdb->prefix . $prefixed;
		$this->wpdb->tables[]  = $prefixed;

		return true;

	}

}
