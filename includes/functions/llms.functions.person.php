<?php
/**
 * Person Functions
 * Functions for managing users in the LifterLMS system.
 *
 * @package  LifterLMS/Functions
 *
 * @since 1.0.0
 * @version 3.36.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Creates new user
 *
 * @deprecated 3.0.0, use 'llms_register_user' instead
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
 * @return int $person_id            [ID of the user created]
 *
 * @version 3.0.0
 */
function llms_create_new_person( $email, $email2, $username = '', $firstname = '', $lastname = '', $password = '', $password2 = '', $billing_address_1 = '', $billing_address_2 = '', $billing_city = '', $billing_state = '', $billing_zip = '', $billing_country = '', $agree_to_terms = '', $phone = '' ) {
	llms_deprecated_function( 'llms_create_new_person', '3.0.0', 'llms_register_user' );
	return llms_register_user(
		array(
			'email_address'          => $email,
			'email_address_confirm'  => $email2,
			'user_login'             => $username,
			'first_name'             => $firstname,
			'last_name'              => $lastname,
			'password'               => $password,
			'password_confirm'       => $password2,
			'llms_billing_address_1' => $billing_address_1,
			'llms_billing_address_2' => $billing_address_2,
			'llms_billing_city'      => $billing_city,
			'llms_billing_state'     => $billing_state,
			'llms_billing_zip'       => $billing_zip,
			'llms_billing_country'   => $billing_country,
			'llms_phone'             => $phone,
			'terms'                  => $agree_to_terms,
		)
	);
}

/**
 * Checks LifterLMS user capabilities against an object
 *
 * @param    string $cap     capability name
 * @param    int    $obj_id  WP_Post or WP_User ID
 * @return   boolean
 * @since    3.13.0
 * @version  3.13.0
 */
function llms_current_user_can( $cap, $obj_id = null ) {

	$caps  = LLMS_Roles::get_all_core_caps();
	$grant = false;

	if ( in_array( $cap, $caps ) ) {

		// if the user has the cap, maybe do some additional checks
		if ( current_user_can( $cap ) ) {

			switch ( $cap ) {

				case 'view_lifterlms_reports':
					// can view others reports so its okay
					if ( current_user_can( 'view_others_lifterlms_reports' ) ) {
						$grant = true;

						// can only view their own reports check if the student is their instructor
					} elseif ( $obj_id ) {

						$instructor = llms_get_instructor();
						$student    = llms_get_student( $obj_id );
						if ( $instructor && $student ) {
							foreach ( $instructor->get_posts(
								array(
									'posts_per_page' => -1,
								),
								'ids'
							) as $id ) {
								if ( $student->get_enrollment_status( $id ) ) {
									$grant = true;
									break;
								}
							}
						}
					}

					break;

				// no other checks needed
				default:
					$grant = true;

			}
		}
	}// End if().

	return apply_filters( 'llms_current_user_can_' . $cap, $grant, $obj_id );

}

/**
 * Determine whether or not a user can bypass enrollment, drip, and prerequisite restrictions
 *
 * @param    LLMS_Student|WP_User|int $user LLMS_Student, WP_User, or WP User ID, if none supplied get_current_user() will be used
 * @return   boolean
 * @since    3.7.0
 * @version  3.9.0
 */
function llms_can_user_bypass_restrictions( $user = null ) {

	$user = llms_get_student( $user );

	if ( ! $user ) {
		return false;
	}

	$roles = get_option( 'llms_grant_site_access', '' );
	if ( ! $roles ) {
		$roles = array();
	}

	if ( array_intersect( $user->get_user()->roles, $roles ) ) {
		return true;
	}

	return false;

}

/**
 * Disables admin bar on front end
 *
 * @param  bool $show_admin_bar default value (true).
 * @return bool
 * @since  1.0.0
 * @version 3.27.0
 */
function llms_disable_admin_bar( $show_admin_bar ) {
	if ( apply_filters( 'lifterlms_disable_admin_bar', true ) && ! ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_lifterlms' ) ) ) {
		$show_admin_bar = false;
	}
	return $show_admin_bar;
}
add_filter( 'show_admin_bar', 'llms_disable_admin_bar', 10 );


