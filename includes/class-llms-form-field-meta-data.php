<?php
/**
 * LifterLMS Event Model
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.36.0
 * @version 4.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS Event Model
 *
 * @since 3.36.0
 * @since 4.3.0 Added record `$type` property definition.
 */
class LLMS_Form_Field_Meta_Data extends LLMS_Abstract_Database_Store {

	/**
	 * Array of table column name => format
	 *
	 * @var array
	 */
	protected $columns = array(
		'field_id'   => '%s',
		'meta_key'   => '%s',
		'meta_value' => '%s',
	);

	/**
	 * Not supported
	 *
	 * @var string
	 */
	protected $date_created = null;

	/**
	 * Not supported
	 *
	 * @var null
	 */
	protected $date_updated = null;

	/**
	 * Database Table Name
	 *
	 * @var  string
	 */
	protected $table = 'form_fields_meta';

	/**
	 * The record type
	 *
	 * @var string
	 */
	protected $type = 'form_field_meta';

	/**
	 * Constructor
	 *
	 * @since [version]
	 *
	 * @param string  $field_id Field ID.
	 * @param boolean $hydrate  If true and an ID is supplied, hydrates the object on instantiation.
	 *
	 * @return void
	 */
	public function __construct( $field_id = null, $meta_key = null ) {

		$this->field_id = $field_id;

		if ( $field_id && $meta_key ) {
			$this->meta_key = $meta_key;
			$this->id = $this->find_id( $field_id, $meta_key );
		}

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

		$val = parent::get( $key, $cache );
		return $this->sanitize( $val, $this->meta_key,  );

	}

	private function sanitize( &$val, $meta_key ) {

		$metas = self::get_registered_metas();
		if ( isset( $metas[ $meta_key ] ) ) {
			$val = $metas[ $meta_key ]['sanitize_callback']( $val );
		}

		return $val;

	}

	public static function get_registered_metas() {

		$metas = array(
			'required' => array(
				'sanitize_callback' => 'llms_parse_bool',
				'validate_callback' => '__return_true',
			),
			// 'label' => array(
				// 'sanitize_callback' => array( 'LLMS_Form_Field_Meta_Data', 'sanitize_label' ),
				// 'validate_callback' => 'is_string',
			// ),
		);


		return apply_filters( 'llms_form_field_meta_properties', $metas );

	}

	private function find_id( $field_id, $meta_key ) {

		global $wpdb;
		return $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare( "SELECT id FROM {$this->get_table()} WHERE field_id = %s AND meta_key = %s", $field_id, $meta_key ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This query is safe.
		);

	}

	// This function doesn't really belong here...
	public function get_all() {

		global $wpdb;
		$res = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->prepare( "SELECT meta_key, meta_value FROM {$this->get_table()} WHERE field_id = %s", $this->field_id ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- This query is safe.
		);

		$metas =  wp_list_pluck( $res, 'meta_value', 'meta_key' );

		array_walk( $metas, array( $this, 'sanitize' ) );

		return $metas;

	}

}
