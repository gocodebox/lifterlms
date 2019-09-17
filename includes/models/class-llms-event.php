<?php
/**
 * LifterLMS Event Model
 *
 * @since 3.36.0
 * @version 3.36.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Event Model
 *
 * @since 3.36.0
 */
class LLMS_Event extends LLMS_Abstract_Database_Store {

	/**
	 * Array of table column name => format
	 *
	 * @var  array
	 */
	protected $columns = array(
		'date'         => '%s',
		'actor_id'     => '%d',
		'object_type'  => '%s',
		'object_id'    => '%d',
		'event_type'   => '%s',
		'event_action' => '%s',
		'meta'         => '%s',
	);

	/**
	 * Created date key name.
	 *
	 * @var string
	 */
	protected $date_created = 'date';

	/**
	 * Updated date not supported.
	 *
	 * @var null
	 */
	protected $date_updated = null;

	/**
	 * Database Table Name
	 *
	 * @var  string
	 */
	protected $table = 'events';

	/**
	 * Constructor
	 *
	 * @since 3.36.0
	 *
	 * @param int  $id Event ID.
	 * @param bool $hydrate If true, hydrates the object on instantiation if an ID is supplied.
	 */
	public function __construct( $id = null, $hydrate = false ) {

		$this->id = $id;
		if ( $this->id && $hydrate ) {
			$this->hydrate();
		}

		// Adds created and updated dates on instantiation.
		parent::__construct();

	}

	/**
	 * Delete meta data
	 *
	 * @since 3.36.0
	 *
	 * @param string $key Meta key, if omitted deletes *all* metadata.
	 * @param bool   $save If true, saves updated metadata to the database.
	 * @return LLMS_Event
	 */
	public function delete_meta( $key = null, $save = false ) {

		if ( ! $key ) {
			return $this->set_unencoded_metas( array(), $save );
		}

		$all = $this->get_meta( null, false );
		unset( $all[ $key ] );
		return $this->set_unencoded_metas( $all, $save );

	}

	/**
	 * Retrieve metadata.
	 *
	 * @since 3.36.0
	 *
	 * @param string $key Metadata key, if omitted returns an associative array of all metadata as key=>val pairs.
	 * @param bool   $cache If true, uses cached data when available.
	 * @return mixed
	 */
	public function get_meta( $key = null, $cache = true ) {

		$all = $this->get( 'meta', $cache );
		$all = empty( $all ) ? array() : json_decode( $all, true );

		if ( ! $key ) {
			return $all;
		}

		return isset( $all[ $key ] ) ? $all[ $key ] : null;

	}

	/**
	 * Update/Add a single meta item.
	 *
	 * @since 3.36.0
	 *
	 * @param string $key Meta key.
	 * @param mixed  $val Meta value.
	 * @param bool   $save If true, saves the updated metadata to the database.
	 * @return LLMS_Event
	 */
	public function set_meta( $key, $val, $save = false ) {

		$all         = $this->get_meta();
		$all[ $key ] = $val;
		return $this->set_unencoded_metas( $all, $save );

	}

	/**
	 * Update/Add multiple metas.
	 *
	 * @since 3.36.0
	 *
	 * @param array $metas Associative array of metadata to update/add as key=>val pairs.
	 * @param bool  $save If true, saves the updated metadata to the database.
	 * @return LLMS_Event
	 */
	public function set_metas( $metas, $save = false ) {

		foreach ( $metas as $key => $val ) {
			$this->set_meta( $key, $val );
		}

		if ( $save ) {
			$this->save();
		}

		return $this;

	}

	/**
	 * Encode the array of metadata before setting it to the object.
	 *
	 * @since 3.36.0
	 *
	 * @param array $metas Associative array of metadata to update/add as key=>val pairs.
	 * @param bool  $save If true, saves the updated metadata to the database.
	 * @return LLMS_Event
	 */
	protected function set_unencoded_metas( $metas, $save = false ) {
		return $this->set( 'meta', wp_json_encode( $metas ), $save );
	}

}
