<?php
defined( 'ABSPATH' ) || exit;

/**
 * WPDB database interactions
 * @since    3.14.0
 * @version  3.24.0
 */
abstract class LLMS_Abstract_Database_Store {

	/**
	 * The Database ID of the record
	 * @var  null
	 */
	protected $id = null;

	/**
	 * Object properties
	 * @var  array
	 */
	private $data = array();

	protected $date_created = 'created';
	protected $date_updated = 'updated';

	/**
	 * Array of table column name => format
	 * @var  array
	 */
	protected $columns = array();

	/**
	 * Primary Key column name => format
	 * @var  array
	 */
	protected $primary_key = array(
		'id' => '%d',
	);

	/**
	 * Database Table Name
	 * @var  string
	 */
	protected $table = '';

	/**
	 * Database Table Prefix
	 * @var  string
	 */
	protected $table_prefix = 'lifterlms_';

	/**
	 * The record type
	 * Used for filters/actions
	 * Should be defined by extending classes
	 * @var  string
	 */
	protected $type = '';

	/**
	 * Constructor
	 * @since    3.14.0
	 * @version  3.21.0
	 */
	public function __construct() {

		if ( ! $this->id ) {

			// if created dates supported, add current time to the data on construction
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
	 * @param    string     $key  key to retrieve
	 * @return   mixed
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function __get( $key ) {

		return $this->data[ $key ];

	}

	/**
	 * Determine if the item exists in the database
	 * @return   boolean
	 * @since    3.14.7
	 * @version  3.15.0
	 */
	public function exists() {

		if ( $this->primary_key ) {
			return $this->read( $this->get_primary_key() ) ? true : false;
		}

		return false;
	}

	/**
	 * Get object data
	 * @param    string     $key    key to retrieve
	 * @param    boolean    $cache  if true, save data to to the object for future gets
	 * @return   mixed
	 * @since    3.14.0
	 * @version  3.16.0
	 */
	public function get( $key, $cache = true ) {

		if ( ! isset( $this->data[ $key ] ) && $this->id ) {
			$res = $this->read( $key )[ $key ];
			if ( $cache ) {
				$this->set( $key, $res );
			}
			return $res;
		}
		return $this->$key;

	}

	/**
	 * Set object data
	 * @param    string    $key  column name
	 * @param    mixed     $val  column value
	 * @return   void
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function __set( $key, $val ) {

		$this->data[ $key ] = $val;

	}

	/**
	 * General setter
	 * @param    string     $key   column name
	 * @param    mixed      $val   column value
	 * @param    boolean    $save  if true, immediately persists to database
	 * @return   self
	 * @since    3.14.0
	 * @version  3.21.0
	 */
	public function set( $key, $val, $save = false ) {

		$this->$key = $val;
		if ( $save ) {
			$update = array(
				$key => $val,
			);
			// if update date supported, add an updated date
			if ( $this->date_updated ) {
				$update[ $this->date_updated ] = llms_current_time( 'mysql' );
			}
			$this->update( $update );
		}

		return $this; // allow chaining like $this->set( $key, $val )->save();

	}

	/**
	 * Setup an object with an array of data
	 * @param    array     $data  key => val
	 * @return   void
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function setup( $data ) {

		foreach ( $data as $key => $val ) {
			$this->set( $key, $val, false );
		}

	}

	/**
	 * Create the item in the database
	 * @return   int|false
	 * @since    3.14.0
	 * @version  3.24.0
	 */
	private function create() {

		if ( ! $this->data ) {
			return false;
		}

		global $wpdb;
		$format = array_map( array( $this, 'get_column_format' ), array_keys( $this->data ) );
		$res = $wpdb->insert( $this->get_table(), $this->data, $format );
		if ( 1 === $res ) {
			$this->id = $wpdb->insert_id;
			do_action( 'llms_' . $this->type . '_created', $this->id, $this );
			return $wpdb->insert_id;
		}
		return false;

	}

	/**
	 * Delete the object from the database
	 * @return   boolean     true on success, false otherwise
	 * @since    3.14.0
	 * @version  3.24.0
	 */
	public function delete() {

		if ( ! $this->id ) {
			return false;
		}

		$id = $this->id;
		global $wpdb;
		$where = array_combine( array_keys( $this->primary_key ), array( $this->id ) );
		$res = $wpdb->delete( $this->get_table(), $where, array_values( $this->primary_key ) );
		if ( $res ) {
			$this->id = null;
			$this->data = array();
			do_action( 'llms_' . $this->type . '_deleted', $id, $this );
			return true;
		}
		return false;

	}

	/**
	 * Read object data from the database
	 * @param    array|string  $keys   key name (or array of keys) to retrieve from the database
	 * @return   array|false           key=>val array of data or false when record not found
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	private function read( $keys ) {

		global $wpdb;
		if ( is_array( $keys ) ) {
			$keys = implode( ', ', $keys );
		}
		$res = $wpdb->get_row( $wpdb->prepare( "SELECT {$keys} FROM {$this->get_table()} WHERE {$this->get_primary_key()} = %d", $this->id ), ARRAY_A );
		return ! $res ? false : $res;

	}

	/**
	 * Update object data in the database
	 * @param    array     $data  data to update as key=>val
	 * @return   bool
	 * @since    3.14.0
	 * @version  3.24.0
	 */
	private function update( $data ) {

		global $wpdb;
		$format = array_map( array( $this, 'get_column_format' ), array_keys( $data ) );
		$where = array_combine( array_keys( $this->primary_key ), array( $this->id ) );
		$res = $wpdb->update( $this->get_table(), $data, $where, $format, array_values( $this->primary_key ) );
		if ( $res ) {
			do_action( 'llms_' . $this->type . '_updated', $this->id, $this );
			return true;
		}
		return false;

	}

	/**
	 * Load the whole object from the database
	 * @return   void
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	protected function hydrate() {

		if ( $this->id ) {
			$res = $this->read( array_keys( $this->columns ) );
			if ( $res ) {
				$this->data = array_merge( $this->data, $res );
			}
		}

		return $this; // allow chaining

	}

	/**
	 * Save object to the database
	 * Creates is it doesn't already exist, updates if it does
	 * @return   boolean
	 * @since    3.14.0
	 * @version  3.24.0
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
	 * @param    string    $key  column name
	 * @return   string
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	private function get_column_format( $key ) {

		if ( isset( $this->columns[ $key ] ) ) {
			return $this->columns[ $key ];
		}
		return '%s';

	}

	/**
	 * Retrieve the primary key column name
	 * @return   string
	 * @since    3.15.0
	 * @version  3.15.0
	 */
	protected function get_primary_key() {

		$primary_key = array_keys( $this->primary_key );
		return preg_replace( '/[^a-zA-Z0-9_]/', '', $primary_key[0] );

	}

	/**
	 * Get the ID of the object
	 * @return   int
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the table Name
	 * @return   string
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	private function get_table() {

		global $wpdb;
		return $wpdb->prefix . $this->table_prefix . $this->table;

	}

	/**
	 * Retrive object as an array
	 * @return   array
	 * @since    3.14.0
	 * @version  3.14.0
	 */
	public function to_array() {

		return array_merge( $this->primary_key, $this->data );

	}

}
