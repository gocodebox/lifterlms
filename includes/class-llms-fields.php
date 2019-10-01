<?php
/**
 * Manage LifterLMS User, Purchase, and Post fields.
 *
 * @package  LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Fields class..
 *
 * @since [version]
 */
class LLMS_Fields {

	/**
	 * Array of registered fields.
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Singleton instance
	 *
	 * @var  null
	 */
	protected static $instance = null;

	/**
	 * Get Main Singleton Instance.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Fields
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	private function __construct() {

		foreach ( $this->get_core_fields() as $field ) {
			$this->add( $field );
		}

	}

	/**
	 * Add a field.
	 *
	 * @since [version]
	 *
	 * @see {Reference}
	 * @link {URL}
	 *
	 * @param array $field {
	 *     Hash of field data.
	 *
	 *     @type string $id (Required) Field ID. Must be unique within the group. Doubles as the HTML `id` attribute of the field.
	 *     @type string $label Field label.
	 *     @type string $name HTML `name` attribute of the field. Defaults to the `$id` property with hyphens replaced with underscores and "_llms_" prepended to the value.
	 *     @type string $value The html `value` attribute of the element. Empty for most fields. Defaults to `yes` for checkbox fields.
	 *     @type string $placeholder HTML `placeholder` attribute.
	 *     @type string $description Description text displayed below the field.
	 *     @type array $options Array of options to use for select, radio, and checkbox group fields.
	 *     @type bool $editable Determines if the field is editable by the user. When `true` the field automatically displays on the user's account edit page. Default `true`.
	 *     @type callable $sanitize_callback Callable function used to sanitize the user-submitted field value. Defaults uses `llms_filter_input()` with the `FILTER_SANITIZE_STRING` flag. Callbacks should return the field value.
	 *     @type callable $validate_callback Callable function used to validate the user-submitted field value. Callback should return `true` to indicate the field is value or a `WP_Error` to show client side or `false` to show a generic error message.
	 *     @type string $element_type HTML5 Form Element type. Accepts: "input", "button", "select", or "textarea". Defaults to "input".
	 *     @type string $input_type When $element_type is `input`, determines element's `input` property. Defaults to "text". Accepts values as defined by https://www.w3.org/TR/html52/sec-forms.html#element-attrdef-input-type.
	 *     @type string[] $classes Array of CSS classes to add to the element.
	 *     @type string[] $wrapper_classes Array of CSS classes to add to the parent wrapper element.
	 *     @type array $attributes An associative array of additional html attributes to add to the $element tag. Array key is the attribute name and the array value is the attribute value.
	 *     @type string $storage Determines where field data is stored. Defaults to "user_meta". Accepts "user_meta" and "users". Pass "none" to disable field storage.
	 *     @type string $storage_key Determines the meta_key name (or column name when $storage is `users`) used to store the value in the database. Defaults to the value of the `$name` argument.
	 * }
	 */
	public function add( $field = array() ) {

		if ( empty( $field['id'] ) ) {
			return new WP_Error( 'llms_fields_add_missing_id', __( 'Field "id" is required to register a field.', 'lifterlms' ) );
		}

		$this->fields[] = apply_filters( 'llms_register_field', $this->setup( $field ), $field );
		return true;

	}

	public function get_defaults() {
		return apply_filters( 'llms_get_field_defaults', array(
			'editable' => true,
			'disabled' => false,
			'required' => false,
			'sanitize_callback' => array( $this, 'sanitize_field_value' ),
			'validate_callback' => array( $this, 'validate_field_value' ),
			'element_type' => 'input',
			'input_type' => 'text',
			'storage' => 'user_meta',
		) );
	}

	public function setup( $field ) {

		$field = wp_parse_args( $field, $this->get_defaults() );

		// Field name defaults to a modified ID.
		if ( empty( $field['name'] ) ) {

			$field['name'] = str_replace( '-', '_', $field['id'] );

			// And automatically add the `_llms_` prefix if it doesn't exist.
			$field['name'] = 0 === strpos( '_llms_', $field['name'] ) ? $field['name'] : '_llms_' . $field['name'];

		}

		// If storage is not disabled add some storage_key defaults.
		if ( 'none' !== $field['storage'] ) {

			// Add storage key if it's not set.
			if ( empty( $field['storage_key'] ) ) {
				$field['storage_key'] = $field['name'];
			}

			// Remove prefix from storage key when storing on the user table directly.
			if ( 'users' === $field['storage_key'] ) {
				$field['storage_key'] = str_replace( '_llms_', '', $field['storage_key'] );
			}

		}

		// Set default field value for checkboxes if none is supplied.
		if ( 'input' === $field['element_type'] && 'checkbox' === $field['input_type'] && empty( $field['value'] ) ) {
			$field['value'] = 'yes';
		}

		return $field;

	}