/**
 * Enroll a WordPress user in a course or membership
 *
 * @param  int    $user_id    WP User ID
 * @param  int    $product_id WP Post ID of the Course or Membership
 * @param  string $trigger    String describing the event that triggered the enrollment
 * @return bool
 *
 * @see  LLMS_Student->enroll() the class method wrapped by this function
 *
 * @since  2.2.3
 * @version 3.0.0 added $trigger parameter
 */
function llms_enroll_student( $user_id, $product_id, $trigger = 'unspecified' ) {
	$student = new LLMS_Student( $user_id );
	return $student->enroll( $product_id, $trigger );
}

/**
 * Get an LLMS_Instructor
 *
 * @param    mixed $user  WP_User ID, instance of WP_User, or instance of any instructor class extending this class
 * @return   LLMS_Instructor|false LLMS_Instructor instance on success, false if user not found
 * @since    3.13.0
 * @version  3.13.0
 */
function llms_get_instructor( $user = null ) {
	$student = new LLMS_Instructor( $user );
	return $student->exists() ? $student : false;
}

/**
 * Retrieve the minimum accepted password strength for student passwords
 *
 * @return string
 * @since  3.0.0
 */
function llms_get_minimum_password_strength() {
	return apply_filters( 'llms_get_minimum_password_strength', get_option( 'lifterlms_registration_password_min_strength' ) );
}

/**
 * Retrieve the translated name of minimum accepted password strength for student passwords
 *
 * @return string
 * @since  3.0.0
 */
function llms_get_minimum_password_strength_name() {
	$strength = llms_get_minimum_password_strength();
	switch ( $strength ) {
		case 'strong':
			$r = __( 'strong', 'lifterlms' );
			break;

		case 'medium':
			$r = __( 'medium', 'lifterlms' );
			break;

		case 'weak':
			$r = __( 'weak', 'lifterlms' );
			break;

		case 'very-weak':
			$r = __( 'very weak', 'lifterlms' );
			break;

		default:
			$r = apply_filters( 'llms_get_minimum_password_strength_name_' . $strength, $strength );
	}

	return $r;
}

/**
 * Get an LLMS_Student
 *
 * @param    mixed $user  WP_User ID, instance of WP_User, or instance of any student class extending this class
 * @return   LLMS_Student|false LLMS_Student instance on success, false if user not found
 * @since    3.8.0
 * @version  3.9.0
 */
function llms_get_student( $user = null ) {
	$student = new LLMS_Student( $user );
	return $student->exists() ? $student : false;
}

/**
 * Checks if user is currently enrolled in course
 *
 * @see     LLMS_Student->is_enrolled()
 * @param   int       $user_id     WP_User ID.
 * @param   int|array $product_id  WP Post ID of a Course, Lesson, or Membership or array of multiple IDs.
 * @param   string    $relation    Comparator for enrollment check.
 *                                     All = user must be enrolled in all $product_ids.
 *                                     Any = user must be enrolled in at least one of the $product_ids.
 * @param   bool      $use_cache  If true, returns cached data if available, if false will run a db query.
 *
 * @return  boolean
 *
 * @since   unknown
 * @version 3.25.0
 */
function llms_is_user_enrolled( $user_id, $product_id, $relation = 'all', $use_cache = true ) {
	$student = new LLMS_Student( $user_id );
	return $student->is_enrolled( $product_id, $relation, $use_cache );
}

/**
 * Checks if the given object is complete for the given student
 *
 * @param  int $user_id      WP User ID of the user
 * @param  int $object_id    WP Post ID of a Course, Section, or Lesson
 * @param  int $object_type  Type, either Course, Section, or Lesson
 *
 * @see  LLMS_Student->is_complete()
 *
 * @return bool    true if complete, false otherwise
 *
 * @version  3.3.1  updated to use LLMS_Student->is_enrolled()
 */
function llms_is_complete( $user_id, $object_id, $object_type = 'course' ) {
	$s = new LLMS_Student( $user_id );
	return $s->is_complete( $object_id, $object_type );
}

