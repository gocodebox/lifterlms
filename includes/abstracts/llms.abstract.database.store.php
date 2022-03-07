<?php
/**
 * WPDB database interactions
 *
 * @package LifterLMS/Abstracts/Classes
 *
 * @since 3.14.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * WPDB database interactions abstract class
 *
 * @since 3.14.0
 * @since 3.33.0 setup() method returns self instead of void.
 * @since 3.34.0 to_array() method returns value of the primary key instead of the format.
 * @since 3.36.0 Prevent undefined index error when attempting to retrieve an unset value from an unsaved object.
 *               Hydrate before returning an array via the `to_array()` method.
 * @since 4.3.0 Add deprecated hook calls to preserve backwards compatibility for extending classes which have no `$type` property declaration.
 *              Updated the `$type` property to have a default placeholder value.
 */
abstract class LLMS_Abstract_Database_Store {

	/**
	 * The Database ID of the record
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * Object properties
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Column name of the record's "created" date
	 *
	 * This can be set to an empty string if the extending
	 * class does not utilize or require created date storage.
	 *
	 * @var string
	 */
	protected $date_created = 'created';

	/**
	 * Column name of the record's "updated" date
	 *
	 * This can be set to an empty string if the extending
	 * class does not utilize or require updated date storage.
	 *
	 * @var string
	 */
	protected $date_updated = 'updated';

	/**
	 * Array of table column name => format
	 *
	 * @var array
	 */
	protected $columns = array();

	/**
	 * Primary Key column name => format
	 *
	 * @var array
	 */
	protected $primary_key = array(
		'id' => '%d',
	);

	/**
	 * Database Table Name
	 *
	 * @var string
	 */
	protected $table = '';

	/**
	 * Database Table Prefix
	 *
	 * @var string
	 */
	protected $table_prefix = 'lifterlms_';

	/**
	 * The record type
	 *
	 * Used for filters/actions.
	 *
	 * This is a placeholder which should be redefined in any extending classes.
	 *
	 * @var string
	 */
	protected $type = '_db_record_';

	/**
	 * Constructor
	 *
	 * @since 3.14.0
	 * @since 3.21.0 Unknown.
	 *
	 * @return void
	 */
	public function __construct() {

		if ( ! $this->id ) {

			// If created dates supported, add current time to the data on construction.
			if ( $this->date_created ) {
				$this->set( $this->date_created, llms_current_time( 'mysql' ), false );
			}

			if ( $this->date_updated ) {
				$this->set( $this->date_updated, llms_current_time( 'mysql' ), false );
			}
		}

	}

	/**
	 * Get object data
	 *
	 * @since 3.14.0
	 *
	 * @param string $key Key to retrieve.
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->data[ $key ];
	}

	/**
	 * Determine if the item exists in the database
	 *
	 * @since 3.14.7
	 * @since 3.15.0 Unknown.
	 *
	 * @return boolean
	 */
	public function exists() {

		if ( $this->primary_key ) {
			return $this->read( $this->get_primary_key() ) ? true : false;
		}

		return false;

	}

	/**
	 * Get object data
	 *
	 * @since 3.14.0
	 * @since 3.16.0 Unknown.
	 * @since 3.36.0 Prevent undefined index error when attempting to retrieve an unset value from an unsaved object.
	 *
	 * @param string  $key   Key to retrieve.
	 * @param boolean $cache If true, save data to to the object for future gets.
	 * @return mixed
	 */
	public function get( $key, $cache = true ) {

		$key_exists = isset( $this->data[ $key ] );
		if ( ! $key_exists && $this->id ) {
			$res = $this->read( $key )[ $key ];
			if ( $cache ) {
				$this->set( $key, $res );
			}
			return $res;
		}
		return $key_exists ? $this->$key : null;

	}

	/**
	 * Set object data
	 *
	 * @since 3.14.0
	 *
	 * @param string $key Column name.
	 * @param mixed  $val Column value.
	 * @return void
	 */
	public function __set( $key, $val ) {
		$this->data[ $key ] = $val;
	}

	/**
	 * General setter
	 *
	 * @since 3.14.0
	 * @since 3.21.0 Unknown.
	 *
	 * @param string  $key  Column name.
	 * @param mixed   $val  Column value.
	 * @param boolean $save If true, immediately persists to database.
	 * @return LLMS_Abstract_Database_Store Instance of the current object, useful for chaining.
	 */
	public function set( $key, $val, $save = false ) {

		$this->$key = $val;
		if ( $save ) {
			$update = array(
				$key => $val,
			);
			// If update date supported, add an updated date.
			if ( $this->date_updated ) {
				$update[ $this->date_updated ] = llms_current_time( 'mysql' );
			}
			$this->update( $update );
		}

		return $this;

	}

	/**
	 * Setup an object with an array of data
	 *
	 * @since 3.14.0
	 * @since 3.33.0 Return self for chaining instead of void.
	 *
	 * @param array $data key => val
	 * @return LLMS_Abstract_Database_Store Instance of the current object, useful for chaining.
	 */
	public function setup( $data ) {

		foreach ( $data as $key => $val ) {
			$this->set( $key, $val, false );
		}

		return $this;

	}

