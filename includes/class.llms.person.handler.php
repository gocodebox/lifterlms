<?php
/**
 * User Handling for login and registration (mostly)
 *
 * @since 3.0.0
 * @version 3.35.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Person_Handler class.
 *
 * @since 3.0.0
 * @since 3.35.0 Sanitize field data when filling field with user-submitted data.
 */
class LLMS_Person_Handler {

	/**
	 * Prefix for all user meta field keys
	 *
	 * @var  string
	 * @since  3.0.0
	 * @version  3.0.0
	 */
	private static $meta_prefix = 'llms_';

	/**
	 * Prevents the hacky voucher script from being output multiple times
	 *
	 * @var      boolean
	 * @since    3.0.2
	 * @version  3.0.2
	 */
	private static $voucher_script_output = false;

	/**
	 * Generate a unique login based on the user's email address
	 *
	 * @param    string $email user's email address
	 * @return   string
	 * @since    3.0.0
	 * @version  3.19.4
	 */
	public static function generate_username( $email ) {

		/**
		 * Allow custom username generation
		 */
		$custom_username = apply_filters( 'lifterlms_generate_username', null, $email );
		if ( $custom_username ) {
			return $custom_username;
		}

		$username      = sanitize_user( current( explode( '@', $email ) ), true );
		$orig_username = $username;
		$i             = 1;
		while ( username_exists( $username ) ) {

			$username = $orig_username . $i;
			$i++;

		}

		return apply_filters( 'lifterlms_generated_username', $username, $email );

	}

