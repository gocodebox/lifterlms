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
class LLMS_Form_Field_Data extends LLMS_Abstract_Database_Store {

	/**
	 * Regex for validating/sanitizing the id field
	 *
	 * This uses a modified form of the HTML4 ID specification, we are
	 * removing the colon and dot characters in order to have more sane rest API
	 * endpoints.
	 *
	 * This also *does not* follow the newer HTML5 specification which allows
	 * any characters and isn't required to start with a letter.
	 *
	 * If you have a huge problem with this and have some really important reason for
	 * requiring leading numbers or ampersands in your IDs take it up with someone
	 * else. I don't want to hear it.
	 *
	 * @link https://www.w3.org/TR/html4/types.html#type-id
	 */
	const ID_FORMAT = '/(?P<id>^[a-zA-Z][\w-]*$)/';

	/**
	 * Array of table column name => format
	 *
	 * @var array
	 */
	protected $columns = array(
		'id'         => '%s',
		'name'       => '%s',
		'field_type' => '%s',
		'store'      => '%s',
		'store_key'  => '%s',
		'protected'  => '%d',
	);

	/**
	 * Primary Key column name => format
	 *
	 * @var array
	 */
	protected $primary_key = array(
		'id' => '%s',
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
	protected $table = 'form_fields';

	/**
	 * The record type
	 *
	 * @var string
	 */
	protected $type = 'form_field';

	/**
	 * Constructor
	 *
	 * @since [version]
	 *
	 * @param string  $id      Field ID.
	 * @param boolean $hydrate If true and an ID is supplied, hydrates the object on instantiation.
	 *
	 * @return void
	 */
	public function __construct( $id = null, $hydrate = false ) {

		if ( ! $id || ! self::validate( 'id', $id ) ) {
			throw new Exception( __( 'Missing or invalid ID.', 'lifterlms' ) );
		}

		$this->id = $id;
		if ( $this->id && $hydrate ) {
			$this->hydrate();
		}

	}

	/**
	 * Determines if the record can be updated
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function can_be_updated() {
		return $this->exists();
	}

	/**
	 * Retrieve the objects $data property
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_data() {
		return array_merge(
			array(
				'id' => $this->id,
			),
			parent::get_data()
		);
	}

	/**
	 * Retrieve a list of valid field types
	 *
	 * @since [version]
	 *
	 * @return string[]
	 */
	public static function get_enum_field_types() {

		/**
		 * Filters the list of registered data stores for form fields
		 *
		 * @since [version]
		 *
		 * @param string[] $stores An array of data store names.
		 */
		return apply_filters(
			'llms_form_field_types',
			array(
				'checkbox',
				'email',
				'number',
				'password',
				'tel',
				'radio',
				'select',
				'text',
				'textarea',
				'url',
			)
		);

	}

	/**
	 * Retrieve a list of valid data stores
	 *
	 * @since [version]
	 *
	 * @return string[]
	 */
	public static function get_enum_stores() {

		/**
		 * Filters the list of registered data stores for form fields
		 *
		 * @since [version]
		 *
		 * @param string[] $stores An array of data store names.
		 */
		return apply_filters(
			'llms_form_field_data_stores',
			array(
				'users',
				'usermeta',
			)
		);

	}

	/**
	 * Sanitize data before an insertion
	 *
	 * @since [version]
	 *
	 * @param string $key   Column name.
	 * @param mixed  $value Data to be inserted.
	 * @return mixed
	 */
	public static function sanitize( $key, $value ) {

		if ( 'protected' === $key ) {
			return llms_parse_bool( $value ) ? 1 : 0;
		}

		return preg_replace( '/[^\w-]/', '', $value );

	}

	/**
	 * Setup an object with an array of data
	 *
	 * Overrides parent method to perform data validation
	 * and sanitization, returning an error message when
	 * validation issues are found.
	 *
	 * @since [version]
	 *
	 * @param array $data key => val
	 * @return LLMS_Form_Field_Data|WP_Error Field instance on success or error when validation errors are found.
	 */
	public function setup( $data ) {

		$err = new WP_Error();
		foreach ( $data as $key => &$value ) {

			if ( ! self::validate( $key, $value ) ) {
				$err->add(
					sprintf( 'llms-form-field-invalid-data--%s', $key ),
					sprintf( __( 'Invalid value submitted for %s', 'lifterlms' ), $key ),
					compact( 'value' )
				);
			}

			$value = self::sanitize( $key, $value );

		}

		if ( $err->has_errors() ) {
			return $err;
		}

		return parent::setup( $data );

	}

	/**
	 * Validate data by column
	 *
	 * @since [version]
	 *
	 * @param string $key   Column name.
	 * @param mixed  $value Data to be inserted.
	 * @return boolean
	 */
	public static function validate( $key, $value ) {

		$ret = true;

		switch ( $key ) {
			case 'id':
				$ret = preg_match( self::ID_FORMAT, $value );
				break;
			case 'name':
			case 'store_key':
				$ret = preg_match( '/[\w-]/', $value );
				break;
			case 'field_type':
				$ret = in_array( $value, self::get_enum_field_types(), true );
				break;
			case 'store':
				$ret = in_array( $value, self::get_enum_stores(), true );
				break;
		}

		return $ret;

	}







	public function add_meta( $meta_key, $meta_value = '' ) {

		$metas = $this->get_metas_obj( $meta_key );
		return $metas->exists() ? false : $this->write_meta( $metas, $meta_key, $meta_value );

	}

	public function set_meta( $meta_key, $meta_value = '' ) {
		return $this->write_meta( $this->get_metas_obj( $meta_key ), $meta_key, $meta_value );
	}


	public function get_meta( $meta_key = null ) {

		if ( empty( $meta_key ) ) {
			return $this->get_metas_obj()->get_all();
		}

		return $this->get_metas_obj( $meta_key )->get( 'meta_value' );

	}

	public function del_meta( $meta_key ) {
		return $this->get_metas_obj( $meta_key )->delete();
	}


	private function get_metas_obj( $meta_key = null ) {
		return new LLMS_Form_Field_Meta_Data( $this->get_id(), $meta_key );
	}

	private function write_meta( $metas, $meta_key, $meta_value ) {

		return $metas->setup( compact( 'meta_key', 'meta_value' ) )->save();

	}

}