	/**
	 * Create the item in the database
	 *
	 * @since 3.14.0
	 * @since 3.24.0 Unknown.
	 * @since 4.3.0 Added deprecated hook call to `llms__created` action to preserve backwards compatibility.
	 * @since 6.0.0 Removed deprecated `llms__created` action hook.
	 *
	 * @return int|false Record ID on success, false on error or when there's nothing to save.
	 */
	private function create() {

		if ( ! $this->data ) {
			return false;
		}

		global $wpdb;
		$format = array_map( array( $this, 'get_column_format' ), array_keys( $this->data ) );
		$res    = $wpdb->insert( $this->get_table(), $this->data, $format ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		if ( 1 === $res ) {

			$this->id = $wpdb->insert_id;

			/**
			 * Fires when a new database record is created.
			 *
			 * The dynamic portion of this hook, `$this->type`, refers to the record type.
			 *
			 * @since Unknown.
			 *
			 * @param int                          $id  Record ID.
			 * @param LLMS_Abstract_Database_Store $obj Instance of the record object.
			 */
			do_action( "llms_{$this->type}_created", $this->id, $this );

			return $this->id;
		}
		return false;

	}

	/**
	 * Delete the object from the database
	 *
	 * @since 3.14.0
	 * @since 3.24.0 Unknown.
	 * @since 4.3.0 Added deprecated hook call to `llms__deleted` action to preserve backwards compatibility.
	 * @since 6.0.0 Removed deprecated `llms__deleted` action hook.
	 *
	 * @return boolean `true` on success, `false` otherwise.
	 */
	public function delete() {

		if ( ! $this->id ) {
			return false;
		}

		$id = $this->id;
		global $wpdb;
		$where = array_combine( array_keys( $this->primary_key ), array( $this->id ) );
		$res   = $wpdb->delete( $this->get_table(), $where, array_values( $this->primary_key ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		if ( $res ) {
			$this->id   = null;
			$this->data = array();

			/**
			 * Fires when a new database record is created.
			 *
			 * The dynamic portion of this hook, `$this->type`, refers to the record type.
			 *
			 * @since Unknown.
			 *
			 * @param int                          $id  Record ID.
			 * @param LLMS_Abstract_Database_Store $obj Instance of the record object.
			 */
			do_action( "llms_{$this->type}_deleted", $id, $this );

			return true;
		}
		return false;

	}

	/**
	 * Read object data from the database
	 *
	 * @since 3.14.0
	 *
	 * @param string[]|string $keys Key name (or array of keys) to retrieve from the database.
	 * @return array|false Returns a key=>val array of data or `false` when record not found.
	 */
	private function read( $keys ) {

		global $wpdb;
		if ( is_array( $keys ) ) {
			$keys = implode( ', ', $keys );
		}
		$res = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare( "SELECT {$keys} FROM {$this->get_table()} WHERE {$this->get_primary_key()} = %d", $this->id ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This query is safe.
			ARRAY_A
		);
		return ! $res ? false : $res;

	}

	/**
	 * Update object data in the database
	 *
	 * @since 3.14.0
	 * @since 3.24.0 Unknown.
	 * @since 4.3.0 Added deprecated hook call to `llms__updated` action to preserve backwards compatibility.
	 * @since 6.0.0 Removed deprecated `llms__updated` action hook.
	 *
	 * @param array $data Data to update as key=>val.
	 * @return boolean
	 */
	private function update( $data ) {

		global $wpdb;
		$format = array_map( array( $this, 'get_column_format' ), array_keys( $data ) );
		$where  = array_combine( array_keys( $this->primary_key ), array( $this->id ) );
		$res    = $wpdb->update( $this->get_table(), $data, $where, $format, array_values( $this->primary_key ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		if ( $res ) {

			/**
			 * Fires when a new database record is updated.
			 *
			 * The dynamic portion of this hook, `$this->type`, refers to the record type.
			 *
			 * @since Unknown.
			 *
			 * @param int                          $id  Record ID.
			 * @param LLMS_Abstract_Database_Store $obj Instance of the record object.
			 */
			do_action( "llms_{$this->type}_updated", $this->id, $this );

			return true;
		}
		return false;

	}

	/**
	 * Load the whole object from the database
	 *
	 * @since 3.14.0
	 *
	 * @return LLMS_Abstract_Database_Store instance of the current object, useful for chaining.
	 */
	protected function hydrate() {

		if ( $this->id ) {
			$res = $this->read( array_keys( $this->columns ) );
			if ( $res ) {
				$this->data = array_merge( $this->data, $res );
			}
		}

		return $this;

	}

	/**
	 * Save object to the database
	 *
	 * Creates it if doesn't already exist, updates if it does.
	 *
	 * @since 3.14.0
	 * @since 3.24.0 Unknown.
	 *
	 * @return boolean
	 */
	public function save() {

		if ( ! $this->id ) {
			$id = $this->create();
			if ( $id ) {
				return true;
			}
			return false;
		} else {
			return $this->update( $this->data );
		}

	}

	/**
	 * Retrieve the format for a column
	 *
	 * @since 3.14.0
	 *
	 * @param string $key Column name.
	 * @return string
	 */
	private function get_column_format( $key ) {

		if ( isset( $this->columns[ $key ] ) ) {
			return $this->columns[ $key ];
		}
		return '%s';

	}

	/**
	 * Retrieve the primary key column name
	 *
	 * @since 3.15.0
	 *
	 * @return string
	 */
	protected function get_primary_key() {
		$primary_key = array_keys( $this->primary_key );
		return preg_replace( '/[^a-zA-Z0-9_]/', '', $primary_key[0] );
	}

	/**
	 * Get the ID of the object
	 *
	 * @since 3.14.0
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the table Name
	 *
	 * @since 3.14.0
	 *
	 * @return string
	 */
	private function get_table() {

		global $wpdb;
		return $wpdb->prefix . $this->table_prefix . $this->table;

	}

	/**
	 * Retrieve object as an array
	 *
	 * @since 3.14.0
	 * @since 3.34.0 Return the item ID instead of item format as the value of the primary key.
	 * @since 3.36.0 Hydrate before returning the array.
	 *
	 * @return array
	 */
	public function to_array() {

		$this->hydrate();
		return array_merge( array_combine( array_keys( $this->primary_key ), array( $this->id ) ), $this->data );

	}

}
