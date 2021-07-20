<?php
/**
 * Handles data sanitization & validation for the LLMS_Form_Handler class
 *
 * @package LifterLMS/Classes
 *
 * @since 5.0.0
 * @version 5.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Form_Handler class.
 *
 * @since 5.0.0
 */
class LLMS_Form_Validator {

	/**
	 * Filters a list of fields down to only the required fields.
	 *
	 * @since 5.0.0
	 *
	 * @param array[] $fields Array of LifterLMS Form Field settings arrays.
	 * @return array[]
	 */
	public function get_required_fields( $fields ) {
		return array_values(
			array_filter(
				$fields,
				function( $field ) {
					return ! empty( $field['required'] );
				}
			)
		);
	}

	/**
	 * Sanitize a single field according to its type
	 *
	 * @since 5.0.0
	 *
	 * @param mixed $posted_value User-submitted (dirty) value.
	 * @param array $field        LifterLMS field settings.
	 * @return mixed
	 */
	public function sanitize_field( $posted_value, $field ) {

		$map = array(
			'email'    => 'sanitize_email',
			'number'   => array( $this, 'sanitize_field_number' ),
			'tel'      => array( $this, 'sanitize_field_tel' ),
			'textarea' => 'sanitize_textarea_field',
			'url'      => 'esc_url_raw',
		);

		$func = isset( $map[ $field['type'] ] ) ? $map[ $field['type'] ] : 'sanitize_text_field';

		// Turn the submitted value into array, so to unify sanitization of scalar and array posted values.
		$to_sanitize = is_array( $posted_value ) ? $posted_value : array( $posted_value );
		$sanitized   = array();

		foreach ( $to_sanitize as $value ) {
			$sanitized[] = trim( call_user_func( $func, $value ) );
		}

		return is_array( $posted_value ) ? $sanitized : $sanitized[0];

	}

	/**
	 * Sanitize a number field
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted (dirty) value.
	 * @return string
	 */
	protected function sanitize_field_number( $posted_value ) {
		return preg_replace( '/[^0-9.,]/', '', $posted_value );
	}

	/**
	 * Sanitize a telephone field
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted (dirty) value.
	 * @return string
	 */
	protected function sanitize_field_tel( $posted_value ) {
		return preg_replace( '/[^\s\#0-9\-\+\(\)\.]/', '', $posted_value );

	}

	/**
	 * Sanitize all user-submitted data according to field settings
	 *
	 * @since 5.0.0
	 *
	 * @param array   $posted_data User-submitted form data.
	 * @param array[] $fields      LifterLMS form fields settings.
	 * @return array
	 */
	public function sanitize_fields( $posted_data, $fields ) {

		foreach ( $fields as $field ) {

			if ( empty( $field['name'] ) || ! isset( $posted_data[ $field['name'] ] ) ) {
				continue;
			}

			$posted_data[ $field['name'] ] = $this->sanitize_field( $posted_data[ $field['name'] ], $field );

		}

		return $posted_data;

	}

