<?php
/**
 * LLMS_Database_Table class file
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
class LLMS_Database_Table {

	/**
	 * Error code: Table creation error.
	 */
	public const E_CREATE = 'llms-db-table-create';

	public const KEY_DEFAULT = 'default';
	public const KEY_PRIMARY = 'primary';
	public const KEY_UNIQUE  = 'unique';

	/**
	 * The table's unprefixed name.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * The database table schema definition.
	 *
	 * @var array
	 */
	protected array $schema;

	/**
	 * References the global `$wpdb` instance.
	 *
	 * @var wpdb
	 */
	protected $wpdb;

	/**
	 * Creates a new database instance.
	 *
	 * @since [version]
	 *
	 * @param array $schema Path to schema definition directory.
	 */
	public function __construct( array $schema ) {

		global $wpdb;
		$this->wpdb = $wpdb;

		$this->schema = $schema;
		$this->name   = $schema['name'];

	}

	/**
	 * Creates a table using the WP core {@see dbDelta} function.
	 *
	 * @since [version]
	 *
	 * @return WP_Error|bool An error object or `true` on success.
	 */
	public function create(): WP_Error|bool {

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$show     = $this->wpdb->hide_errors();
		$suppress = $this->wpdb->suppress_errors();

		dbDelta( $this->get_create_statement() );

		$this->wpdb->show_errors( $show );
		$this->wpdb->suppress_errors = $suppress;

		if ( ! $this->wpdb->last_error ) {
			return true;
		}

		return new WP_Error(
			self::E_CREATE,
			sprintf(
				// Translators: %s = the table name.
				__( 'An error was encountered creating the table "%s".', 'lifterlms' ),
				$this->get_prefixed_name(),
			),
			array(
				'table' => $this,
				'error' => $this->wpdb->last_error,
				'query' => $this->wpdb->last_query,
			)
		);

	}

	/**
	 * Retrieves a table creation SQL statement.
	 *
	 * @since [version]
	 *
	 * @return string Returns the table creation statement.
	 */
	public function get_create_statement(): string {

		$opts = LLMS_Database::instance()->get_table_options();
		$name = $this->get_prefixed_name();
		$cols = array_map(
			function( string $key ): string {
				return $this->get_column( $key )->get_create_string();
			},
			array_keys( $this->schema['columns'] )
		);
		$keys = array_map(
			array( $this, 'get_create_key_string' ),
			array_keys( $this->schema['keys'] ),
			array_values( $this->schema['keys'] )
		);

		$str  = "CREATE TABLE IF NOT EXISTS `{$name}` (" . PHP_EOL;
		$str .= implode(
			',' . PHP_EOL,
			array_filter(
				array_merge( $cols, $keys )
			)
		);
		$str .= PHP_EOL . ") {$opts};";

		return $str;

	}

	/**
	 * Retrieves a table key/index creation string.
	 *
	 * @since [version]
	 *
	 * @param string       $name The key/index name.
	 * @param string|array $cfg  The key configuration array or a shorthand string.
	 *                           More at {@see LLMS_Database_Table::setup_key}.
	 * @return string
	 */
	protected function get_create_key_string( string $name, string|array $cfg = array() ): string {

		$cfg = $this->setup_key( $name, $cfg );

		$key = 'KEY';
		if ( in_array( $cfg['type'], array( self::KEY_PRIMARY, self::KEY_UNIQUE ), true ) ) {
			$key = strtoupper( $cfg['type'] ) . " {$key}";
		}

		$parts = array();
		foreach ( $cfg['parts'] as $col => $len ) {
			$part    = "`{$col}`";
			$parts[] = is_null( $len ) ? $part : "$part({$len})";
		}

		$parts = implode( ',', $parts );
		$name  = self::KEY_PRIMARY === $cfg['type'] ? '' : "`{$name}` ";

		return "{$key} {$name}({$parts})";

	}

	/**
	 * Retrieves a column object for a registered column by column name.
	 *
	 * @since [version]
	 *
	 * @param string $column The column name.
	 * @return LLMS_Database_Column|bool
	 */
	public function get_column( string $column ): LLMS_Database_Column|bool {
		$schema = $this->schema['columns'][ $column ] ?? null;
		if ( is_null( $schema ) ) {
			return false;
		}
		return new LLMS_Database_Column( $column, $schema );
	}

	/**
	 * Retrieves the table's schema definition array.
	 *
	 * @since [version]
	 *
	 * @param bool $raw Whether to return the raw schema or to return the fully
	 *                  computed schema. Computing the schema will return
	 *                  the composed versions of any pre-composed column types
	 *                  and explicitly return default values which may be left
	 *                  undefined on the raw schema.
	 * @return array
	 */
	public function get_schema( bool $raw = true ): array {

		if ( $raw ) {
			return $this->schema;
		}

		$schema = $this->schema;
		foreach ( $schema['columns'] as $key => &$col ) {
			$col = $this->get_column( $key )->to_array();
		}

		foreach ( $schema['keys'] as $key => &$col ) {
			$col = $this->setup_key( $key, $col );
		}

		return $schema;

	}

	/**
	 * Retrieves the table's name.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Retrieves the full table name with prefixes.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_prefixed_name(): string {
		return $this->wpdb->prefix . LLMS_Database::instance()->get_prefix() . $this->name;
	}

	/**
	 * Determines if the table is installed.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	public function is_installed() {
		$query = $this->wpdb->query(
			$this->wpdb->prepare( 'SHOW TABLES LIKE %s', $this->get_prefixed_name() ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);
		return 1 === $query;
	}

	/**
	 * Configures a table key.
	 *
	 * Allows usage of shorthand schema definitions for table keys.
	 *
	 * @since [version]
	 *
	 * @param string       $name The key name. If defining a primary key, this should be the
	 *                           name of a column in the table.
	 * @param string|array $cfg  {
	 *     The key configuration array or a shorthand string. When passing a string it should
	 *     be one of the `LLMS_Database_Table::KEY_*` constants. If a string is passed,
	 *     the rest of the configuration is assumed to be the default.
	 *
	 *     @type string   $type   The key type, one of {@see LLMS_Database_Table::KEY_*} constants.
	 *     @type null|int $length The key length. If not supplied no length restraint will be imposed.
	 *     @type array    $parts  An associative array of key parts. The array key must correspond to
	 *                            a table column and the array value should be the key part length. As
	 *                            with `$length`, if not supplied no length restraint will be imposed.
	 *                            If not supplied, the array defaults to the keys name and defined length.
	 * }
	 */
	protected function setup_key( string $name, string|array $cfg = array() ) {

		$cfg = is_string( $cfg ) ? array( 'type' => $cfg ) : $cfg;
		$cfg = array_merge(
			array(
				'type'   => self::KEY_DEFAULT,
				'length' => null,
				'parts'  => array(),
			),
			$cfg
		);

		if ( empty( $cfg['parts'] ) ) {
			$cfg['parts'][ $name ] = $cfg['length'];
		}

		return $cfg;

	}

}