	/**
	 * Retrieve an array of fields for a specific screen
	 *
	 * Each array represents a form field that can be passed to llms_form_field()
	 *
	 * An array of data or a user ID can be passed to fill the fields via self::fill_fields()
	 *
	 * @param    string    $screen  name os the screen [account|checkout|registration]
	 * @param    array|int $data    array of data to fill fields with or a WP User ID
	 * @return   array
	 * @since    3.0.0
	 * @version  3.7.0
	 */
	public static function get_available_fields( $screen = 'registration', $data = array() ) {

		$uid = get_current_user_id();

		// setup all the fields to load
		$fields = array();

		// this isn't needed if we're on an account screen or
		if ( 'account' !== $screen && ( 'checkout' !== $screen || ! $uid ) ) {
			$fields[] = array(
				'columns'     => 12,
				'id'          => 'user_login',
				'label'       => __( 'Username', 'lifterlms' ),
				'last_column' => true,
				'required'    => true,
				'type'        => ( 'yes' === get_option( 'lifterlms_registration_generate_username' ) ) ? 'hidden' : 'text',
			);
		}

		// on the checkout screen, if we already have a user we can remove these fields:
		// username, email, email confirm, password, password confirm, password meter
		if ( 'checkout' !== $screen || ! $uid ) {
			$email_con = get_option( 'lifterlms_user_info_field_email_confirmation_' . $screen . '_visibility' );
			$fields[]  = array(
				'columns'     => ( 'no' === $email_con ) ? 12 : 6,
				'id'          => 'email_address',
				'label'       => __( 'Email Address', 'lifterlms' ),
				'last_column' => ( 'no' === $email_con ) ? true : false,
				'matched'     => 'email_address_confirm',
				'required'    => true,
				'type'        => 'email',
			);
			if ( 'yes' === $email_con ) {
				$fields[] = array(
					'columns'     => 6,
					'id'          => 'email_address_confirm',
					'label'       => __( 'Confirm Email Address', 'lifterlms' ),
					'last_column' => true,
					'match'       => 'email_address',
					'required'    => true,
					'type'        => 'email',
				);
			}

			// account screen has password updates at the bottom
			if ( 'account' !== $screen ) {
				$fields = self::get_password_fields( $screen, $fields );
			}
		}

		$names = get_option( 'lifterlms_user_info_field_names_' . $screen . '_visibility' );
		if ( 'hidden' !== $names ) {
			$fields[] = array(
				'columns'     => 6,
				'id'          => 'first_name',
				'label'       => __( 'First Name', 'lifterlms' ),
				'last_column' => false,
				'required'    => ( 'required' === $names ) ? true : false,
				'type'        => 'text',
			);
			$fields[] = array(
				'columns'     => 6,
				'id'          => 'last_name',
				'label'       => __( 'Last Name', 'lifterlms' ),
				'last_column' => true,
				'required'    => ( 'required' === $names ) ? true : false,
				'type'        => 'text',
			);
		}

		$address = get_option( 'lifterlms_user_info_field_address_' . $screen . '_visibility' );

		if ( 'hidden' !== $address ) {
			$fields[] = array(
				'columns'     => 8,
				'id'          => self::$meta_prefix . 'billing_address_1',
				'label'       => __( 'Street Address', 'lifterlms' ),
				'last_column' => false,
				'required'    => ( 'required' === $address ) ? true : false,
				'type'        => 'text',
			);
			$fields[] = array(
				'columns'     => 4,
				'id'          => self::$meta_prefix . 'billing_address_2',
				'label'       => '&nbsp;',
				'last_column' => true,
				'placeholder' => __( 'Apartment, suite, or unit', 'lifterlms' ),
				'required'    => false,
				'type'        => 'text',
			);
			$fields[] = array(
				'columns'     => 6,
				'id'          => self::$meta_prefix . 'billing_city',
				'label'       => __( 'City', 'lifterlms' ),
				'last_column' => false,
				'required'    => ( 'required' === $address ) ? true : false,
				'type'        => 'text',
			);
			$fields[] = array(
				'columns'     => 3,
				'id'          => self::$meta_prefix . 'billing_state',
				'label'       => __( 'State', 'lifterlms' ),
				'last_column' => false,
				'required'    => ( 'required' === $address ) ? true : false,
				'type'        => 'text',
			);
			$fields[] = array(
				'columns'     => 3,
				'id'          => self::$meta_prefix . 'billing_zip',
				'label'       => __( 'Zip Code', 'lifterlms' ),
				'last_column' => true,
				'required'    => ( 'required' === $address ) ? true : false,
				'type'        => 'text',
			);
			$fields[] = array(
				'columns'     => 12,
				'default'     => get_lifterlms_country(),
				'id'          => self::$meta_prefix . 'billing_country',
				'label'       => __( 'Country', 'lifterlms' ),
				'last_column' => true,
				'options'     => get_lifterlms_countries(),
				'required'    => ( 'required' === $address ) ? true : false,
				'type'        => 'select',
			);
		}// End if().

		$phone = get_option( 'lifterlms_user_info_field_phone_' . $screen . '_visibility' );
		if ( 'hidden' !== $phone ) {
			$fields[] = array(
				'columns'     => 12,
				'id'          => self::$meta_prefix . 'phone',
				'label'       => __( 'Phone Number', 'lifterlms' ),
				'last_column' => true,
				'placeholder' => _x( '(123) 456 - 7890', 'Phone Number Placeholder', 'lifterlms' ),
				'required'    => ( 'required' === $phone ) ? true : false,
				'type'        => 'text',
			);
		}

		$voucher = get_option( 'lifterlms_voucher_field_' . $screen . '_visibility', '' );
		if ( 'registration' === $screen && 'hidden' !== $voucher ) {

			$toggleable    = apply_filters( 'llms_voucher_toggleable', ( 'required' === $voucher ) ? false : true );
			$voucher_label = __( 'Have a voucher?', 'lifterlms' );
			if ( $toggleable ) {
				$voucher_label = '<a class="llms-voucher-toggle" id="llms-voucher-toggle" href="#">' . $voucher_label . '</a>';
				add_action( 'wp_print_footer_scripts', array( __CLASS__, 'voucher_toggle_script' ) );
			}

			$fields[] = array(
				'columns'     => 12,
				'id'          => self::$meta_prefix . 'voucher',
				'label'       => $voucher_label,
				'last_column' => true,
				'placeholder' => __( 'Voucher Code', 'lifterlms' ),
				'required'    => ( 'required' === $voucher ) ? true : false,
				'style'       => $toggleable ? 'display: none;' : '',
				'type'        => 'text',
			);

		}

		// add account password fields
		if ( 'account' === $screen ) {
			$fields = self::get_password_fields( $screen, $fields );
		}

		$fields = apply_filters( 'lifterlms_get_person_fields', $fields, $screen );

		// populate fields with data, if we have any
		if ( $data ) {
			$fields = self::fill_fields( $fields, $data );
		}

		return $fields;

	}