/**
 * Mark a lesson, section, course, or track as complete
 *
 * @param  int    $user_id      WP User ID
 * @param  int    $object_id    WP Post ID of the Lesson, Section, Track, or Course
 * @param  int    $object_type  object type [lesson|section|course|track]
 * @param  string $trigger      String describing the event that triggered marking the object as complete
 * @return bool
 *
 * @see    LLMS_Student->mark_complete() the class method wrapped by this function
 *
 * @since     3.3.1
 * @version   3.3.1
 */
function llms_mark_complete( $user_id, $object_id, $object_type, $trigger = 'unspecified' ) {
	$student = new LLMS_Student( $user_id );
	return $student->mark_complete( $object_id, $object_type, $trigger );
}

/**
 * Mark a lesson, section, course, or track as incomplete
 *
 * @param  int    $user_id      WP User ID
 * @param  int    $object_id    WP Post ID of the Lesson, Section, Track, or Course
 * @param  int    $object_type  object type [lesson|section|course|track]
 * @param  string $trigger      String describing the event that triggered marking the object as incomplete
 * @return bool
 *
 * @see    LLMS_Student->mark_incomplete() the class method wrapped by this function
 *
 * @since     3.5.0
 * @version   3.5.0
 */
function llms_mark_incomplete( $user_id, $object_id, $object_type, $trigger = 'unspecified' ) {
	$student = new LLMS_Student( $user_id );
	return $student->mark_incomplete( $object_id, $object_type, $trigger );
}

/**
 * Register a new user
 *
 * @see  LLMS_Person_Handler::register()
 *
 * @param  array  $data    array of registration data
 * @param  string $screen  the screen to be used for the validation template, accepts "registration" or "checkout"
 * @param  bool   $signon  if true, signon the newly created user
 *
 * @return int|WP_Error
 *
 * @since 3.0.0
 */
function llms_register_user( $data = array(), $screen = 'registration', $signon = true ) {
	return LLMS_Person_Handler::register( $data, $screen, $signon );
}

/**
 * Sets user auth cookie by id and records the date/time of the login in the usermeta table
 *
 * @since  Unknown
 * @since  3.0.0 Use `wp_set_current_user()` rather than overriding the global manually.
 * @since  3.36.0 Pass the `$remember` param to `wp_set_auth_cookie()`
 *
 * @param  int  $person_id WP_User ID.
 * @param  bool $remember Whether to remember the user.
 * @return void
 */
function llms_set_person_auth_cookie( $user_id, $remember = false ) {
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, $remember );
	update_user_meta( $user_id, 'llms_last_login', current_time( 'mysql' ) );
}

/**
 * Generate a user password reset key, hash it, and store it in the database
 *
 * @param    int $user_id  WP_User ID
 * @return   string
 * @since    3.8.0
 * @version  3.8.0
 */
function llms_set_user_password_rest_key( $user_id ) {

	$user = get_user_by( 'ID', $user_id );

	// generate an activation key
	$key = wp_generate_password( 20, false );

	do_action( 'retrieve_password_key', $user->user_login, $key ); // wp core hook

	// insert the hashed key into the db
	if ( empty( $wp_hasher ) ) {
		require_once ABSPATH . 'wp-includes/class-phpass.php';
		$wp_hasher = new PasswordHash( 8, true );
	}
	$hashed = $wp_hasher->HashPassword( $key );

	global $wpdb;
	$wpdb->update(
		$wpdb->users,
		array(
			'user_activation_key' => $hashed,
		),
		array(
			'user_login' => $user->user_login,
		)
	);

	return $key;

}

/**
 * Remove a LifterLMS Student from a course or membership
 *
 * @param  int    $user_id     WP User ID
 * @param  int    $product_id  WP Post ID of the Course or Membership
 * @param  string $new_status  the value to update the new status with after removal is complete
 * @param  string $trigger     only remove the student if the original enrollment trigger matches the submitted value
 *                             "any" will remove regardless of enrollment trigger
 * @return boolean
 *
 * @see  LLMS_Student->unenroll() the class method wrapped by this function
 *
 * @since  3.0.0
 */
function llms_unenroll_student( $user_id, $product_id, $new_status = 'expired', $trigger = 'any' ) {
	$student = new LLMS_Student( $user_id );
	return $student->unenroll( $product_id, $trigger, $new_status );
}