	/**
	 * Validate a posted value
	 *
	 * @since 5.0.0
	 *
	 * @param mixed $posted_value Posted data.
	 * @param array $field        LifterLMS Form Field settings array.
	 * @return WP_Error|true
	 */
	public function validate_field( $posted_value, $field ) {

		// Validate field by type.
		$type_map = array(
			'email'  => array( $this, 'validate_field_email' ),
			'number' => array( $this, 'validate_field_number' ),
			'tel'    => array( $this, 'validate_field_tel' ),
			'url'    => array( $this, 'validate_field_url' ),
		);

		// Turn the submitted value into array, so to unify validation of scalar and array posted values.
		$to_validate = is_array( $posted_value ) ? $posted_value : array( $posted_value );

		foreach ( $to_validate as $value ) {

			$valid = isset( $type_map[ $field['type'] ] ) ? call_user_func( $type_map[ $field['type'] ], $value, $field ) : true;
			if ( is_wp_error( $valid ) ) { // Return as soon as a field is not valid.
				return $valid;
			}

			// HTML Attribute Validations.
			if ( ! empty( $field['attributes']['minlength'] ) ) {
				$valid = $this->validate_field_attribute_minlength( $value, $field['attributes']['minlength'], $field );
				if ( is_wp_error( $valid ) ) {
					return $valid;
				}
			}
		}

		// Perform special validations for special field types (scalar by their nature).
		$extra_map = array(
			'llms_voucher'     => array( $this, 'validate_field_voucher' ),
			'password_current' => array( $this, 'validate_field_current_password' ),
			'user_email'       => array( $this, 'validate_field_user_email' ),
			'user_login'       => array( $this, 'validate_field_user_login' ),
		);
		$valid     = isset( $extra_map[ $field['id'] ] ) ? call_user_func( $extra_map[ $field['id'] ], $posted_value ) : true;
		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		return true;

	}

	/**
	 * Validates the html input minlength attribute
	 *
	 * Used by the User Password field.
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted value.
	 * @param int    $minlength    The minimum string length as parsed from the field block.
	 * @param array  $field        LifterLMS Form Field settings array.
	 * @return WP_Error|boolean Returns `true` for a valid value, otherwise an error.
	 */
	protected function validate_field_attribute_minlength( $posted_value, $minlength, $field ) {

		if ( strlen( $posted_value ) < $minlength ) {
			return new WP_Error(
				'llms-form-field-invalid',
				sprintf(
					__( 'The %1$s must be at least %2$d characters in length.', 'lifterlms' ),
					isset( $field['label'] ) ? $field['label'] : $field['name'],
					$minlength
				)
			);
		}

		return true;

	}

	/**
	 * Validate an email field
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted (dirty) value.
	 * @return WP_Error|boolean Returns `true` for a valid submission, otherwise an error.
	 */
	protected function validate_field_email( $posted_value ) {

		if ( ! is_email( $posted_value ) ) {
			// Translators: %s user submitted value.
			return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The email address "%s" is not valid.', 'lifterlms' ), $posted_value ) );
		}

		return true;

	}

	/**
	 * Validate a number field
	 *
	 * Ensures the posted valued is numeric and, where applicable, ensures that the number falls
	 * within minimum and maximum value requirements.
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted (dirty) value.
	 * @param array  $field        The LLMS_Form_Field settings array.
	 * @return WP_Error|boolean Returns `true` for a valid submission, otherwise an error.
	 */
	protected function validate_field_number( $posted_value, $field ) {

		$temp_value = str_replace( ',', '', $posted_value );
		if ( ! is_numeric( $temp_value ) ) {
			// Translators: %1$s field label or name; %2$s = user submitted value.
			return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The %1$s "%2$s" is not valid number.', 'lifterlms' ), isset( $field['label'] ) ? $field['label'] : $field['name'], $posted_value ) );
		} elseif ( isset( $field['attributes'] ) ) {
			if ( ( ! empty( $field['attributes']['min'] ) || ( isset( $field['attributes']['min'] ) && '0' === $field['attributes']['min'] ) ) && $temp_value < $field['attributes']['min'] ) {
				// Translators: %1$s = field label or name; %2$s = user submitted value; %3$d = minimum allowed number.
				return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The %1$s "%2$s" must be greater than or equal to %3$d.', 'lifterlms' ), isset( $field['label'] ) ? $field['label'] : $field['name'], $posted_value, $field['attributes']['min'] ) );
			} elseif ( ( ! empty( $field['attributes']['max'] ) || ( isset( $field['attributes']['max'] ) && '0' === $field['attributes']['max'] ) ) && $temp_value > $field['attributes']['max'] ) {
				// Translators: %1$s = field label or name; %2$s = user submitted value; %3$d = maximum allowed number.
				return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The %1$s "%2$s" must be less than or equal to %3$d.', 'lifterlms' ), isset( $field['label'] ) ? $field['label'] : $field['name'], $posted_value, $field['attributes']['max'] ) );
			}
		}

		return true;

	}