	/**
	 * Get the fields for the login form
	 *
	 * @param    string $layout  form layout [columns|stacked]
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.4
	 */
	public static function get_login_fields( $layout = 'columns' ) {

		$gen_usernames = ( 'yes' === get_option( 'lifterlms_registration_generate_username' ) );

		return apply_filters(
			'lifterlms_person_login_fields',
			array(
				array(
					'columns'     => ( 'columns' == $layout ) ? 6 : 12,
					'id'          => 'llms_login',
					'label'       => $gen_usernames ? __( 'Email Address', 'lifterlms' ) : __( 'Username or Email Address', 'lifterlms' ),
					'last_column' => ( 'columns' == $layout ) ? false : true,
					'required'    => true,
					'type'        => $gen_usernames ? 'email' : 'text',
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
					'description'     => '<a href="' . esc_url( llms_lostpassword_url() ) . '">' . __( 'Lost your password?', 'lifterlms' ) . '</a>',
					'type'            => 'html',
					'wrapper_classes' => 'align-right',
				),
			)
		);

	}

	/**
	 * Retrieve an array of password fields for a specific screen
	 *
	 * Each array represents a form field that can be passed to llms_form_field()
	 *
	 * @param    string $screen  name os the screen [account|checkout|registration]
	 * @param    array  $fields  array of fields to add the pass fields to (from self::get_available_fields() for example)
	 * @return   array
	 * @since    3.7.0
	 * @version  3.7.0
	 */
	private static function get_password_fields( $screen = 'registration', $fields = array() ) {

		if ( 'account' === $screen ) {
			$fields[] = array(
				'columns'         => 12,
				'id'              => 'current_password',
				'label'           => __( 'Current Password', 'lifterlms' ),
				'last_column'     => true,
				'required'        => true,
				'type'            => 'password',
				'wrapper_classes' => 'llms-change-password',
			);
		}

		$fields[] = array(
			'columns'         => 6,
			'classes'         => 'llms-password',
			'id'              => 'password',
			'label'           => ( 'account' === $screen ) ? __( 'New Password', 'lifterlms' ) : __( 'Password', 'lifterlms' ),
			'last_column'     => false,
			'matched'         => 'password_confirm',
			'required'        => true,
			'type'            => 'password',
			'wrapper_classes' => ( 'account' === $screen ) ? 'llms-change-password' : '',
		);
		$fields[] = array(
			'columns'         => 6,
			'classes'         => 'llms-password-confirm',
			'id'              => 'password_confirm',
			'label'           => ( 'account' === $screen ) ? __( 'Confirm New Password', 'lifterlms' ) : __( 'Confirm Password', 'lifterlms' ),
			'last_column'     => true,
			'match'           => 'password',
			'required'        => true,
			'type'            => 'password',
			'wrapper_classes' => ( 'account' === $screen ) ? 'llms-change-password' : '',
		);

		if ( 'yes' === get_option( 'lifterlms_registration_password_strength' ) ) {
			$strength = llms_get_minimum_password_strength();
			if ( 'strong' === $strength ) {
				$desc = __( 'A %s password is required.', 'lifterlms' );
			} else {
				$desc = __( 'A minimum password strength of %s is required.', 'lifterlms' );
			}

			$fields[] = array(
				'columns'         => 12,
				'classes'         => 'llms-password-strength-meter',
				'description'     => sprintf( $desc, llms_get_minimum_password_strength_name() ) . ' ' . __( 'The password must be at least 6 characters in length. Consider adding letters, numbers, and symbols to increase the password strength.', 'lifterlms' ),
				'id'              => 'llms-password-strength-meter',
				'last_column'     => true,
				'type'            => 'html',
				'wrapper_classes' => ( 'account' === $screen ) ? 'llms-change-password' : '',
			);
		}

		if ( 'account' === $screen ) {

			$fields[] = array(
				'columns'     => 12,
				'classes'     => 'llms-password-change-toggle',
				'value'       => '<a data-action="show" data-text="' . __( 'Cancel', 'lifterlms' ) . '" href="#llms-password-change-toggle">' . __( 'Change Password', 'lifterlms' ) . '</a>',
				'id'          => 'llms-password-change-toggle',
				'last_column' => true,
				'type'        => 'html',
			);

		}

		return $fields;

	}