/**
 * Delete LifterLMS Student's Enrollment record related to a given product.
 *
 * @since 3.33.0
 * @param  int    $user_id     WP User ID.
 * @param  int    $product_id  WP Post ID of the Course or Membership.
 * @param  string $trigger     Optional. Only delete the student enrollment if the original enrollment trigger matches the submitted value
 *                             "any" will remove regardless of enrollment trigger.
 * @return boolean Whether or not the enrollment records have been succesfully removed.
 *
 * @see `LLMS_Student->delete_enrollment()` the class method wrapped by this function.
 */
function llms_delete_student_enrollment( $user_id, $product_id, $trigger = 'any' ) {
	$student = new LLMS_Student( $user_id );
	return $student->delete_enrollment( $product_id, $trigger );
}

/**
 * Perform validations according to $screen and updates the user
 *
 * @see      LLMS_Person_Handler::update()
 *
 * @param    array  $data   array of user data
 * @param    string $screen  screen to perform validations for, accepts "account" or "checkout"
 * @return   int|WP_Error
 *
 * @since    3.0.0
 * @version  3.7.0
 */
function llms_update_user( $data = array(), $screen = 'account' ) {
	return LLMS_Person_Handler::update( $data, $screen );
}


















/**
 * @todo  move this to a post-table like interface & add more useful info to the table
 */

/**
 * Add Custom Columns to the Admin Users Table Screen
 *
 * @param  array $columns key=>val array of existing columns
 *
 * @return array $columns updated columns
 */
function llms_add_user_table_columns( $columns ) {
	$columns['llms-last-login']  = __( 'Last Login', 'lifterlms' );
	$columns['llms-memberships'] = __( 'Memberships', 'lifterlms' );
	return $columns;
}
add_filter( 'manage_users_columns', 'llms_add_user_table_columns' );

/**
 * Add data user data for custom column added by llms_add_user_table_columns
 *
 * @param     string $val         value of the field
 * @param     string $column_name "id" or name of the column
 * @param     int    $user_id        user_id for the row in the loop
 * @return    string              data to display on screen
 * @since     1.0.0
 * @version   3.16.14
 */
function llms_add_user_table_rows( $val, $column_name, $user_id ) {

	switch ( $column_name ) {

		/**
		 * Display user information for their last successful login
		 */
		case 'llms-last-login':
			$last = get_user_meta( $user_id, 'llms_last_login', true );
			if ( ! is_numeric( $last ) ) {
				$last = strtotime( $last );
			}
			$return = $last ? date_i18n( get_option( 'date_format', 'Y-m-d' ) . ' h:i:s a', $last ) : __( 'Never', 'lifterlms' );

			break;

		/**
		 * Display information related to user memberships
		 */
		case 'llms-memberships':
			$user = new LLMS_Person();
			$data = $user->get_user_memberships_data( $user_id );

			if ( ! empty( $data ) ) {

				$return = '';

				foreach ( $data as $membership_id => $obj ) {

					$return .= '<b>' . get_the_title( $membership_id ) . '</b><br>';

					$return .= '<em>Status</em>: ' . $obj['_status']->meta_value;

					if ( 'Enrolled' == $obj['_status']->meta_value ) {

						$return .= '<br><em>Start Date</em>: ' . date( get_option( 'date_format', 'Y-m-d' ), strtotime( $obj['_start_date']->updated_date ) );

						$membership_interval = get_post_meta( $membership_id, '_llms_expiration_interval', true );
						$membership_period   = get_post_meta( $membership_id, '_llms_expiration_period', true );

						// only display end date if exists.
						if ( $membership_interval ) {

							$end_date = strtotime( '+' . $membership_interval . $membership_period, strtotime( $obj['_start_date']->updated_date ) );

							$return .= '<br><em>End Date</em>: ' . date( get_option( 'date_format', 'Y-m-d' ), $end_date );
						}
					}
				}
			} else {

				return __( 'No memberships', 'lifterlms' );

			}

			break;

		default:
			$return = $val;
	}// End switch().

	return $return;

}
add_filter( 'manage_users_custom_column', 'llms_add_user_table_rows', 10, 3 );
