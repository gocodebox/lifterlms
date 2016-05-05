<?php
/**
 * Add Custom User Fields to user admin panel screens
 *
 * Applies to edit-user.php & profile.php
 * Fields are **not** available on a user creation screen
 *
 * @author LifterLMS
 * @project LifterLMS
 *
 * @since  2.7.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_User_Custom_Fields {

	private $fields = array();

	/**
	 * Constructor
	 *
	 * @since  2.7.0
	 */
	public function __construct() {

		// output custom fields on edit screens
		add_action( 'show_user_profile', array( $this, 'output_custom_fields' ), 10, 1 );
		add_action( 'edit_user_profile', array( $this, 'output_custom_fields' ), 10, 1 );

		// allow errors to be output before saving field data
		// save the data if no errors are encountered
		add_action( 'user_profile_update_errors', array( $this, 'save_custom_fields' ), 10, 3 );

	}


	/**
	 * Retrieve an associative array of custom fields and custom field data
	 *
	 * @return array
	 *
	 * @since  2.7.0
	 */
	public function get_fields() {

		$fields = apply_filters( 'lifterlms_get_user_custom_fields', array(

			'llms_billing_address_1' => array(
				'description' => '',
				'label' => __( 'Billing Address 1', 'lifterlms' ),
				'required' => false,
				'type'  => 'text',
			),

			'llms_billing_address_2' => array(
				'description' => '',
				'label' => __( 'Billing Address 2', 'lifterlms' ),
				'required' => false,
				'type'  => 'text',
			),

			'llms_billing_city' => array(
				'description' => '',
				'label' => __( 'Billing City', 'lifterlms' ),
				'required' => false,
				'type'  => 'text',
			),

			'llms_billing_state' => array(
				'description' => '',
				'label' => __( 'Billing State', 'lifterlms' ),
				'required' => false,
				'type'  => 'text',
			),

			'llms_billing_zip' => array(
				'description' => '',
				'label' => __( 'Billing Zip Code', 'lifterlms' ),
				'required' => false,
				'type'  => 'text',
			),

			'llms_billing_country' => array(
				'description' => '',
				'label' => __( 'Billing Country', 'lifterlms' ),
				'required' => false,
				'type'  => 'text',
			),

			'llms_phone' => array(
				'description' => '',
				'label' => __( 'Phone', 'lifterlms' ),
				'required' => false,
				'type'  => 'text',
			),

		) );

		$this->fields = $fields;

		return $this->fields;

	}


	/**
	 * Load usermeta data into the array of fields retreived from $this->get_fields
	 * meta data is added to the array under the key "value" for each field
	 * if no data is found for a particular field the value is still added as an empty string
	 *
	 * @param  mixed  $user   Instance of WP_User or WP User ID
	 * @return array
	 *
	 * @since  2.7.0
	 */
	public function get_fields_with_data( $user ) {

		if ( is_numeric( $user ) ) {

			$user = new WP_User( $user );

		}

		$this->get_fields();

		foreach ( $this->fields as $field => $data ) {

			$this->fields[ $field ]['value'] = apply_filters( 'lifterlms_get_user_custom_field_value_' . $field, $user->get( $field ), $user, $field );

		}

		return $this->fields;

	}


	/**
	 * Output custom field data fields as HTML inputs
	 *
	 * @param  mixed  $user   Instance of WP_User or WP User ID
	 * @return void
	 *
	 * @since  2.7.0
	 */
	public function output_custom_fields( $user ) {

		$this->get_fields_with_data( $user );

		llms_get_template( 'admin/user-edit.php', array( 'fields' => $this->fields ) );

	}


	/**
	 * Save custom field data on profile form submission
	 *
	 * @param  obj    &$errors  Instance of WP_Error
	 * @param  bool   $update   true if updating a profile, false if a new user
	 * @param  obj    $user     Instace of WP_User for the user being updated
	 *
	 * @return void
	 *
	 * @since 2.7.0
	 */
	public function save_custom_fields( &$errors, $update, $user ) {

		// if update is not true, a new user is being created and we should skip our saving
		if ( ! $update ) {
			return;
		}

		$this->get_fields();

		$error = $this->validate_fields( $user );

		if ( $error ) {

			$errors->add( '', $error, '' );

			return;

		}

		// save data
		foreach ( $this->fields as $field => $data ) {

			update_user_meta( $user->ID, $field, sanitize_text_field( apply_filters( 'lifterlms_save_custom_user_field_' . $field, $_POST[ $field ], $user, $field ) ) );

		}

	}


	/**
	 * Validate custom fields
	 * By default only checks for valid as core fields don't have any special validation
	 * If adding custom fields, hook into the action run after required validation
	 * to add special validation rules for your field
	 *
	 * @param  mixed  $user   Instance of WP_User or WP User ID
	 * @return mixed          false if no validation errors, string (the error message) if validation errors occurred
	 */
	public function validate_fields( $user ) {

		// ensure there's no missing required fields
		foreach ( $this->fields as $field => $data ) {

			// return an error message for empty required fields
			if ( empty( $_POST[ $field ] ) && $data['required'] ) {

				return sprintf( __( 'Required field "%s" is missing.', 'lifterlms' ), $data['label'] );

			} // allow additional validation to be run on the custom field
			else {

				/**
				 * Run custom validation against the field
				 * If filter function returns a truthy, validation will stop, fields will not be saved,
				 * and an error message will be displayed on screen
				 *
				 * This should return false or a string which will be used as the error message
				 *
				 * @since  2.7.0
				 */
				$error_msg = apply_filters( 'lifterlms_validate_custom_user_field_' . $field, false, $field, $user );

				if ( $error_msg ) {

					return $error_msg;

				}

			}

		}

		return false;

	}

}

return new LLMS_Admin_User_Custom_Fields;
