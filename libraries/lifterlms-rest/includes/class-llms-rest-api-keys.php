<?php
/**
 * CRUD API Keys.
 *
 * @package  LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_REST_API_Keys class.
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_API_Keys extends LLMS_REST_Database_Resource {

	use LLMS_REST_Trait_Singleton;

	/**
	 * Resource Name/ID key.
	 *
	 * EG: key.
	 *
	 * @var string
	 */
	protected $id = 'key';

	/**
	 * Resource Model classname.
	 *
	 * EG: LLMS_REST_API_Key.
	 *
	 * @var string
	 */
	protected $model = 'LLMS_REST_API_Key';

	/**
	 * Default column values (for creating).
	 *
	 * @var array
	 */
	protected $default_column_values = array(
		'permissions' => 'read',
	);

	/**
	 * Array of read only column names.
	 *
	 * @var array
	 */
	protected $read_only_columns = array(
		'id',
		'consumer_key',
		'consumer_secret',
		'truncated_key',
	);

	/**
	 * Array of required columns (for creating).
	 *
	 * @var array
	 */
	protected $required_columns = array(
		'description',
		'user_id',
		'permissions',
	);

	/**
	 * Create a new API Key
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data {
	 *     Associative array of data to set to a key's properties.
	 *
	 *     @type string $description (Required) A friendly name for the key.
	 *     @type int $user_id WP_User (Required) ID of the key's owner.
	 *     @type string $permissions (Required) Permission string for the key. Accepts `read`, `write`, or `read_write`.
	 * }
	 * @return WP_Error|LLMS_REST_API_Key
	 */
	public function create( $data ) {

		$data = $this->create_prepare( $data );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$api_key = new LLMS_REST_API_Key();

		$key    = 'ck_' . llms_rest_random_hash();
		$secret = 'cs_' . llms_rest_random_hash();

		$data['consumer_key']    = llms_rest_api_hash( $key );
		$data['consumer_secret'] = $secret;
		$data['truncated_key']   = substr( $key, -7 );

		// Set and save.
		$api_key->setup( $data )->save();

		// Return the unhashed key on creation to be displayed once and never stored.
		$api_key->set( 'consumer_key_one_time', $key );

		return $api_key;

	}

	/**
	 * Retrieve the base admin url for managing API keys.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	public function get_admin_url() {
		return add_query_arg(
			array(
				'page'    => 'llms-settings',
				'tab'     => 'rest-api',
				'section' => 'keys',
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Retrieve the translated resource name.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return string
	 */
	protected function get_i18n_name() {
		return __( 'API Key', 'lifterlms' );
	}

	/**
	 * Retrieve an array of options for API Key Permissions.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return array
	 */
	public function get_permissions() {
		return array(
			'read'       => __( 'Read', 'lifterlms' ),
			'write'      => __( 'Write', 'lifterlms' ),
			'read_write' => __( 'Read / Write', 'lifterlms' ),
		);
	}

	/**
	 * Validate data supplied for creating/updating a key.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param array $data {
	 *     Associative array of data to set to a key's properties.
	 *
	 *     @type string $description A friendly name for the key.
	 *     @type int $user_id WP_User ID of the key's owner.
	 *     @type string $permissions Permission string for the key. Accepts `read`, `write`, or `read_write`.
	 * }
	 * @return WP_Error|true When data is invalid will return a WP_Error with information about the invalid properties,
	 *                            otherwise `true` denoting data is valid.
	 */
	protected function is_data_valid( $data ) {

		// First conditions prevents '', '0', 0, etc... & second prevents invalid / non existant user ids.
		if ( ( isset( $data['user_id'] ) && empty( $data['user_id'] ) ) || ( ! empty( $data['user_id'] ) && ! get_user_by( 'id', $data['user_id'] ) ) ) {
			// Translators: %s = Invalid user id.
			return new WP_Error( 'llms_rest_key_invalid_user_id', sprintf( __( '"%s" is not a valid user ID.', 'lifterlms' ), $data['user_id'] ) );
		}

		// Prevent blank/empty descriptions.
		if ( isset( $data['description'] ) && empty( $data['description'] ) ) {
			return new WP_Error( 'llms_rest_key_invalid_description', __( 'An API Description is required.', 'lifterlms' ) );
		}

		// Validate Permissions.
		if ( ! empty( $data['permissions'] ) && ! in_array( $data['permissions'], array_keys( $this->get_permissions() ), true ) ) {
			// Translators: %s = Invalid permission string.
			return new WP_Error( 'llms_rest_key_invalid_permissions', sprintf( __( '"%s" is not a valid permission.', 'lifterlms' ), $data['permissions'] ) );
		}

		return true;

	}

}