	/**
	 * Validate a logged-in users current password
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted (dirty) value.
	 * @return WP_Error|boolean Returns `true` for a valid submission, otherwise an error.
	 */
	protected function validate_field_current_password( $posted_value ) {

		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'llms-form-field-invalid-no-user', __( 'You must be logged in to update your password.', 'lifterlms' ), $posted_value );
		}

		$user = wp_get_current_user();
		if ( ! wp_check_password( $posted_value, $user->user_pass ) ) {
			return new WP_Error( 'llms-form-field-invalid', __( 'The submitted password was not correct.', 'lifterlms' ), $posted_value );
		}

		return true;
	}

	/**
	 * Validate a telephone field
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted (dirty) value.
	 * @return WP_Error|boolean Returns `true` for a valid submission, otherwise an error.
	 */
	protected function validate_field_tel( $posted_value ) {

		if ( 0 < strlen( trim( preg_replace( '/[\s\#0-9\-\+\(\)\.]/', '', $posted_value ) ) ) ) {
			// Translators: %s = user submitted value.
			return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The phone number "%s" is not valid.', 'lifterlms' ), $posted_value ) );
		}

		return true;

	}

	/**
	 * Validate a url field
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted (dirty) value.
	 * @return WP_Error|boolean Returns `true` for a valid submission, otherwise an error.
	 */
	protected function validate_field_url( $posted_value ) {

		if ( ! filter_var( $posted_value, FILTER_VALIDATE_URL ) ) {
			// Translators: %s = user submitted value.
			return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The URL "%s" is not valid.', 'lifterlms' ), $posted_value ) );
		}

		return true;

	}

	/**
	 * Validate a user-email field
	 *
	 * User emails must be unique.
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted (dirty) value.
	 * @return WP_Error|boolean Returns `true` for a valid submission, otherwise an error.
	 */
	protected function validate_field_user_email( $posted_value ) {
		if ( email_exists( $posted_value ) ) {
			return new WP_Error( 'llms-form-field-not-unique', sprintf( __( 'An account with the email address "%s" already exists.', 'lifterlms' ), $posted_value ) );
		}

		return true;
	}

	/**
	 * Validate a user-login field
	 *
	 * Ensures that a username isn't found in the LifterLMS username blocklist, that it meets the default
	 * WP core username criteria and that the username doesn't already exist.
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted (dirty) value.
	 * @return WP_Error|boolean Returns `true` for a valid submission, otherwise an error.
	 */
	protected function validate_field_user_login( $posted_value ) {
		if ( in_array( $posted_value, llms_get_usernames_blocklist(), true ) || ! validate_username( $posted_value ) ) {
			return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The username "%s" is invalid, please try a different username.', 'lifterlms' ), $posted_value ), $posted_value );
		} elseif ( username_exists( $posted_value ) ) {
			return new WP_Error( 'llms-form-field-not-unique', sprintf( __( 'An account with the username "%s" already exists.', 'lifterlms' ), $posted_value ), $posted_value );
		}

		return true;
	}

	/**
	 * Validate a voucher field ensuring it's a valid and usable voucher code
	 *
	 * @since 5.0.0
	 *
	 * @param string $posted_value User-submitted (dirty) value.
	 * @return WP_Error|boolean Returns `true` for a valid submission, otherwise an error.
	 */
	protected function validate_field_voucher( $posted_value ) {

		$voucher = new LLMS_Voucher();
		$check   = $voucher->check_voucher( $posted_value );
		if ( is_wp_error( $check ) ) {
			return new WP_Error( 'llms-form-field-invalid', $check->get_error_message(), array( $posted_value, $check ) );
		}

		return true;

	}

	/**
	 * Validate submitted field values.
	 *
	 * @since 5.0.0
	 * @since 5.1.0 Don't validate form with no user input only if the form is not empty itself (e.g. contains only invisible fields).
	 *
	 * @param array   $posted_data Array of posted data.
	 * @param array[] $fields      Array of LifterLMS Form Fields.
	 * @return WP_Error|true
	 */
	public function validate_fields( $posted_data, $fields ) {

		if ( empty( $posted_data ) && ! empty( $fields ) ) {
			return new WP_Error( 'llms-form-no-input', __( 'Cannot validate a form with no user input.', 'lifterlms' ) );
		}

		$err      = new WP_Error();
		$err_data = array();
		foreach ( $fields as $field ) {

			if ( empty( $field['name'] ) || empty( $posted_data[ $field['name'] ] ) ) {
				continue;
			}

			$valid = $this->validate_field( $posted_data[ $field['name'] ], $field );
			if ( is_wp_error( $valid ) ) {
				$err->add( $valid->get_error_code(), $valid->get_error_message() );
				$err_data[ $field['name'] ] = $field;
			}
		}

		if ( $err->errors ) {
			$err->add_data( $err_data );
			return $err;
		}

		return true;

	}

	/**
	 * Ensure matching fields match one another.
	 *
	 * @since 5.0.0
	 *
	 * @param array   $posted_data Array of posted data.
	 * @param array[] $fields      Array of LifterLMS form fields.
	 * @return WP_Error|true
	 */
	public function validate_matching_fields( $posted_data, $fields ) {

		$err      = new WP_Error();
		$err_data = array();

		$matches = array();
		foreach ( $fields as $field ) {

			// Field doesn't have a match to check or it was already checked by it's match.
			if ( empty( $field['match'] ) || in_array( $field['id'], $matches, true ) ) {
				continue;
			}

			$field_name = isset( $field['label'] ) ? $field['label'] : $field['name'];

			$name        = $field['name'];
			$match_field = LLMS_Forms::instance()->get_field_by( $fields, 'id', $field['match'] );
			if ( ! $match_field ) {
				continue;
			}

			$match = $match_field['name'];

			$val   = isset( $posted_data[ $name ] ) ? $posted_data[ $name ] : '';
			$match = isset( $posted_data[ $match ] ) ? $posted_data[ $match ] : '';

			if ( $val !== $match ) {

				$match_name = isset( $match_field['label'] ) ? $match_field['label'] : $match_field['name'];
				$err->add( 'llms-form-field-not-matched', sprintf( __( '%1$s must match %2$s.', 'lifterlms' ), $field_name, $match_name ) );
				$err_data[] = array( $field, $match_field );

			}

			// Fields reference each other so we only need to check the pair one time.
			$matches[] = $match_field['id'];

		}

		if ( $err->errors ) {
			$err->add_data( $err_data, 'llms-form-field-not-matched' );
			return $err;
		}

		return true;

	}

	/**
	 * Ensure that all of the forms required fields are present in the submitted data.
	 *
	 * @since 5.0.0
	 *
	 * @param array   $posted_data User data (likely from $_POST).
	 * @param array[] $fields      Array of LifterLMS form fields.
	 * @return WP_Error|true
	 */
	public function validate_required_fields( $posted_data, $fields ) {

		// Ensure all required fields have been submitted.
		$err      = new WP_Error();
		$err_data = array();
		foreach ( $this->get_required_fields( $fields ) as $field ) {

			if ( empty( $posted_data[ $field['name'] ] ) ) {
				// Translators: %s = field label or name.
				$err->add( 'llms-form-missing-required', sprintf( __( '%s is a required field.', 'lifterlms' ), isset( $field['label'] ) ? $field['label'] : $field['name'] ) );
				$err_data[ $field['name'] ] = $field;
			}
		}

		if ( $err->errors ) {
			$err->add_data( $err_data, 'llms-form-missing-required' );
			return $err;
		}

		return true;

	}

}
