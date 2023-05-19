<?php
/**
 * LLMS_Database_Column class file
 *
 * @package LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Database table column model.
 *
 * @since [version]
 */
class LLMS_Database_Column {

	/**
	 * Whether or not the column allows a `null` value.
	 *
	 * @var bool
	 */
	protected bool $allow_null = true;

	/**
	 * The column's default value.
	 *
	 * @var null|string|int|float
	 */
	protected null|string|int|float $default = null;

	/**
	 * Column description.
	 *
	 * This is *not* added to the table as a comment, instead it is used for
	 * internal and public-facing documentation of the table's schema.
	 *
	 * @var string
	 */
	protected string $description = '';

	/**
	 * Whether or not the column is auto-incremented.
	 *
	 * @var bool
	 */
	protected bool $auto_increment = false;

	/**
	 * The column's length.
	 *
	 * Data types which don't allow length specification should use `null`.
	 *
	 * @var int|null
	 */
	protected ?int $length = null;

	/**
	 * The column's name.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * The column's type.
	 *
	 * Any valid MySQL column type may be provided.
	 *
	 * Additional "pre-composed" types can be specified as a shorthand, allowing
	 * a partial column definition, {@see LLMS_Database_Column::prepare_schema}.
	 *
	 * @link https://dev.mysql.com/doc/refman/8.0/en/data-types.html
	 *
	 * @var string
	 */
	protected string $type = 'varchar';

	/**
	 * Whether or not a numeric column is unsigned.
	 *
	 * @var bool|null
	 */
	protected ?bool $unsigned = null;

	/**
	 * Initializes a database table column object.
	 *
	 * @since [version]
	 *
	 * @param string $name   The column name.
	 * @param array  $schema The column's settings.
	 */
	public function __construct( string $name, array $schema = array() ) {

		$this->name = $name;

		// Setup object props.
		foreach ( $this->prepare_schema( $schema ) as $key => $val ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $val;
			}
		}

		if ( is_null( $this->default ) && $this->allow_null ) {
			$this->default = 'NULL';
		}

	}

	/**
	 * Configures a schema array according to pre-composed types.
	 *
	 * There are two valid pre-composed types: `id` and `primary_id`. These types
	 * follow the WordPress core pattern for column IDs. The `primary_id` is
	 * intended to be the primary key on a table and the `id` is used when it
	 * is referencing the `primary_id` on another table.
	 *
	 * For example, the user post meta table uses the `primary_id` type on its
	 * `meta_id` column (the primary key) and uses the `id` type for the `user_id`
	 * and `post_id` columns which reference the primary IDs on the `wp_users`
	 * and `wp_posts` tables, respectively.
	 *
	 * @since [version]
	 *
	 * @param array $schema The provided schema.
	 * @return array
	 */
	protected function prepare_schema( array $schema ): array {

		$type = $schema['type'] ?? $this->type;

		// Not a pre-composed type.
		if ( ! in_array( $type, array( 'id', 'primary_id' ), true ) ) {
			return $schema;
		}

		$schema['type'] = 'bigint';

		$defaults = array(
			'length'     => 20,
			'unsigned'   => true,
			'allow_null' => false,
		);

		if ( 'primary_id' === $type ) {
			$defaults['auto_increment'] = true;
		}

		return array_merge( $defaults, $schema );

	}

	/**
	 * Retrieves an SQL statement used when creating the table the column's table.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_create_string(): string {

		$type = $this->type;
		if ( $this->length ) {
			$type .= "({$this->length})";
		}

		$default = '';
		if ( ! is_null( $this->default ) ) {
			$default_val = 'NULL' === $this->default ? $this->default : "'{$this->default}'";
			$default     = "DEFAULT {$default_val}";
		}

		$parts = array(
			"`{$this->name}`",
			$type,
			$this->unsigned ? 'UNSIGNED' : '',
			! $this->allow_null ? 'NOT NULL' : '',
			$default,
			$this->auto_increment ? 'AUTO_INCREMENT' : '',
		);

		return implode( ' ', array_filter( $parts ) );

	}

	/**
	 * Retrieves whether or not `AUTO_INCREMENT` is enabled for the column.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	public function get_auto_increment(): bool {
		return $this->auto_increment;
	}

	/**
	 * Retrieves whether column value can be `null`.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	public function get_allow_null(): bool {
		return $this->allow_null;
	}

	/**
	 * Retrieves the column's default value.
	 *
	 * A `null` return denotes there is no default value.
	 *
	 * If the default value is `null`, the string `NULL` will be returned.
	 *
	 * @since [version]
	 *
	 * @return null|string|float|int
	 */
	public function get_default(): null|string|float|int {
		return $this->default;
	}

	/**
	 * Retrieves the columns length.
	 *
	 * Columns that don't support lengths, such as `datetime` will return `null`.
	 *
	 * @since [version]
	 *
	 * @return int|null
	 */
	public function get_length(): ?int {
		return $this->length;
	}

	/**
	 * Retrieves the column name.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->name;
	}

	/**
	 * Retrieves the columns type.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Retrieves whether or not the column is unsigned.
	 *
	 * Non-numeric column types will return `null`.
	 *
	 * @since [version]
	 *
	 * @return bool|null
	 */
	public function get_unsigned(): ?bool {
		return $this->unsigned;
	}

	/**
	 * Converts the object into an associative array.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'allow_null'     => $this->allow_null,
			'description'    => $this->description,
			'default'        => $this->default,
			'auto_increment' => $this->auto_increment,
			'length'         => $this->length,
			'type'           => $this->type,
			'unsigned'       => $this->unsigned,
		);
	}

}
