<?php
/**
 * Handle LifterLMS Form submissions.
 *
 * @package  LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Form_Handler class..
 *
 * @since [version]
 */
class LLMS_Form_Handler {

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
	 * @return LLMS_Form_Handler
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

		add_action( 'lifterlms_before_user_update', array( $this, 'maybe_modify_edit_account_field_settings' ), 10, 3 );

	}

	/**
	 * Ensure matching fields match one another.
	 *
	 * @since [version]
	 *
	 * @param array   $posted_data Array of posted data.
	 * @param array[] $fields      Array of LifterLMS Form Fields.
	 * @return WP_Error|true
	 */
	protected function check_matching_fields( $posted_data, $fields ) {

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
	 * Callback function for array_filter() used in `$this->get_required_fields()`
	 *
	 * @since [version]
	 *
	 * @param array $field LifterLMS Form Field settings.
	 * @return bool `true` if the field is required, `false` otherwise.
	 */
	protected function filter_required_fields( $field ) {

		return ! empty( $field['required'] );

	}

	/**
	 * Filters a list of fields down to only the required fields.
	 *
	 * @since [version]
	 *
	 * @param array[] $fields Array of LifterLMS Form Field settings arrays.
	 * @return array[]
	 */
	protected function get_required_fields( $fields ) {

		return array_values( array_filter( $fields, array( $this, 'filter_required_fields' ) ) );

	}

	/**
	 * Insert user data into the database.
	 *
	 * @since [version]
	 *
	 * @param string  $action Type of insert action. Either "registration" for a new user or "update" for an existing one.
	 * @param array   $posted_data User-submitted form data.
	 * @param array[] $fields List of LifterLMS Form fields for the form.
	 * @return WP_Error|int Error on failure or WP_User ID on success.
	 */
	protected function insert( $action, $posted_data, $fields ) {

		$func     = 'registration' === $action ? 'wp_insert_user' : 'wp_update_user';
		$prepared = $this->prepare_data_for_insert( $posted_data, $fields, $action );

		$user_id = $func( $prepared['users'] );
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		foreach ( $prepared['usermeta'] as $key => $val ) {
			update_user_meta( $user_id, $key, $val );
		}

		return $user_id;

	}

	/**
	 * Modify LifterLMS Fields prior to performing submit handler validations.
	 *
	 * @since [version]
	 *
	 * @param array   &$posted_data User submitted form data (passed by reference).
	 * @param string  $location Form location ID.
	 * @param array[] &$fields Array of LifterLMS Form Fields (passed by reference).
	 * @return void
	 */
	public function maybe_modify_edit_account_field_settings( &$posted_data, $location, &$fields ) {

		if ( 'account' !== $location ) {
			return;
		}

		/**
		 * If email address and passwords aren't submitted we can mark them as "optional" fields.
		 *
		 * These fields are dynamically toggled and disabled if they're not modified.
		 */
		foreach ( array( 'email_address', 'password', 'password_current' ) as $field_id ) {

			// If the field exists and it's not included (or empty) in the posted data.
			$index = LLMS_Forms::instance()->get_field_by( $fields, 'id', $field_id, 'index' );
			if ( false !== $index && empty( $posted_data[ $fields[ $index ]['name'] ] ) ) {

				// Remove the field so we don't accidentally save an empty value later.
				unset( $posted_data[ $fields[ $index ]['name'] ] );

				// Mark the field as optional (for validation purposes).
				$fields[ $index ]['required'] = false;

				// Check if there's a confirm field and do the same.
				$con_index = LLMS_Forms::instance()->get_field_by( $fields, 'id', "{$field_id}_confirm", 'index' );
				if ( false !== $con_index && empty( $posted_data[ $fields[ $con_index ]['name'] ] ) ) {
					unset( $posted_data[ $fields[ $con_index ]['name'] ] );
					$fields[ $con_index ]['required'] = false;
				}
			}
		}

	}

	/**
	 * Prepares user-submitted data for insertion into the database.
	 *
	 * @since [version]
	 *
	 * @param array   $posted_data Sanitized & validated user-submitted form data.
	 * @param array[] $fields LifterLMS form fields list.
	 * @param string  $action Insert action, either "registration" for new users or "update" for existing, logged-in users.
	 * @return array
	 */
	protected function prepare_data_for_insert( $posted_data, $fields, $action ) {

		$forms = LLMS_Forms::instance();

		$prepared = array();

		foreach ( $posted_data as $name => $value ) {

			$field = $forms->get_field_by( $fields, 'name', $name );
			if ( ! $field || empty( $field['data_store_key'] ) ) {
				continue;
			}

			if ( ! isset( $prepared[ $field['data_store'] ] ) ) {
				$prepared[ $field['data_store'] ] = array();
			}
			$prepared[ $field['data_store'] ][ $field['data_store_key'] ] = $value;

		}

		if ( 'registration' === $action ) {

			$defaults = array(
				'role'                 => 'student',
				'show_admin_bar_front' => false,
			);

			// Add a username if we don't have a user_login field.
			if ( empty( $prepared['users']['user_login'] ) ) {
				$defaults['user_login'] = LLMS_Person_Handler::generate_username( $posted_data['email_address'] );
			}

			// Add a password if we don't have a password field.
			if ( empty( $prepared['users']['user_pass'] ) ) {
				$defaults['user_pass'] = wp_generate_password( 32, true, true );
			}

			$prepared['users'] = wp_parse_args( $prepared['users'], $defaults );

		} elseif ( 'update' === $action ) {

			$prepared['users']['ID'] = get_current_user_id();

		}

		// Record an IP Address.
		$prepared['usermeta']['llms_ip_address'] = llms_get_ip_address();

		// If terms have been agreed to, record a time stamp for the agreement.
		if ( isset( $posted_data['llms_agress_to_terms'] ) ) {
			$prepared['usermeta']['llms_agress_to_terms'] = current_time( 'mysql' );
		}

		/**
		 * Filter data added to the wp_users data via `wp_insert_user()` or `wp_update_user()`.
		 *
		 * The dynamic portion of this hook, `$action`, can be either "registration" or "update".
		 *
		 * @since 3.0.0
		 * @since [version] Moved from `LLMS_Person_Handler::insert_data()`.
		 *
		 * @param array $user_data Array of user data.
		 * @param array $posted_data Array of user-submitted data.
		 * @param string $action Submission action, either "registration" or "update".
		 */
		$prepared['users'] = apply_filters( "lifterlms_user_${action}_insert_user", $prepared['users'], $posted_data, $action );

		/**
		 * Filter meta data to be added for the user.
		 *
		 * @since 3.0.0
		 * @since [version] Moved from `LLMS_Person_Handler::insert_data()`.
		 *
		 * @param array $user_meta Array of user meta data.
		 * @param array $posted_data Array of user-submitted data.
		 * @param string $action Submission action, either "registration" or "update".
		 */
		$prepared['usermeta'] = apply_filters( "lifterlms_user_${action}_insert_user_meta", $prepared['usermeta'], $posted_data, $action );

		return $prepared;

	}

	/**
	 * Sanitize a single field according to its type.
	 *
	 * @since [version]
	 *
	 * @param mixed $posted_value User-submitted (dirty) value.
	 * @param array $field LifterLMS field settings.
	 * @return mixed
	 */
	protected function sanitize_field( $posted_value, $field ) {

		if ( isset( $field['sanitize'] ) && is_callable( $field['sanitize'] ) ) {
			return trim( call_user_func( $field['sanitize'], $posted_value ) );
		}

		switch ( $field['type'] ) {

			case 'email':
				$value = sanitize_email( $posted_value );
				break;

			case 'tel':
				$value = preg_replace( '/[^\s\#0-9\-\+\(\)\.]/', '', $posted_value );
				break;

			case 'number':
				$value = preg_replace( '/[^0-9.,]/', '', $posted_value );
				break;

			default:
				$value = sanitize_text_field( $posted_value );

		}

		return trim( $value );

	}

	/**
	 * Sanitize all user-submitted data according to field settings.
	 *
	 * @since [version]
	 *
	 * @param array   $posted_data User-submitted form data.
	 * @param array[] $fields LifterLMS form fields settings.
	 * @return array
	 */
	protected function sanitize_fields( $posted_data, $fields ) {

		foreach ( $fields as $field ) {

			if ( ! isset( $posted_data[ $field['name'] ] ) ) {
				continue;
			}

			$posted_data[ $field['name'] ] = $this->sanitize_field( $posted_data[ $field['name'] ], $field );

		}

		return $posted_data;

	}

	/**
	 * Form submission handler.
	 *
	 * @since [version]
	 *
	 * @param array  $posted_data User-submitted form data.
	 * @param string $location Form location ID.
	 * @param array  $args
	 * @return int|WP_Error WP_User ID on success, error object on failure.
	 */
	public function submit( $posted_data, $location, $args = array() ) {

		$fields = LLMS_Forms::instance()->get_form_fields( $location, $args );

		$action = get_current_user_id() ? 'update' : 'registration';

		// Form couldn't be located.
		if ( false === $fields ) {

			return $this->submit_error(
				// Translators: %s = form location ID.
				new WP_Error( 'llms-form-invalid-location', sprintf( __( 'The form location "%s" is invalid.', 'lifterlms' ), $location ), $args ),
				$posted_data,
				$action
			);

			// No logged in user, can't update.
		} elseif ( 'account' === $location && 'update' !== $action ) {

			return $this->submit_error(
				// Translators: %s = form location ID.
				new WP_Error( 'llms-form-no-user', __( 'You must be logged in to perform this action.', 'lifterlms' ), $args ),
				$posted_data,
				$action
			);

		}

		/**
		 * Run an action immediately prior to user registration or update.
		 *
		 * The dynamic portion of this hook, `$action`, can be either "registration" or "update".
		 *
		 * @since 3.0.0
		 * @since [version] Moved from `LLMS_Person_Handler::update()` & LLMS_Person_Handler::register().
		 *               Added parameters `$fields` and `$args`.
		 *               Triggered by `do_action_ref_array()` instead of `do_action()` allowing modification
		 *                 of `$posted_data` and `$fields` via hooks.
		 *
		 * @param array $posted_data Array of user-submitted data (passed by reference).
		 * @param string $location Form location.
		 * @param array[] $fields Array of LifterLMS Form Fields (passed by reference).
		 * @param array $args Additional arguments from the form retrieval function.
		 */
		do_action_ref_array( "lifterlms_before_user_${action}", array( &$posted_data, $location, &$fields, $args ) );

		// Check for all required fields.
		$required = $this->validate_required_fields( $posted_data, $fields );
		if ( is_wp_error( $required ) ) {
			return $this->submit_error( $required, $posted_data, $action );
		}

		// Sanitize.
		$posted_data = $this->sanitize_fields( wp_unslash( $posted_data ), $fields );

		$valid = $this->validate_fields( $posted_data, $fields );
		if ( is_wp_error( $valid ) ) {
			return $this->submit_error( $valid, $posted_data, $action );
		}

		// Validate matching fields.
		$matches = $this->check_matching_fields( $posted_data, $fields );
		if ( is_wp_error( $matches ) ) {
			return $this->submit_error( $matches, $posted_data, $action );
		}

		/**
		 * Filter the validity of the form submission.
		 *
		 * The dynamic portion of this hook, `$action`, can be either "registration" or "update".
		 *
		 * @since 3.0.0
		 * @since [version]
		 *
		 * @param WP_Error|true $valid Error object containing validation errors or true when the data is valid.
		 * @param array $posted_data Array of user-submitted data.
		 * @param string $location Form location.
		 * @param array $args Additional arguments passed to the form submission handler.
		 */
		$valid = apply_filters( "lifterlms_user_${action}_data", true, $posted_data, $location, $args );
		if ( is_wp_error( $valid ) ) {
			return $this->submit_error( $valid, $posted_data, $action );
		}

		/**
		 * Run an action immediately after user registration/update fields have been validated.
		 *
		 * The dynamic portion of this hook, `$action`, can be either "registration" or "update".
		 *
		 * @since 3.0.0
		 * @since [version] Moved from `LLMS_Person_Handler::update()` & LLMS_Person_Handler::register().
		 *               Added parameters `$fields` and `$args`.
		 *
		 * @param array $posted_data Array of user-submitted data.
		 * @param string $location Form location.
		 * @param array[] $fields Array of LifterLMS Form Fields
		 * @param array $args Additional arguments from the form retrieval function.
		 */
		do_action( "lifterlms_user_${action}_after_validation", $posted_data, $location, $fields, $args );

		$user_id = $this->insert( $action, $posted_data, $fields );
		if ( is_wp_error( $user_id ) ) {
			return $this->submit_error( $user_id, $posted_data, $action );
		}

		if ( 'registration' === $action ) {

			/**
			 * Deprecated user creation hook.
			 *
			 * @since Unknown.
			 * @deprecated [version]
			 *
			 * @param int $user_id WP_User ID of the newly created user.
			 * @param array $posted_data Array of user-submitted data.
			 * @param string $location Form location.
			 */
			do_action( 'lifterlms_created_person', $user_id, $posted_data, $location );

			/**
			 * Fire an action after a user has been registered.
			 *
			 * @since 3.0.0
			 * @since [version] Moved from `LLMS_Person_Handler::register()`.
			 *
			 * @param int $user_id WP_User ID of the user.
			 * @param array $posted_data Array of user submitted data.
			 * @param string $location Form location.
			 */
			do_action( 'lifterlms_user_registered', $user_id, $posted_data, $location );

		} elseif ( 'update' === $action ) {

			/**
			 * Fire an action after a user has been updated.
			 *
			 * @since 3.0.0
			 * @since [version] Moved from `LLMS_Person_Handler::update()`.
			 *
			 * @param int $user_id WP_User ID of the user.
			 * @param array $posted_data Array of user submitted data.
			 * @param string $location Form location.
			 */
			do_action( 'lifterlms_user_updated', $user_id, $posted_data, $location );

		}

		return $user_id;

	}

	/**
	 * Ensure all errors objects encountered during form submission are filterable.
	 *
	 * @since [version]
	 *
	 * @param WP_Error $error Error object.
	 * @param array    $posted_data User-submitted form data.
	 * @param string   $action Form action, either "registration" or "update".
	 * @return WP_Error
	 */
	protected function submit_error( $error, $posted_data, $action ) {

		/**
		 * Filter the error return when the insert/update fails.
		 *
		 * @since 3.0.0
		 * @since [version] Moved from `LLMS_Person_Handler::insert_data()`.
		 *
		 * @param WP_Error $error Error object.
		 * @param array $posted_data Array of user-submitted data.
		 * @param string $action Submission action, either "registration" or "update"!
		 */
		return apply_filters( "lifterlms_user_${action}_failure", $error, $posted_data, $action );

	}

	/**
	 * Validate a posted value.
	 *
	 * @since [version]
	 *
	 * @param mixed $posted_value Posted data.
	 * @param array $field LifterLMS Form Field settings array.
	 * @return WP_Error|true
	 */
	protected function validate_field( $posted_value, $field ) {

		if ( isset( $field['validate'] ) && is_callable( $field['validate'] ) ) {
			return trim( call_user_func( $field['validate'], $posted_value ) );
		}

		// Validate field by field type.
		if ( ! empty( $field['type'] ) ) {

			switch ( $field['type'] ) {

				case 'email':
					if ( ! is_email( $posted_value ) ) {
						// Translators: %s user submitted value.
						return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The email address "%s" is not valid.', 'lifterlms' ), $posted_value ) );
					}
					break;

				case 'tel':
					if ( 0 < strlen( trim( preg_replace( '/[\s\#0-9\-\+\(\)\.]/', '', $posted_value ) ) ) ) {
						// Translators: %s user submitted value.
						return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The phone number "%s" is not valid.', 'lifterlms' ), $posted_value ) );
					}
					break;

				case 'number':
					$temp_value = str_replace( ',', '', $posted_value );
					if ( ! is_numeric( $temp_value ) ) {
						// Translators: %1$s field label or name; %2$s = user submitted value.
						return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The %1$s "%2$s" is not valid number.', 'lifterlms' ), isset( $field['label'] ) ? $field['label'] : $field['name'], $posted_value ) );
					} elseif ( isset( $field['attributes'] ) ) {
						if ( isset( $field['attributes']['min'] ) && $temp_value < $field['attributes']['min'] ) {
							// Translators: %1$s field label or name; %2$s = user submitted value; %3$d = minimum allowed number.
							return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The %1$s "%2$s" must be greater than or equal to %3$d.', 'lifterlms' ), isset( $field['label'] ) ? $field['label'] : $field['name'], $posted_value, $field['attributes']['min'] ) );
						} elseif ( isset( $field['attributes']['max'] ) && $temp_value > $field['attributes']['max'] ) {
							// Translators: %1$s field label or name; %2$s = user submitted value; %3$d = maximum allowed number.
							return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The %1$s "%2$s" must be less than or equal to %3$d.', 'lifterlms' ), isset( $field['label'] ) ? $field['label'] : $field['name'], $posted_value, $field['attributes']['max'] ) );
						}
					}
					break;

				// case 'password':
					// @todo check password min length
					// break;

			}
		}

		// Perform special validations for special field types.
		if ( ! empty( $field['id'] ) ) {

			switch ( $field['id'] ) {

				case 'llms_voucher':
					$voucher = new LLMS_Voucher();
					$check   = $voucher->check_voucher( $posted_value );
					if ( is_wp_error( $check ) ) {
						return new WP_Error( 'llms-form-field-invalid', $check->get_error_message(), array( $posted_value, $check ) );
					}
					break;

				case 'password_current':
					if ( ! is_user_logged_in() ) {
						return new WP_Error( 'llms-form-field-invalid-no-user', __( 'You must be logged in to update your password.', 'lifterlms' ), $posted_value );
					}
					$user = wp_get_current_user();
					if ( ! wp_check_password( $posted_value, $user->user_pass ) ) {
						return new WP_Error( 'llms-form-field-invalid', __( 'The submitted password was not correct.', 'lifterlms' ), $posted_value );
					}
					break;

				case 'user_email':
					if ( email_exists( $posted_value ) ) {
						return new WP_Error( 'llms-form-field-not-unique', sprintf( __( 'An account with the email address "%s" already exists.', 'lifterlms' ), $posted_value ) );
					}
					break;

				case 'user_login':
					if ( in_array( $posted_value, llms_get_usernames_blacklist(), true ) || ! validate_username( $posted_value ) ) {
						return new WP_Error( 'llms-form-field-invalid', sprintf( __( 'The username "%s" is invalid, please try a different username.', 'lifterlms' ), $posted_value ), $posted_value );
					} elseif ( username_exists( $posted_value ) ) {
						return new WP_Error( 'llms-form-field-not-unique', sprintf( __( 'An account with the username "%s" already exists.', 'lifterlms' ), $posted_value ), $posted_value );
					}
					break;

			}
		}

		return true;

	}


	/**
	 * Validate submitted field values.
	 *
	 * @since [version]
	 *
	 * @param array   $posted_data Array of posted data.
	 * @param array[] $fields Array of LifterLMS Form Fields.
	 * @return WP_Error|true
	 */
	protected function validate_fields( $posted_data, $fields ) {

		$err      = new WP_Error();
		$err_data = array();
		foreach ( $fields as $field ) {

			if ( empty( $posted_data[ $field['name'] ] ) ) {
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
	 * Ensure that all of the forms required fields are present in the submitted data.
	 *
	 * @since [version]
	 *
	 * @param array   $posted_data User data (likely from $_POST).
	 * @param array[] $fields Array of LifterLMS form fields.
	 * @return WP_Error|true
	 */
	protected function validate_required_fields( $posted_data, $fields ) {

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
