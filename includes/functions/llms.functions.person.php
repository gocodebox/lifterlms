<?php
/**
* Person Functions
*
* Functions for managing users in the LifterLMS system
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

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
 * @return int $person_id 			 [ID of the user created]
 *
 * @version 3.0.0
 */
function llms_create_new_person( $email, $email2, $username = '', $firstname = '', $lastname = '', $password = '', $password2 = '', $billing_address_1 = '', $billing_address_2 = '', $billing_city = '', $billing_state = '', $billing_zip = '', $billing_country = '', $agree_to_terms = '', $phone = '' ) {
	llms_deprecated_function( 'llms_create_new_person', '3.0.0', 'llms_register_user' );
	return llms_register_user( array(
		'email_address' => $email,
		'email_address_confirm' => $email2,
		'user_login' => $username,
		'first_name' => $firstname,
		'last_name' => $lastname,
		'password' => $password,
		'password_confirm' => $password2,
		'llms_billing_address_1' => $billing_address_1,
		'llms_billing_address_2' => $billing_address_2,
		'llms_billing_city' => $billing_city,
		'llms_billing_state' => $billing_state,
		'llms_billing_zip' => $billing_zip,
		'llms_billing_country' => $billing_country,
		'llms_phone' => $phone,
		'terms' => $agree_to_terms,
	) );
}


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
 * Enroll a WordPress user in a course or membership
 * @param  int     $user_id    WP User ID
 * @param  int     $product_id WP Post ID of the Course or Membership
 * @param  string  $trigger    String describing the event that triggered the enrollment
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
 * Retrieve the minimum accepted password strength for student passwords
 * @return string
 * @since  3.0.0
 */
function llms_get_minimum_password_strength() {
	return apply_filters( 'llms_get_minimum_password_strength', get_option( 'lifterlms_registration_password_min_strength' ) );
}

/**
 * Retrieve the translated name of minimum accepted password strength for student passwords
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
 * Checks if user is currently enrolled in course
 *
 * @param  int $user_id    WP User ID of the user
 * @param  int $product_id WP Post ID of a Course, Lesson, or Membership
 *
 * @see  LLMS_Student->is_enrolled()
 *
 * @return bool    true if enrolled, false otherwise
 *
 * @version  3.0.0  updated to use LLMS_Studnet->is_enrolled()
 */
function llms_is_user_enrolled( $user_id, $product_id ) {
	$s = new LLMS_Student( $user_id );
	return $s->is_enrolled( $product_id );
}

/**
 * Checks if the given object is complete for the given student
 * @param  int $user_id      WP User ID of the user
 * @param  int $object_id    WP Post ID of a Course, Section, or Lesson
 * @param  int $object_type  Type, either Course, Section, or Lesson
 *
 * @see  LLMS_Student->is_complete()
 *
 * @return bool    true if complete, false otherwise
 *
 * @version  3.3.1  updated to use LLMS_Studnet->is_enrolled()
 */
function llms_is_complete( $user_id, $object_id, $object_type = 'course' ) {
	$s = new LLMS_Student( $user_id );
	return $s->is_complete( $object_id, $object_type );
}

/**
 * Mark a lesson, section, course, or track as complete
 * @param  int     $user_id   	 WP User ID
 * @param  int     $object_id  	 WP Post ID of the Lesson, Section, Track, or Course
 * @param  int     $object_type	 object type [lesson|section|course|track]
 * @param  string  $trigger    	 String describing the event that triggered marking the object as complete
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
 * @param  int     $user_id   	 WP User ID
 * @param  int     $object_id  	 WP Post ID of the Lesson, Section, Track, or Course
 * @param  int     $object_type	 object type [lesson|section|course|track]
 * @param  string  $trigger    	 String describing the event that triggered marking the object as incomplete
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
 * @param  int $person_id  ID of the user
 *
 * @return void
 * @version  3.0.0   now uses wp_set_current_user rather than overridding the global manually
 */
function llms_set_person_auth_cookie( $user_id, $remember = false ) {
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, false );
	update_user_meta( $user_id, 'llms_last_login', current_time( 'mysql' ) );
}

/**
 * Remove a LifterLMS Student from a course or membership
 * @param  int     $user_id     WP User ID
 * @param  int     $product_id  WP Post ID of the Course or Membership
 * @param  string  $new_status  the value to update the new status with after removal is complete
 * @param  string  $trigger     only remove the student if the original enrollment trigger matches the submitted value
 *                              "any" will remove regardless of enrollment trigger
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
 * Perform validations according to $screen and updates the user
 *
 * @see  LLMS_Person_Handler::update()
 *
 * @param  array  $data   array of user data
 * @param  string $screen  screen to perform validations for, accepts "update" or "checkout"
 * @return int|WP_Error
 *
 * @since  3.0.0
 */
function llms_update_update( $data = array(), $screen = 'update' ) {
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
			if ( ! is_numeric( $last ) ) {
				$last = strtotime( $last );
			}
			$return = $last ? date_i18n( get_option( 'date_format' , 'Y-m-d' ) . ' h:i:s a', $last ) : __( 'Never', 'lifterlms' );

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