	/**
	 * Retrieve fields for password recovery
	 * This is for the form that sends a password reset email
	 *
	 * @return   array
	 * @since    3.8.0
	 * @version  3.8.0
	 */
	public static function get_lost_password_fields() {

		$gen_usernames = ( 'yes' === get_option( 'lifterlms_registration_generate_username' ) );

		if ( $gen_usernames ) {
			$message = __( 'Lost your password? Enter your email address and we will send you a link to reset it.', 'lifterlms' );
		} else {
			$message = __( 'Lost your password? Enter your username or email address and we will send you a link to reset it.', 'lifterlms' );
		}

		return apply_filters(
			'lifterlms_lost_password_fields',
			array(
				array(
					'columns'     => 12,
					'id'          => 'llms_lost_password_message',
					'last_column' => true,
					'type'        => 'html',
					'value'       => apply_filters( 'lifterlms_lost_password_message', $message ),
				),
				array(
					'columns'     => 12,
					'id'          => 'llms_login',
					'label'       => $gen_usernames ? __( 'Email Address', 'lifterlms' ) : __( 'Username or Email Address', 'lifterlms' ),
					'last_column' => true,
					'required'    => true,
					'type'        => $gen_usernames ? 'email' : 'text',
				),
				array(
					'columns'     => 12,
					'classes'     => 'llms-button-action auto',
					'id'          => 'llms_lost_password_button',
					'value'       => __( 'Reset Password', 'lifterlms' ),
					'last_column' => true,
					'required'    => false,
					'type'        => 'submit',
				),
			)
		);

	}

	public static function get_password_reset_fields( $key = '', $login = '' ) {
		$fields   = self::get_password_fields( 'reset' );
		$fields[] = array(
			'columns'     => 12,
			'classes'     => 'llms-button-action auto',
			'id'          => 'llms_lost_password_button',
			'value'       => __( 'Update Password', 'lifterlms' ),
			'last_column' => true,
			'required'    => false,
			'type'        => 'submit',
		);
		$fields[] = array(
			'id'       => 'llms_reset_key',
			'required' => true,
			'type'     => 'hidden',
			'value'    => $key,
		);
		$fields[] = array(
			'id'       => 'llms_reset_login',
			'required' => true,
			'type'     => 'hidden',
			'value'    => $login,
		);
		return apply_filters( 'lifterlms_lost_password_fields', $fields );
	}

	/**
	 * Field an array of user fields retrieved from self::get_available_fields() with data
	 * the resulting array will be the data retrieved from self::get_available_fields() with "value" keys filled for each field
	 *
	 * @since 3.0.0
	 * @since 3.35.0 Sanitize field data when filling field with user-submitted data.
	 *
	 * @param    array $fields array of fields from self::get_available_fields()
	 * @param    array $data   array of data (from a $_POST or function)
	 * @return   array
	 */
	private static function fill_fields( $fields, $data ) {

		if ( is_numeric( $data ) ) {
			$user = new LLMS_Student( $data );
		}

		foreach ( $fields as &$field ) {

			if ( 'password' === $field['type'] || 'html' === $field['type'] ) {
				continue;
			}

			$name = isset( $field['name'] ) ? $field['name'] : $field['id'];
			$val  = false;

			if ( isset( $data[ $name ] ) ) {

				$val = $data[ $name ];

			} elseif ( isset( $user ) ) {

				if ( 'email_address' === $name ) {
					$name = 'user_email';
				}
				$val = $user->get( $name );

			}

			if ( $val ) {
				if ( 'checkbox' === $field['type'] ) {
					if ( $val == $field['value'] ) {
						$field['selected'] = true;
					}
				} else {
					$field['value'] = self::sanitize_field( $val, $field['type'] );
				}
			}
		}

		return $fields;

	}


