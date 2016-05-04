<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
* Person Functions
*
* Functions for managing users in the LifterLMS system
*
* @author codeBOX
* @project lifterLMS
*/

/**
 * Disables admin bar on front end
 *
 * @param  bool $show_admin_bar [show = true]
 *
 * @return bool $show_admin_bar [Display admin bar on front end for user?]
 */
function llms_disable_admin_bar( $show_admin_bar ) {
	if ( apply_filters( 'lifterlms_disable_admin_bar', get_option( 'lifterlms_lock_down_admin', 'yes' ) === 'yes' ) && ! ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_lifterlms' ) ) ) {
		$show_admin_bar = false;
	}

	return $show_admin_bar;
}
add_filter( 'show_admin_bar', 'llms_disable_admin_bar', 10, 1 );

/**
 * Creates new user
 *
 * @param  string $email             [user email]
 * @param  string $email2            [user verify email]
 * @param  string $username          [username]
 * @param  string $firstname         [user first name]
 * @param  string $lastname          [user last name]
 * @param  string $password          [user password]
 * @param  string $password2         [user verify password]
 * @param  string $billing_address_1 [user billing address 1]
 * @param  string $billing_address_2 [user billing address 2]
 * @param  string $billing_city      [user billing city]
 * @param  string $billing_state     [user billing state]
 * @param  string $billing_zip       [user billing zip]
 * @param  string $billing_country   [user billing country]
 * @param  string $agree_to_terms    [agree to terms checkbox bool]
 *
 * @return int $person_id 			 [ID of the user created]
 */
function llms_create_new_person( $email, $email2, $username = '', $firstname = '', $lastname = '', $password = '', $password2 = '', $billing_address_1 = '', $billing_address_2 = '', $billing_city = '', $billing_state = '', $billing_zip = '', $billing_country = '', $agree_to_terms = '', $phone = '' ) {

	// Check the e-mail address
	if ( empty( $email ) || ! is_email( $email ) ) {
		return new WP_Error( 'registration-error', __( 'Please provide a valid email address.', 'lifterlms' ) );
	}

	if ( $email != $email2 ) {
		return new WP_Error( 'registration-error', __( 'Your email addresses do not match.', 'lifterlms' ) );
	}

	if ( email_exists( $email ) ) {
		return new WP_Error( 'registration-error', __( 'An account is already registered with your email address. Please login.', 'lifterlms' ) );
	}

	// Handle username creation
	if ( 'no' === get_option( 'lifterlms_registration_generate_username' ) || ! empty( $username ) ) {

		$username = sanitize_user( $username );

		if ( empty( $username ) || ! validate_username( $username ) ) {
			return new WP_Error( 'registration-error', __( 'Please enter a valid account username.', 'lifterlms' ) );
		}

		if ( username_exists( $username ) ) {

			return new WP_Error( 'registration-error', __( 'An account is already registered with that username. Please choose another.', 'lifterlms' ) );

		}

	} else {

		$username = sanitize_user( current( explode( '@', $email ) ) );

		// Ensure username is unique
		$append     = 1;
		$o_username = $username;

		while ( username_exists( $username ) ) {
			$username = $o_username . $append;
			$append++;
		}
	}

	if ( 'yes' === get_option( 'lifterlms_registration_require_name' ) ) {

		if ( empty( $firstname ) || empty( $lastname ) ) {
			return new WP_Error( 'registration-error', __( 'Please enter your name.', 'lifterlms' ) );
		}

	}

	if ( 'yes' === get_option( 'lifterlms_registration_require_address' ) ) {
		if ( empty( $billing_address_1 ) ) {
			return new WP_Error( 'registration-error', __( 'Please enter your billing address.', 'lifterlms' ) );
		}
		if ( empty( $billing_city ) ) {
			return new WP_Error( 'registration-error', __( 'Please enter your billing city.', 'lifterlms' ) );
		}
		if ( empty( $billing_state ) ) {
			return new WP_Error( 'registration-error', __( 'Please enter your billing state.', 'lifterlms' ) );
		}
		if ( empty( $billing_zip ) ) {
			return new WP_Error( 'registration-error', __( 'Please enter your billing zip code.', 'lifterlms' ) );
		}
		if ( empty( $billing_country ) ) {
			return new WP_Error( 'registration-error', __( 'Please enter your billing country.', 'lifterlms' ) );
		}
	}

	//get terms page
	$terms = get_option( 'lifterlms_terms_page_id' );
	if ( ( 'yes' === get_option( 'lifterlms_registration_require_agree_to_terms' ) ) && $terms ) {

		if ( empty( $agree_to_terms ) ) {
			return new WP_Error( 'registration-error', __( 'You must agree to the Terms and Conditions.', 'lifterlms' ) );
		}

	}

	// Handle password creation
	if ( empty( $password ) ) {
		return new WP_Error( 'registration-error', __( 'Please enter an account password.', 'lifterlms' ) );
	} elseif ( $password != $password2 ) {
		return new WP_Error( 'registration-error', __( 'Your passwords did not match.', 'lifterlms' ) );
	} else {
		$password_generated = false;
	}

	// WP Validation
	$validation_errors = new WP_Error();

	do_action( 'lifterlms_register_post', $username, $email, $validation_errors, $firstname, $lastname );

	$validation_errors = apply_filters( 'lifterlms_registration_errors', $validation_errors, $username, $email, $firstname, $lastname, $billing_address_1, $billing_city, $billing_state, $billing_zip, $billing_country );

	if ( $validation_errors->get_error_code() ) {
		return $validation_errors; }

	$new_person_data = apply_filters( 'lifterlms_new_person_data', array(
		'user_login' => $username,
		'user_pass'  => $password,
		'user_email' => $email,
		'first_name' => $firstname,
		'last_name'  => $lastname,
		'role'       => 'student',
	) );

	$new_person_address = apply_filters( 'lifterlms_new_person_address', array(
		'llms_billing_address_1' => $billing_address_1,
		'llms_billing_address_2'	=> $billing_address_2,
		'llms_billing_city'		=> $billing_city,
		'llms_billing_state'		=> $billing_state,
		'llms_billing_zip'		=> $billing_zip,
		'llms_billing_country'	=> $billing_country,
	) );

	$person_id = wp_insert_user( $new_person_data );

	foreach ($new_person_address as $key => $value ) {
		add_user_meta( $person_id, $key, $value );
	}

	if ( isset( $phone ) ) {
		add_user_meta( $person_id, 'llms_phone', $phone );
	}

	if ( is_wp_error( $person_id ) ) {
		return new WP_Error( 'registration-error', '<strong>' . __( 'ERROR', 'lifterlms' ) . '</strong>: ' . __( 'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', 'lifterlms' ) );
	}

	do_action( 'lifterlms_created_person', $person_id, $new_person_data, $password_generated );
	do_action( 'lifterlms_created_person_address', $billing_address_1, $billing_address_2, $billing_city, $billing_state, $billing_zip, $billing_country );

	return $person_id;
}



