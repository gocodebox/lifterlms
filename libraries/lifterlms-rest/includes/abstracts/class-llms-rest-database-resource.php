<?php
/**
 * Shared functiosn for database resource management.
 *
 * @package  LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_Database_Resource class..
 *
 * @since 1.0.0-beta.1
 */
abstract class LLMS_REST_Database_Resource {

	/**
	 * Resource Name/ID key.
	 *
	 * EG: key.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Resource Model classname.
	 *
	 * EG: LLMS_REST_API_Key.
	 *
	 * @var string
	 */
	protected $model = '';

	/**
	 * Default column values (for creating).
	 *
	 * @var array
	 */
	protected $default_column_values = array();

	/**
	 * Array of read only column names.
	 *
	 * @var array
	 */
	protected $read_only_columns = array( 'id' );

	/**
	 * Array of required columns (for creating).
	 *
	 * @var array
	 */
	protected $required_columns = array();

	/**
	 * Validate data supplied for creating/updating a resource.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data Associative array of data to set to a key's properties.
	 * @return WP_Error|true When data is invalid will return a WP_Error with information about the invalid properties,
	 *                            otherwise `true` denoting data is valid.
	 */
	protected function is_data_valid( $data ) {

		return true;

	}

	/**
	 * Create a new Resource
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data Associative array of data to set to the resource's properties.
	 * @return WP_Error|obj
	 */
	public function create( $data ) {

		$data = $this->create_prepare( $data );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return $this->save( new $this->model(), $data );

	}

	/**
	 * Prepare data for creation.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data Array of data.
	 * @return array
	 */
	public function create_prepare( $data ) {

		if ( ! empty( $data['id'] ) ) {
			// Translators: %s = name of the resource type (for example: "API Key").
			return new WP_Error( 'llms_rest_' . $this->id . '_exists', sprintf( __( 'Cannot create a new %s with a pre-defined ID.', 'lifterlms' ), $this->get_i18n_name() ) );
		}

		// Merge in default values.
		$data = wp_parse_args( array_filter( $data ), $this->get_default_column_values() );

		// Required Fields.
		foreach ( $this->required_columns as $key ) {

			if ( empty( $data[ $key ] ) ) {
				return new WP_Error(
					'llms_rest_' . $this->id . '_missing_' . $key,
					// Translators: %1$s = name of the resource type; %2$s = field name.
					sprintf( __( '%1$s "%2$s" is required.', 'lifterlms' ), $this->get_i18n_name(), $key )
				);
			}
		}

		$err = $this->is_data_valid( $data );
		if ( is_wp_error( $err ) ) {
			return $err;
		}

		return $data;

	}

	/**
	 * Delete a the resource.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int $id Resource ID.
	 * @return bool  `true` on success, `false` if the resource couldn't be found or an error was encountered during deletion.
	 */
	public function delete( $id ) {
		$obj = $this->get( $id, false );
		if ( $obj ) {
			return $obj->delete();
		}
		return false;
	}

	/**
	 * Retrieve an API Key object instance.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param int  $id API Key ID.
	 * @param bool $hydrate If true, pulls all key data from the database on instantiation.
	 * @return obj|false
	 */
	public function get( $id, $hydrate = true ) {
		$obj = new $this->model( $id, $hydrate );
		if ( $obj && $obj->exists() ) {
			return $obj;
		}
		return false;
	}

	/**
	 * Get default column values.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_default_column_values() {

		/**
		 * Allow customization of default Resource values.
		 *
		 * @since 1.0.0-beta.1
		 *
		 * @param array $values An associative array of default values.
		 */
		return apply_filters( 'llms_rest_' . $this->id . '_default_properties', $this->default_column_values );

	}

	/**
	 * Retrieve the translated resource name.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	protected function get_i18n_name() {
		return __( 'Resource', 'lifterlms' );
	}

	/**
	 * Update a resource.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data {
	 *     Array of data to update.
	 *
	 *     @type int $id (Required). Resource ID.
	 * }
	 * @return [type]
	 */
	public function update( $data ) {

		if ( empty( $data['id'] ) ) {
			// Translators: %s = name of the resource type (for example: "API Key").
			return new WP_Error( 'llms_rest_' . $this->id . '_missing_id', sprintf( __( 'No %s ID was supplied.', 'lifterlms' ), $this->get_i18n_name() ) );
		}

		$obj = $this->get( $data['id'] );
		if ( ! $obj || ! $obj->exists() ) {
			// Translators: %s = name of the resource type (for example: "API Key").
			return new WP_Error( 'llms_rest_' . $this->id . '_invalid_' . $this->id, sprintf( __( 'The requested %s could not be located.', 'lifterlms' ), $this->get_i18n_name() ) );
		}

		$data = $this->update_prepare( $data );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return $this->save( $obj, $data );

	}

	/**
	 * Prepare data for an update.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data Associative array of data to set to a resources properties.
	 * @return object|WP_Error
	 */
	protected function update_prepare( $data ) {

		// Filter out write-protected keys.
		$data = array_diff_key(
			$data,
			array_fill_keys( $this->read_only_columns, false )
		);

		$err = $this->is_data_valid( $data );
		if ( is_wp_error( $err ) ) {
			return $err;
		}

		return $data;

	}

	/**
	 * Persist data.
	 *
	 * This method assumes the supplied data has already been validated and sanitized.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param obj   $obj Instantiated object.
	 * @param array $data Associative array of data to persist.
	 * @return obj
	 */
	protected function save( $obj, $data ) {

		$obj->setup( $data )->save();
		return $obj;

	}

}