	/**
	 * Insert user data during registrations and updates
	 *
	 * @param    array  $data    array of user data to be passed to WP core functions
	 * @param    string $action  either registration or update
	 * @return   WP_Error|int        WP_Error on error or the WP User ID
	 * @since    3.0.0
	 * @version  3.24.0
	 */
	private static function insert_data( $data = array(), $action = 'registration' ) {

		if ( 'registration' === $action ) {
			$insert_data = array(
				'role'                 => 'student',
				'show_admin_bar_front' => false,
				'user_email'           => $data['email_address'],
				'user_login'           => $data['user_login'],
				'user_pass'            => $data['password'],
			);

			$extra_data = array(
				'first_name',
				'last_name',
			);

			$insert_func = 'wp_insert_user';
			$meta_func   = 'add_user_meta';

		} elseif ( 'update' === $action ) {

			$insert_data = array(
				'ID' => $data['user_id'],
			);

			// email address if set
			if ( isset( $data['email_address'] ) ) {
				$insert_data['user_email'] = $data['email_address'];
			}

			// update password if both are set
			if ( isset( $data['password'] ) && isset( $data['password_confirm'] ) ) {
				$insert_data['user_pass'] = $data['password'];
			}

			$extra_data = array(
				'first_name',
				'last_name',
			);

			$insert_func = 'wp_update_user';
			$meta_func   = 'update_user_meta';

		} else {

			return new WP_Error( 'invalid', __( 'Invalid action', 'lifterlms' ) );

		}// End if().

		foreach ( $extra_data as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$insert_data[ $field ] = $data[ $field ];
			}
		}

		// attempt to insert the data
		$person_id = $insert_func( apply_filters( 'lifterlms_user_' . $action . '_insert_user', $insert_data, $data, $action ) );

		// return the error object if registration fails
		if ( is_wp_error( $person_id ) ) {
			return apply_filters( 'lifterlms_user_' . $action . '_failure', $person_id, $data, $action );
		}

		// add user ip address
		$data[ self::$meta_prefix . 'ip_address' ] = llms_get_ip_address();

		// metas
		$possible_metas = apply_filters(
			'llms_person_insert_data_possible_metas',
			array(
				self::$meta_prefix . 'billing_address_1',
				self::$meta_prefix . 'billing_address_2',
				self::$meta_prefix . 'billing_city',
				self::$meta_prefix . 'billing_state',
				self::$meta_prefix . 'billing_zip',
				self::$meta_prefix . 'billing_country',
				self::$meta_prefix . 'ip_address',
				self::$meta_prefix . 'phone',
			)
		);
		$insert_metas   = array();
		foreach ( $possible_metas as $meta ) {
			if ( isset( $data[ $meta ] ) ) {
				$insert_metas[ $meta ] = $data[ $meta ];
			}
		}

		// record all meta values
		$metas = apply_filters( 'lifterlms_user_' . $action . '_insert_user_meta', $insert_metas, $data, $action );
		foreach ( $metas as $key => $val ) {
			$meta_func( $person_id, $key, $val );
		}

		// if agree to terms data is present, record the agreement date
		if ( isset( $data[ self::$meta_prefix . 'agree_to_terms' ] ) && 'yes' === $data[ self::$meta_prefix . 'agree_to_terms' ] ) {

			$meta_func( $person_id, self::$meta_prefix . 'agree_to_terms', current_time( 'mysql' ) );

		}