/**
 * Enroll a WordPress user in a course or membership
 * @param  int $user_id    WP User ID
 * @param  int $product_id WP Post ID of the Course or Membership
 * @return bool
 *
 * @since  2.2.3
 */
function llms_enroll_student( $user_id, $product_id ) {

	$student = new LLMS_Student( $user_id );
	return $student->enroll( $product_id );

}


/**
 * Sets user auth cookie by id
 *
 * @param  int $person_id [ID of user]
 *
 * @return void
 */
function llms_set_person_auth_cookie( $person_id ) {
	global $current_user;

	$current_user = get_user_by( 'id', $person_id );

	wp_set_auth_cookie( $person_id, true );
}


/**
 * Add Custom Columns to the Admin Users Table Screen
 *
 * @param  array $columns key=>val array of existing columns
 *
 * @return array $columns updated columns
 */
function llms_add_user_table_columns( $columns ) {
	$columns['llms-last-login'] = __( 'Last Login', 'lifterlms' );
	$columns['llms-memberships'] = __( 'Memberships', 'lifterlms' );
	return $columns;
}
add_filter( 'manage_users_columns', 'llms_add_user_table_columns' );

/**
 * Add data user data for custom column added by llms_add_user_table_columns
 *
 * @param  string $val         value of the field
 * @param  string $column_name "id" or name of the column
 * @param  int $user_id        user_id for the row in the loop
 *
 * @return string              data to display on screen
 */
function llms_add_user_table_rows( $val, $column_name, $user_id ) {
	// $user = get_userdata( $user_id );

	switch ( $column_name ) {

		/**
		 * Display user information for their last sucessful login
		 */
		case 'llms-last-login':

			$last = get_user_meta( $user_id, 'llms_last_login', true );
			$return = ($last) ? date( get_option( 'date_format' , 'Y-m-d' ). ' h:i:s a', $last ) : 'Never';

		break;

		/**
		 * Display information related to user memberships
		 */
		case 'llms-memberships':

			$user = new LLMS_Person;
			$data = $user->get_user_memberships_data( $user_id );

			if ( ! empty( $data ) ) {

				$return = '';

				foreach ( $data as $membership_id => $obj ) {

					$return .= '<b>' . get_the_title( $membership_id ) . '</b><br>';

					$return .= '<em>Status</em>: ' . $obj['_status']->meta_value;

					if ( 'Enrolled' == $obj['_status']->meta_value ) {

						$return .= '<br><em>Start Date</em>: ' . date( get_option( 'date_format' , 'Y-m-d' ), strtotime( $obj['_start_date']->updated_date ) );

						$membership_interval = get_post_meta( $membership_id, '_llms_expiration_interval', true );
						$membership_period = get_post_meta( $membership_id, '_llms_expiration_period', true );

						//only display end date if exists.
						if ( $membership_interval ) {

							$end_date = strtotime( '+' . $membership_interval . $membership_period, strtotime( $obj['_start_date']->updated_date ) );

							$return .= '<br><em>End Date</em>: ' . date( get_option( 'date_format' , 'Y-m-d' ), $end_date );
						}
					}

				}

			} else {

				return 'No memberships';

			}

		break;

		default:
			$return = $val;
	}

	return $return;

}
add_filter( 'manage_users_custom_column', 'llms_add_user_table_rows', 10, 3 );
