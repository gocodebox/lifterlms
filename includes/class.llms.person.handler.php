<?php
/**
 * User Handling for login and registration (mostly)
 *
 * @package LifterLMS/Classes
 *
 * @since 3.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Person_Handler class.
 *
 * @since 3.0.0
 * @since 3.35.0 Sanitize field data when filling field with user-submitted data.
 * @since 5.0.0 Private methods `LLMS_Person_Handler::fill_fields()` and `LLMS_Person_Handler::insert_data()` were removed.
 * @since 6.0.0 Removed deprecated items.
 *              - `LLMS_Person_Handler::register()` method
 *              - `LLMS_Person_Handler::sanitize_field() method`
 *              - `LLMS_Person_Handler::update()` method
 *              - `LLMS_Person_Handler::validate_fields()` method
 *              - `LLMS_Person_Handler::voucher_toggle_script()` method
 */
class LLMS_Person_Handler {

	/**
	 * Prefix for all user meta field keys
	 *
	 * @var string
	 */
	private static $meta_prefix = 'llms_';

	/**
	 * Prevents the hacky voucher script from being output multiple times
	 *
	 * @var boolean
	 */
	private static $voucher_script_output = false;

	/**
	 * Locate password fields from a given form location.
	 *
	 * @since 5.0.0
	 *
	 * @param string $location From location.
	 * @return false|array[]
	 */
	protected static function find_password_fields( $location ) {

		$forms = LLMS_Forms::instance();
		$all   = $forms->get_form_fields( $location );

		$pwd = $forms->get_field_by( (array) $all, 'id', 'password' );

		// If we don't have a password in the form return early.
		if ( ! $pwd ) {
			return false;
		}

		// Setup the return array.
		$fields = array( $pwd );

		// Add confirmation and strength meter if they exist.
		foreach ( array( 'password_confirm', 'llms-password-strength-meter' ) as $id ) {

			$field = $forms->get_field_by( $all, 'id', $id );
			if ( $field ) {

				// If we have a confirmation field ensure that the fields sit side by side.
				if ( 'password_confirm' === $id ) {

					$fields[0]['columns']         = 6;
					$fields[0]['last_column']     = false;
					$fields[0]['wrapper_classes'] = array();

					$field['columns']         = 6;
					$field['last_column']     = true;
					$field['wrapper_classes'] = array();

				}

				$fields[] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Generate a unique login based on the user's email address
	 *
	 * @since 3.0.0
	 * @since 3.19.4 Unknown.
	 *
	 * @param string $email User's email address.
	 * @return string
	 */
	public static function generate_username( $email ) {

		/**
		 * Allow custom username generation
		 *
		 * @since 3.0.0
		 *
		 * @param string $custom_username The custom-generated username. If the filter returns a truthy string it will be used in favor
		 *                                of the automatically generated username.
		 * @param string $email           User's email address.
		 */
		$custom_username = apply_filters( 'lifterlms_generate_username', null, $email );
		if ( $custom_username && is_string( $custom_username ) ) {
			return $custom_username;
		}

		$username      = sanitize_user( current( explode( '@', $email ) ), true );
		$orig_username = $username;
		$i             = 1;
		while ( username_exists( $username ) ) {

			$username = $orig_username . $i;
			++$i;

		}

		/**
		 * Modify an auto-generated username before it is used
		 *
		 * @since 3.0.0
		 *
		 * @param string $username The generated user name.
		 * @param string $email    User's email address which was used to generate the username.
		 */
		return apply_filters( 'lifterlms_generated_username', $username, $email );
	}

	/**
	 * Get the fields for the login form
	 *
	 * @since 3.0.0
	 * @since 3.0.4 Unknown.
	 * @since 5.0.0 Remove usage of the deprecated `lifterlms_registration_generate_username`.
	 *
	 * @param string $layout Form layout. Accepts "columns" (default) or "stacked".
	 * @return array[] An array of form field arrays.
	 */
	public static function get_login_fields( $layout = 'columns' ) {

		$usernames = LLMS_Forms::instance()->are_usernames_enabled();

		/**
		 * Customize the fields used to build the user login form
		 *
		 * @since 3.0.0
		 * @param array[] $fields An array of form field arrays.
		 */
		return apply_filters(
			'lifterlms_person_login_fields',
			array(
				array(
					'columns'     => ( 'columns' == $layout ) ? 6 : 12,
					'id'          => 'llms_login',
					'label'       => ! $usernames ? __( 'Email Address', 'lifterlms' ) : __( 'Username or Email Address', 'lifterlms' ),
					'last_column' => ( 'columns' == $layout ) ? false : true,
					'required'    => true,
					'type'        => ! $usernames ? 'email' : 'text',
				),
				array(
					'columns'     => ( 'columns' == $layout ) ? 6 : 12,
					'id'          => 'llms_password',
					'label'       => __( 'Password', 'lifterlms' ),
					'last_column' => ( 'columns' == $layout ) ? true : true,
					'required'    => true,
					'type'        => 'password',
				),
				array(
					'columns'     => ( 'columns' == $layout ) ? 3 : 12,
					'classes'     => 'llms-button-action',
					'id'          => 'llms_login_button',
					'value'       => __( 'Login', 'lifterlms' ),
					'last_column' => ( 'columns' == $layout ) ? false : true,
					'required'    => false,
					'type'        => 'submit',
				),
				array(
					'columns'     => ( 'columns' == $layout ) ? 6 : 6,
					'id'          => 'llms_remember',
					'label'       => __( 'Remember me', 'lifterlms' ),
					'last_column' => false,
					'required'    => false,
					'type'        => 'checkbox',
				),
				array(
					'columns'         => ( 'columns' == $layout ) ? 3 : 6,
					'id'              => 'llms_lost_password',
					'last_column'     => true,
					'description'     => '<a href="' . esc_url( wp_lostpassword_url() ) . '">' . __( 'Lost your password?', 'lifterlms' ) . '</a>',
					'type'            => 'html',
					'wrapper_classes' => 'align-right',
				),
			)
		);
	}

	/**
	 * Retrieve fields for password recovery
	 *
	 * Used to generate the form where a username/email is entered to start the password reset process.
	 *
	 * @since 3.8.0
	 * @since 5.0.0 Use LLMS_Forms::are_usernames_enabled() in favor of deprecated option "lifterlms_registration_generate_username".
	 *               Remove field values set to the default value for a form field.
	 *
	 * @return array[] An array of form field arrays.
	 */
	public static function get_lost_password_fields() {

		$usernames = LLMS_Forms::instance()->are_usernames_enabled();

		if ( ! $usernames ) {
			$message = __( 'Lost your password? Enter your email address and we will send you a link to reset it.', 'lifterlms' );
		} else {
			$message = __( 'Lost your password? Enter your username or email address and we will send you a link to reset it.', 'lifterlms' );
		}

		/**
		 * Filter the message displayed on the lost password form.
		 *
		 * @since Unknown.
		 *
		 * @param string $message The message displayed before the form.
		 */
		$message = apply_filters( 'lifterlms_lost_password_message', $message );

		/**
		 * Filter the form fields displayed for the lost password form.
		 *
		 * @since 3.8.0
		 *
		 * @param array[] $fields An array of form field arrays.
		 */
		return apply_filters(
			'lifterlms_lost_password_fields',
			array(
				array(
					'id'    => 'llms_lost_password_message',
					'type'  => 'html',
					'value' => $message,
				),
				array(
					'id'       => 'llms_login',
					'label'    => ! $usernames ? __( 'Email Address', 'lifterlms' ) : __( 'Username or Email Address', 'lifterlms' ),
					'required' => true,
					'type'     => ! $usernames ? 'email' : 'text',
				),
				array(
					'classes' => 'llms-button-action auto',
					'id'      => 'llms_lost_password_button',
					'value'   => __( 'Reset Password', 'lifterlms' ),
					'type'    => 'submit',
				),
			)
		);
	}

	/**
	 * Retrieve an array of password fields.
	 *
	 * This is only used on the password rest form as a fallback
	 * when no "custom" password fields can be found in either of the default
	 * checkout or registration forms.
	 *
	 * @since 3.7.0
	 * @since 5.0.0 Removed optional parameters
	 *
	 * @return array[]
	 */
	private static function get_password_fields() {

		$fields = array();

		$fields[] = array(
			'columns'     => 6,
			'classes'     => 'llms-password',
			'id'          => 'password',
			'label'       => __( 'Password', 'lifterlms' ),
			'last_column' => false,
			'match'       => 'password_confirm',
			'required'    => true,
			'type'        => 'password',
		);
		$fields[] = array(
			'columns'  => 6,
			'classes'  => 'llms-password-confirm',
			'id'       => 'password_confirm',
			'label'    => __( 'Confirm Password', 'lifterlms' ),
			'match'    => 'password',
			'required' => true,
			'type'     => 'password',
		);

		$fields[] = array(
			'classes'      => 'llms-password-strength-meter',
			'description'  => __( 'A strong password is required. The password must be at least 6 characters in length. Consider adding letters, numbers, and symbols to increase the password strength.', 'lifterlms' ),
			'id'           => 'llms-password-strength-meter',
			'type'         => 'html',
			'min_length'   => 6,
			'min_strength' => 'strong',
		);

		return $fields;
	}

	/**
	 * Retrieve form fields used on the password reset form.
	 *
	 * This method will attempt to the "custom" password fields in the checkout form
	 * and then in the registration form. At least a password field must be found. If
	 * it cannot be found this function falls back to a set of default fields as defined
	 * in the LLMS_Person_Handler::get_password_fields() method.
	 *
	 * @since Unknown
	 * @since 5.0.0 Get fields from the checkout or registration forms before falling back to default fields.
	 *              Changed filter on return from "lifterlms_lost_password_fields" to "llms_password_reset_fields".
	 *
	 * @param string $key User password reset key, usually populated via $_GET vars.
	 * @param string $login User login (username), usually populated via $_GET vars.
	 * @return array[]
	 */
	public static function get_password_reset_fields( $key = '', $login = '' ) {

		$fields = array();
		foreach ( array( 'checkout', 'registration' ) as $location ) {
			$fields = self::find_password_fields( $location );
			if ( $fields ) {
				break;
			}
		}

		// Fallback if no custom fields are found.
		if ( ! $fields ) {
			$location = 'fallback';
			$fields   = self::get_password_fields();
		}

		// Add button.
		$fields[] = array(
			'classes' => 'llms-button-action auto',
			'id'      => 'llms_lost_password_button',
			'type'    => 'submit',
			'value'   => __( 'Reset Password', 'lifterlms' ),
		);

		// Add hidden fields.
		$fields[] = array(
			'id'    => 'llms_reset_key',
			'type'  => 'hidden',
			'value' => $key,
		);
		$fields[] = array(
			'id'    => 'llms_reset_login',
			'type'  => 'hidden',
			'value' => $login,
		);

		/**
		 * Filter password reset form fields.
		 *
		 * @since 5.0.0
		 *
		 * @param array[] $fields   Array of form field arrays.
		 * @param string  $key      User password reset key, usually populated via $_GET vars.
		 * @param string  $login    User login (username), usually populated via $_GET vars.
		 * @param string  $location Location where the fields were retrieved from. Either "checkout", "registration", or "fallback".
		 *                          Fallback denotes that no password field was located in either of the previous forms so a default
		 *                          set of fields is generated programmatically.
		 */
		return apply_filters( 'llms_password_reset_fields', $fields, $key, $login, $location );
	}

	/**
	 * Login a user
	 *
	 * @since 3.0.0
	 * @since 3.29.4 Unknown.
	 * @since 5.0.0 Removed email lookup logic since `wp_authenticate()` supports email addresses as `user_login` since WP 4.5.
	 *
	 * @param array $data {
	 *     User login information.
	 *
	 *     @type string $llms_login User email address or username.
	 *     @type string $llms_password User password.
	 *     @type string $llms_remember Whether to extend the cookie duration to keep the user logged in for a longer period.
	 * }
	 * @return WP_Error|int The WP_User ID on login success or an error object on failure.
	 */
	public static function login( $data ) {

		/**
		 * Run an action prior to user login.
		 *
		 * @since 3.0.0
		 *
		 * @param array $data {
		 *    User login credentials.
		 *
		 *    @type string $user_login User's username.
		 *    @type string $password User's password.
		 *    @type bool $remeber Whether to extend the cookie duration to keep the user logged in for a longer period.
		 * }
		 */
		do_action( 'lifterlms_before_user_login', $data );

		/**
		 * Filter user submitted login data prior to data validation.
		 *
		 * @since 3.0.0
		 *
		 * @param array $data {
		 *    User login credentials.
		 *
		 *    @type string $user_login User's username.
		 *    @type string $password User's password.
		 *    @type bool $remeber Whether to extend the cookie duration to keep the user logged in for a longer period.
		 * }
		 */
		$data = apply_filters( 'lifterlms_user_login_data', $data );

		// Validate the fields & allow custom validation to occur.
		$valid = self::validate_login_fields( $data );

		// If errors found, return them.
		if ( is_wp_error( $valid ) ) {

			/**
			 * Filters the errors found during a LifterLMS user login attempt
			 *
			 * @since Unknown
			 *
			 * @param WP_Error       $valid  Error object containing information about the login error.
			 * @param array          $data   User submitted login form data.
			 * @param WP_Error|false $signon The original WP Error object returned by `wp_signon()` or false if the error
			 *                               is encountered prior to the signon attempt.
			 */
			return apply_filters( 'lifterlms_user_login_errors', $valid, $data, false );

		}

		$creds = array(
			'user_login'    => wp_unslash( $data['llms_login'] ), // Unslash ensures that an email address with an apostrophe is unescaped for lookups.
			'user_password' => $data['llms_password'],
			'remember'      => isset( $data['llms_remember'] ),
		);

		/**
		 * Filter a user's login credentials immediately prior to signing in.
		 *
		 * @since Unknown
		 *
		 * @param array $creds {
		 *    User login credentials.
		 *
		 *    @type string $user_login User's username.
		 *    @type string $password User's password.
		 *    @type bool $remeber Whether to extend the cookie duration to keep the user logged in for a longer period.
		 * }
		 */
		$creds  = apply_filters( 'lifterlms_login_credentials', $creds );
		$signon = wp_signon( $creds, is_ssl() );

		if ( is_wp_error( $signon ) ) {

			$err = new WP_Error( 'login-error', __( 'Could not find an account with the supplied email address and password combination.', 'lifterlms' ) );
			// This hook is documented in includes/class.llms.person.handler.php.
			return apply_filters( 'lifterlms_user_login_errors', $err, $data, $signon );

		}

		return $signon->ID;
	}

	/**
	 * Validate login form fields
	 *
	 * @since 5.0.0
	 *
	 * @param array $data Array of user-submitted data, usually from `$_POST`.
	 * @return WP_Error|true Returns an error object or `true` if the submission is valid.
	 */
	protected static function validate_login_fields( $data ) {

		$err = new WP_Error();

		$fields = self::get_login_fields();

		foreach ( $fields as $field ) {

			$name  = isset( $field['name'] ) ? $field['name'] : $field['id'];
			$label = isset( $field['label'] ) ? $field['label'] : $name;

			$field_type = isset( $field['type'] ) ? $field['type'] : '';
			$val        = isset( $data[ $name ] ) ? $data[ $name ] : '';

			// Ensure required fields are submitted.
			if ( ! empty( $field['required'] ) && empty( $val ) ) {

				$err->add( $field['id'], sprintf( __( '%s is a required field', 'lifterlms' ), $label ), 'required' );
				continue;

			}

			// Email fields must be emails.
			if ( 'email' === $field_type && ! is_email( $val ) ) {
				$err->add( $field['id'], sprintf( __( '%s must be a valid email address', 'lifterlms' ), $label ), 'invalid' );
			}
		}

		$valid = $err->has_errors() ? $err : true;

		/**
		 * Filters the validation result of user-submitted login data
		 *
		 * @since 4.21.0
		 *
		 * @param WP_Error|boolean $valid An error object containing validation errors or `true` if no validation errors found.
		 * @param array            $data  User submitted login data.
		 */
		return apply_filters( 'llms_after_user_login_data_validation', $valid, $data );
	}

	/**
	 * Retrieve an array of fields for a specific screen
	 *
	 * @since 3.0.0
	 * @since 3.7.0 Unknown.
	 * @deprecated 5.0.0 `LLMS_Person_Handler::get_available_fields()` is deprecated in favor of `LLMS_Forms::get_form_fields()`.
	 *
	 * @param string    $screen Name os the screen [account|checkout|registration].
	 * @param array|int $data   Array of data to fill fields with or a WP User ID.
	 * @return array
	 */
	public static function get_available_fields( $screen = 'registration', $data = array() ) {
		_deprecated_function( 'LLMS_Person_Handler::get_available_fields()', '5.0.0', 'LLMS_Forms::get_form_fields()' );
		return LLMS_Forms::instance()->get_form_fields( $screen );
	}
}