	public function remove( $field_id ) {

		if ( $this->get( $field_id ) ) {
			unset( $this->fields[ $field_id ] );
		}

		return false;

	}

	public function get( $field_id ) {

		if ( array_key_exists( $this->fields, $field_id ) ) {
			return $this->fields[ $field_id ];
		}

		return false;

	}

	public function get_all() {
		return apply_filters( 'llms_get_registered_fields', $this->fields );
	}

	protected function get_core_fields() {

		$fields = array();

		$fields[] = array(
			'id' => 'user_login',
			'label' => __( 'Username', 'lifterlms' ),
			'editable' => false,
			'storage' => 'users',
			'sanitize_callback' => 'sanitize_user',
			'validate_callback' => array( $this, 'validate_username' ),
		);

		$fields[] = array(
			'id' => 'email_address',
			'label' => __( 'Email Address', 'lifterlms' ),
			'input_type' => 'email',
			'storage' => 'users',
			'storage_key' => 'user_email',
			'sanitize_callback' => 'sanitize_email',
			'validate_callback' => array( $this, 'validate_email' ),
		);

		$fields[] = array(
			'id' => 'email_address_confirm',
			'label' => __( 'Confirm Email Address', 'lifterlms' ),
			'input_type' => 'email',
			'storage' => 'none',
		);

		$fields[] = array(
			'id' => 'llms_first_name',
			'label' => __( 'First Name', 'lifterlms' ),
		);

		$fields[] = array(
			'id' => 'llms_last_name',
			'label' => __( 'Last Name', 'lifterlms' ),
		);

		$fields[] = array(
			'id' => 'llms_billing_address_1',
			'label' => __( 'Street Address', 'lifterlms' ),
		);

		$fields[] = array(
			'id' => 'llms_billing_address_1',
			'label' => __( 'Street Address', 'lifterlms' ),
		);

		$fields[] = array(
			'id' => 'llms_billing_address_2',
			'label' => __( 'Apartment, suite, or unit', 'lifterlms' ),
		);

		$fields[] = array(
			'id' => 'llms_billing_city',
			'label' => __( 'City', 'lifterlms' ),
		);

		$fields[] = array(
			'id' => 'llms_billing_state',
			'label' => __( 'State', 'lifterlms' ),
		);

		$fields[] = array(
			'id' => 'llms_billing_zip',
			'label' => __( 'Zip Code', 'lifterlms' ),
		);

		$fields[] = array(
			'id' => 'llms_billing_country',
			'label' => __( 'Country', 'lifterlms' ),
			'options' => get_lifterlms_countries(),
		),

		$fields[] = array(
			'id' => 'llms_phone',
			'label' => __( 'Phone Number', 'lifterlms' ),
			'input_type' => 'tel',
			'placeholder' => _x( '(123) 456 - 7890', 'Phone Number Placeholder', 'lifterlms' ),
		);

		return $fields;

	}

	/**
	 * Validate a user-submitted email address.
	 *
	 * @since [version]
	 *
	 * @param string $email User-submitted email address.
	 * @param array $field LifterLMS Field data.
	 * @return true|WP_Error
	 */
	public function validate_email( $email, $field ) {

		if ( ! is_email( $email ) ) {

			return new WP_Error( sprintf( 'llms_invalid_field_%s', $field['id'] ), sprintf( __( 'The email "%s" is invalid, please try a different email address.', 'lifterlms' ), $email ) );

		} elseif ( email_exists( $email ) ) {

			return new WP_Error( sprintf( 'llms_invalid_field_%s_exists', $field['id'] ), __( 'An account with the email "%s" already exists.', 'lifterlms' ), $email ) );

		}

		return true;

	}

	/**
	 * Validate a user-submitted username.
	 *
	 * @since [version]
	 *
	 * @param string $username User-submitted username.
	 * @param array $field LifterLMS Field data.
	 * @return true|WP_Error
	 */
	public function validate_username( $username, $field ) {

		// Blacklist usernames for security purposes.
		$banned_usernames = apply_filters( 'llms_usernames_blacklist', array( 'admin', 'test', 'administrator', 'password', 'testing' ) );

		if ( in_array( $username, $banned_usernames ) || ! validate_username( $username ) ) {

			return new WP_Error( sprintf( 'llms_invalid_field_%s', $field['id'] ), sprintf( __( 'The username "%s" is invalid, please try a different username.', 'lifterlms' ), $username ) );

		} elseif ( username_exists( $username ) ) {

			return new WP_Error( sprintf( 'llms_invalid_field_%s_exists', $field['id'] ), __( 'An account with the username "%s" already exists.', 'lifterlms' ), $username ) );

		}

		return true;

	}

}