		return $person_id;

	}

	/**
	 * Login a user
	 *
	 * @param    array $data array of login data.
	 * @return   WP_Error|int WP_Error on error or the WP_User ID.
	 * @since    3.0.0
	 * @version  3.29.4
	 */
	public static function login( $data ) {

		do_action( 'lifterlms_before_user_login', $data );

		// validate the fields & allow custom validation to occur.
		$valid = self::validate_fields( apply_filters( 'lifterlms_user_login_data', $data ), 'login' );

		// if errors found, return them.
		if ( is_wp_error( $valid ) ) {
			return apply_filters( 'lifterlms_user_login_errors', $valid, $data, false );
		}

		$creds               = array();
		$creds['user_login'] = $data['llms_login'];

		$err = new WP_Error( 'login-error', __( 'Could not find an account with the supplied email address and password combination.', 'lifterlms' ) );

		// get the username from the email address
		if ( llms_parse_bool( get_option( 'lifterlms_registration_generate_username' ) ) && apply_filters( 'lifterlms_get_username_from_email', true ) ) {

			$user = get_user_by( 'email', wp_unslash( $data['llms_login'] ) );

			if ( ! isset( $user->user_login ) ) {
				return apply_filters( 'lifterlms_user_login_errors', $err, $data, false );
			}

			$creds['user_login'] = $user->user_login;

		}

		$creds['user_password'] = $data['llms_password'];
		$creds['remember']      = isset( $data['llms_remember'] );

		$signon = wp_signon( apply_filters( 'lifterlms_login_credentials', $creds ), is_ssl() );

		if ( is_wp_error( $signon ) ) {
			return apply_filters( 'lifterlms_user_login_errors', $err, $data, $signon );
		}

		return $signon->ID;

	}

	/**
	 * Perform validations according to the registration screen and registers a user
	 *
	 * @see  llms_register_user() for a classless wrapper for this function
	 *
	 * @param  array  $data   array of user data
	 *                        array(
	 *                          'user_login' => '',
	 *                          'email_address' => '',
	 *                          'email_address_confirm' => '',
	 *                          'password' => '',
	 *                          'password_confirm' => '',
	 *                          'first_name' => '',
	 *                          'last_name' => '',
	 *                          'llms_billing_address_1' => '',
	 *                          'llms_billing_address_2' => '',
	 *                          'llms_billing_city' => '',
	 *                          'llms_billing_state' => '',
	 *                          'llms_billing_zip' => '',
	 *                          'llms_billing_country' => '',
	 *                          'llms_phone' => '',
	 *                        )
	 * @param    string $screen  screen to perform validations for, accepts "registration" or "checkout"
	 * @param    bool   $signon  if true, also signon the newly created user
	 * @return   int|WP_Error
	 * @since    3.0.0
	 * @version  3.19.4
	 */
	public static function register( $data = array(), $screen = 'registration', $signon = true ) {

		do_action( 'lifterlms_before_user_registration', $data, $screen );

		// generate a username if we're supposed to generate a username
		if ( llms_parse_bool( get_option( 'lifterlms_registration_generate_username' ) ) && ! empty( $data['email_address'] ) ) {
			$data['user_login'] = self::generate_username( $data['email_address'] );
		}

		// validate the fields & allow custom validation to occur
		$valid = apply_filters( 'lifterlms_user_registration_data', self::validate_fields( $data, $screen ), $data, $screen );

		// if errors found, return them
		if ( is_wp_error( $valid ) ) {

			return apply_filters( 'lifterlms_user_registration_errors', $valid, $data, $screen );

		} else {

			do_action( 'lifterlms_user_registration_after_validation', $data, $screen );

			// create the user and update all metadata
			$person_id = self::insert_data( $data, 'registration' ); // even during checkout we want to call this registration

			// return the error object if registration fails
			if ( is_wp_error( $person_id ) ) {
				return $person_id; // this is filtered already
			}

			// signon
			if ( $signon ) {
				llms_set_person_auth_cookie( $person_id, false );
			}

			// fire actions
			do_action( 'lifterlms_created_person', $person_id, $data, $screen );
			do_action( 'lifterlms_user_registered', $person_id, $data, $screen );

			// return the ID
			return $person_id;

		}

	}

	/**
	 * Sanitize posted fields
	 *
	 * @param    string $val         unsanitized user data
	 * @param    string $field_type  field type, allows additional sanitization to run based on field type
	 * @return   string
	 * @since    3.19.4
	 * @version  3.19.4
	 */
	private static function sanitize_field( $val, $field_type = '' ) {

		$val = trim( sanitize_text_field( $val ) );
		if ( $field_type && 'email' === $field_type ) {
			$val = wp_unslash( $val );
		}

		return $val;

	}


	/**
	 * Perform validations according to $screen and update the user
	 *
	 * @see    llms_update_user() for a classless wrapper for this function
	 *
	 * @param  array  $data   array of user data
	 *                        array(
	 *                          'user_id' => '',
	 *                          'user_login' => '',
	 *                          'email_address' => '',
	 *                          'email_address_confirm' => '',
	 *                          'current_password' => '',
	 *                          'password' => '',
	 *                          'password_confirm' => '',
	 *                          'first_name' => '',
	 *                          'last_name' => '',
	 *                          'llms_billing_address_1' => '',
	 *                          'llms_billing_address_2' => '',
	 *                          'llms_billing_city' => '',
	 *                          'llms_billing_state' => '',
	 *                          'llms_billing_zip' => '',
	 *                          'llms_billing_country' => '',
	 *                          'llms_phone' => '',
	 *                        )
	 * @param    string $screen  screen to perform validations for, accepts "account", update" or "checkout"
	 * @return   int|WP_Error
	 * @since    3.0.0
	 * @version  3.7.0
	 */
	public static function update( $data = array(), $screen = 'update' ) {

		do_action( 'lifterlms_before_user_update', $data, $screen );

		// user_id will automatically be the current user if non provided
		if ( empty( $data['user_id'] ) ) {
			$data['user_id'] = get_current_user_id();
		}

		// if no user id available, return an error
		if ( ! $data['user_id'] ) {
			$e = new WP_Error();
			$e->add( 'user_id', __( 'No user ID specified.', 'lifterlms' ), 'missing-user-id' );
			return $e;
		}

		// validate the fields & allow custom validation to occur
		$valid = apply_filters( 'lifterlms_user_update_data', self::validate_fields( $data, $screen ), $data, $screen );

		// if errors found, return them
		if ( is_wp_error( $valid ) ) {

			return apply_filters( 'lifterlms_user_update_errors', $valid, $data, $screen );

		} else {

			do_action( 'lifterlms_user_update_after_validation', $data, $screen );

			// create the user and update all metadata
			$person_id = self::insert_data( $data, 'update' );

			// return the error object if registration fails
			if ( is_wp_error( $person_id ) ) {
				return $person_id; // this is filtered already
			}

			do_action( 'lifterlms_user_updated', $person_id, $data, $screen );

			return $person_id;

		}

	}

	/**
	 * Validate submitted user data for registration or profile updates
	 *
	 * @param  array  $data   user data array
	 *                        array(
	 *                          'user_login' => '',
	 *                          'email_address' => '',
	 *                          'email_address_confirm' => '',
	 *                          'password' => '',
	 *                          'password_confirm' => '',
	 *                          'first_name' => '',
	 *                          'last_name' => '',
	 *                          'llms_billing_address_1' => '',
	 *                          'llms_billing_address_2' => '',
	 *                          'llms_billing_city' => '',
	 *                          'llms_billing_state' => '',
	 *                          'llms_billing_zip' => '',
	 *                          'llms_billing_country' => '',
	 *                          'llms_phone' => '',
	 *                        )
	 * @param    string $screen screen to validate fields against, accepts "account", "checkout", "registration", or "update"
	 * @return   true|WP_Error
	 * @since    3.0.0
	 * @version  3.19.4
	 */
	public static function validate_fields( $data, $screen = 'registration' ) {

		if ( 'login' === $screen ) {

			$fields = self::get_login_fields();

		} elseif ( 'reset_password' === $screen ) {

			$fields = self::get_password_reset_fields();

		} else {

			$fields = self::get_available_fields( $screen );

			// if no current password submitted with an account update
			// we can remove password fields so we don't get false validations
			if ( 'account' === $screen && empty( $data['current_password'] ) ) {
				unset( $data['current_password'], $data['password'], $data['password_confirm'] );
				foreach ( $fields as $key => $field ) {
					if ( in_array( $field['id'], array( 'current_password', 'password', 'password_confirm' ) ) ) {
						unset( $fields[ $key ] );
					}
				}
			}
		}

		$e = new WP_Error();

		$matched_values = array();

		foreach ( $fields as $field ) {

			$name  = isset( $field['name'] ) ? $field['name'] : $field['id'];
			$label = isset( $field['label'] ) ? $field['label'] : $name;

			$field_type = isset( $field['type'] ) ? $field['type'] : '';
			$val        = isset( $data[ $name ] ) ? self::sanitize_field( $data[ $name ], $field_type ) : '';

			// ensure required fields are submitted
			if ( isset( $field['required'] ) && $field['required'] && empty( $val ) ) {

				$e->add( $field['id'], sprintf( __( '%s is a required field', 'lifterlms' ), $label ), 'required' );
				continue;

			}

			// check email field for uniqueness
			if ( 'email_address' === $name ) {

				$skip_email = false;

				// only run this check when we're trying to change the email address for an account update
				if ( 'account' === $screen ) {
					$user = wp_get_current_user();
					if ( self::sanitize_field( $data['email_address'], 'email' ) === $user->user_email ) {
						$skip_email = true;
					}
				}

				if ( ! $skip_email && email_exists( $val ) ) {
					$e->add( $field['id'], sprintf( __( 'An account with the email address "%s" already exists.', 'lifterlms' ), $val ), 'email-exists' );
				}
			} elseif ( 'user_login' === $name ) {

				// blacklist usernames for security purposes
				$banned_usernames = apply_filters( 'llms_usernames_blacklist', array( 'admin', 'test', 'administrator', 'password', 'testing' ) );

				if ( in_array( $val, $banned_usernames ) || ! validate_username( $val ) ) {

					$e->add( $field['id'], sprintf( __( 'The username "%s" is invalid, please try a different username.', 'lifterlms' ), $val ), 'invalid-username' );

				} elseif ( username_exists( $val ) ) {

					$e->add( $field['id'], sprintf( __( 'An account with the username "%s" already exists.', 'lifterlms' ), $val ), 'username-exists' );

				}
			} elseif ( 'llms_voucher' === $name && ! empty( $val ) ) {

				$v     = new LLMS_Voucher();
				$check = $v->check_voucher( $val );
				if ( is_wp_error( $check ) ) {
					$e->add( $field['id'], $check->get_error_message(), 'voucher-' . $check->get_error_code() );
				}
			} elseif ( 'current_password' === $name ) {
				$user = wp_get_current_user();
				if ( ! wp_check_password( $val, $user->data->user_pass, $user->ID ) ) {
					$e->add( $field['id'], sprintf( __( 'The submitted %s was incorrect.', 'lifterlms' ), $field['label'] ), 'incorrect-password' );
				}
			}

			// scrub and check field data types
			if ( isset( $field['type'] ) ) {

				switch ( $field['type'] ) {

					// ensure it's a selectable option
					case 'select':
					case 'radio':
						if ( ! in_array( $val, array_keys( $field['options'] ) ) ) {
							$e->add( $field['id'], sprintf( __( '"%1$s" is an invalid option for %2$s', 'lifterlms' ), $val, $label ), 'invalid' );
						}
						break;

					// case 'password':
					// case 'text':
					// case 'textarea':
					// break;

					// make sure the value is numeric
					case 'number':
						if ( ! is_numeric( $val ) ) {
							$e->add( $field['id'], sprintf( __( '%s must be numeric', 'lifterlms' ), $label ), 'invalid' );
							continue 2;
						}
						break;

					// validate the email address
					case 'email':
						if ( ! is_email( $val ) ) {
							$e->add( $field['id'], sprintf( __( '%s must be a valid email address', 'lifterlms' ), $label ), 'invalid' );
						}
						break;

				}
			}// End if().

			// store this fields label so it can be used in a match error later if necessary
			if ( ! empty( $field['matched'] ) ) {

				$matched_values[ $field['matched'] ] = $label;

			}

			// match matchy fields
			if ( ! empty( $field['match'] ) ) {

				$match = isset( $data[ $field['match'] ] ) ? self::sanitize_field( $data[ $field['match'] ], $field_type ) : false;
				if ( ! $match || $val !== $match ) {

					$e->add( $field['id'], sprintf( __( '%1$s must match %2$s', 'lifterlms' ), $matched_values[ $field['id'] ], $label ), 'match' );

				}
			}
		}// End foreach().

		// return errors if we have errors
		if ( $e->get_error_messages() ) {
			return $e;
		}

		return true;

	}


	/**
	 * Output Voucher toggle JS in a quick and shameful manner...
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function voucher_toggle_script() {

		if ( empty( self::$voucher_script_output ) ) {

			self::$voucher_script_output = true;

			echo "<script type=\"text/javascript\">
			( function( $ ) {
				$( '#llms-voucher-toggle' ).on( 'click', function( e ) {
					e.preventDefault();
					$( '#llms_voucher' ).toggle();
				} );
			} )( jQuery );
			</script>";

		}

	}

}
