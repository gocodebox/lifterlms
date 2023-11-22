<?php
/**
 * Person Functions
 *
 * Functions for managing users in the LifterLMS system.
 *
 * @package LifterLMS/Functions
 *
 * @since 1.0.0
 * @version 7.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determines whether or not a user can bypass enrollment, drip, and prerequisite restrictions.
 *
 * @since 3.7.0
 * @since 3.9.0 Unknown.
 * @since 5.9.0 Added optional second parameter `$post_id`.
 *
 * @param LLMS_Student|WP_User|int $user    LLMS_Student, WP_User, or WP User ID, if none supplied get_current_user() will be used.
 * @param integer                  $post_id A WP_Post ID to check permissions against. If supplied, in addition to the user's role
 *                                          being allowed to bypass the restrictions, the user must also have `edit_post` capabilities
 *                                          for the requested post.
 * @return bool
 */
function llms_can_user_bypass_restrictions( $user = null, $post_id = null ) {

	$user = llms_get_student( $user );

	if ( ! $user ) {
		return false;
	}

	$roles = get_option( 'llms_grant_site_access', '' );
	if ( ! $roles ) {
		$roles = array();
	}

	if ( ! array_intersect( $user->get_user()->roles, $roles ) ) {
		return false;
	}

	if ( $post_id && ! user_can( $user->get( 'id' ), 'edit_post', $post_id ) ) {
		return false;
	}

	return true;

}

/**
 * Checks LifterLMS user capabilities against an object
 *
 * @since 3.13.0
 * @since 4.5.0 Use strict array comparison.
 *
 * @param string $cap    Capability name.
 * @param int    $obj_id WP_Post or WP_User ID.
 * @return bool
 */
function llms_current_user_can( $cap, $obj_id = null ) {

	$caps  = LLMS_Roles::get_all_core_caps();
	$grant = false;

	if ( in_array( $cap, $caps, true ) ) {

		// If the user has the cap, maybe do some additional checks.
		if ( current_user_can( $cap ) ) {

			switch ( $cap ) {

				case 'view_lifterlms_reports':
					// Can view others reports so its okay.
					if ( current_user_can( 'view_others_lifterlms_reports' ) ) {
						$grant = true;

						// Can only view their own reports check if the student is their instructor.
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

				// No other checks needed.
				default:
					$grant = true;

			}
		}
	}

	/**
	 * Filters whether or not the current user can perform the requested action
	 *
	 * The dynamic portion of this hook, `$cap`, refers to the requested user capability.
	 *
	 * @since 3.13.0
	 *
	 * @param bool $grant  Whether or not the requested capability is granted to the user.
	 * @param int  $obj_id WP_Post or WP_User ID.
	 */
	return apply_filters( "llms_current_user_can_{$cap}", $grant, $obj_id );

}

/**
 * Delete LifterLMS Student's Enrollment record related to a given product.
 *
 * @since 3.33.0
 *
 * @see `LLMS_Student->delete_enrollment()` the class method wrapped by this function.
 *
 * @param int    $user_id    WP User ID.
 * @param int    $product_id WP Post ID of the Course or Membership.
 * @param string $trigger    Optional. Only delete the student enrollment if the original enrollment trigger matches the submitted value.
 *                           Passing "any" will remove regardless of enrollment trigger.
 * @return bool Whether or not the enrollment records have been successfully removed.
 */
function llms_delete_student_enrollment( $user_id, $product_id, $trigger = 'any' ) {
	$student = new LLMS_Student( $user_id );
	return $student->delete_enrollment( $product_id, $trigger );
}

/**
 * Disables admin bar on front end
 *
 * @since 1.0.0
 * @since 3.27.0 Unknown
 *
 * @param bool $show_admin_bar default value (true).
 * @return bool
 */
function llms_disable_admin_bar( $show_admin_bar ) {
	/**
	 * Filter whether or not the WP Admin Bar is disabled for users
	 *
	 * By default, the admin bar is disabled for all users except those with the `edit_posts` or `manage_lifterlms` capabilities.
	 *
	 * @since Unknown
	 *
	 * @param bool $disabled Whether or not the admin bar should be disabled.
	 */
	if ( apply_filters( 'lifterlms_disable_admin_bar', true ) && ! ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_lifterlms' ) ) ) {
		$show_admin_bar = false;
	}
	return $show_admin_bar;
}
add_filter( 'show_admin_bar', 'llms_disable_admin_bar', 10 );


/**
 * Enroll a WordPress user in a course or membership
 *
 * @since 2.2.3
 * @since 3.0.0 Added `$trigger` parameter.
 *
 * @see LLMS_Student->enroll() the class method wrapped by this function
 *
 * @param int    $user_id    WP User ID.
 * @param int    $product_id WP Post ID of the Course or Membership.
 * @param string $trigger    String describing the event that triggered the enrollment.
 * @return bool
 */
function llms_enroll_student( $user_id, $product_id, $trigger = 'unspecified' ) {
	$student = new LLMS_Student( $user_id );
	return $student->enroll( $product_id, $trigger );
}

/**
 * Get an LLMS_Instructor
 *
 * @since 3.13.0
 *
 * @param mixed $user WP_User ID, instance of WP_User, or instance of any instructor class extending this class.
 * @return LLMS_Instructor|false LLMS_Instructor instance on success, false if user not found
 */
function llms_get_instructor( $user = null ) {
	$student = new LLMS_Instructor( $user );
	return $student->exists() ? $student : false;
}

/**
 * Retrieve the translated name of minimum accepted password strength for student passwords
 *
 * @since 3.0.0
 * @since 5.0.0 Remove database call to deprecated option and add the $strength parameter.
 *
 * @param string $strength Optional. Password strength value to translate. Default is 'strong'.
 * @return string
 */
function llms_get_minimum_password_strength_name( $strength = 'strong' ) {

	$opts = array(
		'strong'    => __( 'strong', 'lifterlms' ),
		'medium'    => __( 'medium', 'lifterlms' ),
		'weak'      => __( 'weak', 'lifterlms' ),
		'very-weak' => __( 'very weak', 'lifterlms' ),
	);

	$name = isset( $opts[ $strength ] ) ? $opts[ $strength ] : $strength;

	/**
	 * Filter the name of the password strength
	 *
	 * The dynamic portion of this hook, `$strength`, can be either "strong", "medium", "weak" or "very-weak".
	 *
	 * @since 5.0.0
	 *
	 * @param $string $name Translated name of the password strength value.
	 */
	return apply_filters( 'llms_get_minimum_password_strength_name_' . $strength, $name );

}

/**
 * Get an LLMS_Student.
 *
 * @since 3.8.0
 * @since 3.9.0 Unknown
 * @since 7.1.0 Added the `$autoload` parameter.
 *
 * @param mixed $user     WP_User ID, instance of WP_User, or instance of any student class extending this class.
 * @param bool  $autoload If `true` and `$user` input is empty, the user will be loaded from `get_current_user_id()`.
 *                        If `$user` is not empty then this parameter has no impact.
 * @return LLMS_Student|false LLMS_Student instance on success, false if user not found.
 */
function llms_get_student( $user = null, $autoload = true ) {
	$student = new LLMS_Student( $user, $autoload );
	return $student->exists() ? $student : false;
}

/**
 * Retrieve a list of disallowed usernames.
 *
 * @since 5.0.0
 * @since 6.0.0 Removed the deprecated `llms_usernames_blacklist` filter hook.
 *
 * @return string[]
 */
function llms_get_usernames_blocklist() {

	$list = array( 'admin', 'test', 'administrator', 'password', 'testing' );

	/**
	 * Modify the list of disallowed usernames
	 *
	 * If a user attempts to create a new account with any username found in this list they will receive an error and will not
	 * be able to register the account.
	 *
	 * @since 5.0.0
	 *
	 * @param string[] $list List of banned usernames.
	 */
	return apply_filters( 'llms_usernames_blocklist', $list );

}

/**
 * Checks if user is currently enrolled in cours
 *
 * @since Unknown
 * @since 3.3.1 Updated to use `LLMS_Student->is_enrolled()`.
 *
 * @see LLMS_Student->is_complete()
 *
 * @param int $user_id      WP User ID of the user.
 * @param int $object_id    WP Post ID of a Course, Section, or Lesson.
 * @param int $object_type  Type, either Course, Section, or Lesson.
 * @return bool Returns `true` if complete, otherwise `false`.
 */
function llms_is_complete( $user_id, $object_id, $object_type = 'course' ) {
	$s = new LLMS_Student( $user_id );
	return $s->is_complete( $object_id, $object_type );
}

/**
 * Checks if user is currently enrolled courses, sections, lessons, or memberships.
 *
 * @since Unknown
 * @since 3.25.0 Unknown.
 * @since 7.1.0 From now on this function will always return false for non existing users,
 *               e.g. deleted users.
 *
 * @see LLMS_Student->is_enrolled()
 *
 * @param int       $user_id    WP_User ID.
 * @param int|int[] $product_id WP Post ID of a Course, Lesson, or Membership or array of multiple IDs.
 * @param string    $relation   Comparator for enrollment check.
 *                              All = user must be enrolled in all $product_ids.
 *                              Any = user must be enrolled in at least one of the $product_ids.
 * @param bool      $use_cache  If true, returns cached data if available, if false will run a db query.
 * @return bool
 */
function llms_is_user_enrolled( $user_id, $product_id, $relation = 'all', $use_cache = true ) {
	$student = new LLMS_Student( $user_id );
	return $student->exists() ?
		$student->is_enrolled( $product_id, $relation, $use_cache ) :
		false;
}

/**
 * Mark a lesson, section, course, or track as complete
 *
 * @since 3.3.1
 *
 * @see LLMS_Student->mark_complete()
 *
 * @param int    $user_id     WP User ID.
 * @param int    $object_id   WP Post ID of the Lesson, Section, Track, or Course.
 * @param string $object_type Object type [lesson|section|course|track].
 * @param string $trigger     String describing the event that triggered marking the object as complete.
 * @return bool
 */
function llms_mark_complete( $user_id, $object_id, $object_type, $trigger = 'unspecified' ) {
	$student = new LLMS_Student( $user_id );
	return $student->mark_complete( $object_id, $object_type, $trigger );
}

/**
 * Mark a lesson, section, course, or track as incomplete
 *
 * @since 3.5.0
 *
 * @see LLMS_Student->mark_incomplete()
 *
 * @param int    $user_id     WP User ID.
 * @param int    $object_id   WP Post ID of the Lesson, Section, Track, or Course.
 * @param string $object_type Object type [lesson|section|course|track].
 * @param string $trigger     String describing the event that triggered marking the object as incomplete.
 * @return bool
 */
function llms_mark_incomplete( $user_id, $object_id, $object_type, $trigger = 'unspecified' ) {
	$student = new LLMS_Student( $user_id );
	return $student->mark_incomplete( $object_id, $object_type, $trigger );
}

/**
 * Mark an object as favorite.
 *
 * @since 7.5.0
 *
 * @see LLMS_Student->mark_favorite()
 *
 * @param int    $user_id     WP User ID.
 * @param int    $object_id   WP Post ID of the object to mark/unmark as favorite.
 * @param string $object_type The object type, currently only 'lesson'.
 * @return bool
 */
function llms_mark_favorite( $user_id, $object_id, $object_type ) {
	$student = new LLMS_Student( $user_id );
	return $student->mark_favorite( $object_id, $object_type );
}

/**
 * Mark a lesson as unfavorite.
 *
 * @since 7.5.0
 *
 * @see LLMS_Student->mark_unfavorite()
 *
 * @param int    $user_id     WP User ID.
 * @param int    $object_id   WP Post ID of the object to mark/unmark as favorite.
 * @param string $object_type The object type, currently only 'lesson'.
 * @return bool
 */
function llms_mark_unfavorite( $user_id, $object_id, $object_type ) {
	$student = new LLMS_Student( $user_id );
	return $student->mark_unfavorite( $object_id, $object_type );
}

/**
 * Parses the password reset cookie.
 *
 * This is the cookie set when a user uses the password reset link found in a reset password email. The query string
 * vars in the link (user login and reset key) are parsed and stored in this cookie.
 *
 * @since 5.0.0
 * @since 5.1.2 Fixed typos in error messages.
 *
 * @return array|WP_Error On success, returns an associative array containing the keys "key" and "login", on error
 *                        returns a WP_Error.
 */
function llms_parse_password_reset_cookie() {

	if ( ! isset( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ) ) {
		return new WP_Error( 'llms_password_reset_no_cookie', __( 'The password reset key could not be found. Please reset your password again if needed.', 'lifterlms' ) );
	}

	$parsed = array_map( 'sanitize_text_field', explode( ':', wp_unslash( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ), 2 ) );  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( 2 !== count( $parsed ) ) {
		return new WP_Error( 'llms_password_reset_invalid_cookie', __( 'The password reset key is in an invalid format. Please reset your password again if needed.', 'lifterlms' ) );
	}

	$uid = $parsed[0];
	$key = $parsed[1];

	$user  = get_user_by( 'ID', $uid );
	$login = $user ? $user->user_login : '';
	$user  = check_password_reset_key( $key, $login );

	if ( is_wp_error( $user ) ) {
		// Error code is either "llms_password_reset_invalid_key" or "llms_password_reset_expired_key".
		return new WP_Error( sprintf( 'llms_password_reset_%s', $user->get_error_code() ), __( 'This password reset key is invalid or has already been used. Please reset your password again if needed.', 'lifterlms' ) );
	}

	// Success.
	return compact( 'key', 'login' );

}

/**
 * Register a new user
 *
 * @since 3.0.0
 * @since 5.0.0 Use `LLMS_Form_Handler()` for registration.
 *
 * @param array  $data   Array of registration data.
 * @param string $screen The screen to be used for the validation template, accepts "registration" or "checkout".
 * @param bool   $signon If true, signon the newly created user.
 * @param array  $args   Additional arguments passed to the short-circuit filter.
 * @return int|WP_Error
 */
function llms_register_user( $data = array(), $screen = 'registration', $signon = true, $args = array() ) {

	$user_id = LLMS_Form_Handler::instance()->submit( $data, $screen, $args );

	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	// Signon.
	if ( $signon && ! empty( $data['password'] ) ) {

		$user = get_user_by( 'ID', $user_id );

		/**
		 * Filters whether or not a new user should be "remembered" when signing on during account creation
		 *
		 * @since 5.0.0
		 *
		 * @param bool    $remember If `true` (default), the user signon will be set to "remember".
		 * @param string  $screen   Current validation template, either "registration" or "checkout".
		 * @param WP_User $user     User object for the newly registered user.
		 */
		$remember = apply_filters( 'llms_user_registration_remember', true, $screen, $user );

		wp_signon(
			array(
				'user_login'    => $user->user_login,
				'user_password' => $data['password'],
				'remember'      => $remember,
			),
			is_ssl()
		);

	}

	return $user_id;

}

/**
 * Set or unset a user's password reset cookie.
 *
 * @since 5.0.0
 *
 * @param string $val Cookie value.
 * @return bool
 */
function llms_set_password_reset_cookie( $val = '' ) {

	$cookie  = sprintf( 'wp-resetpass-%s', COOKIEHASH );
	$expires = $val ? 0 : time() - YEAR_IN_SECONDS;
	$path    = isset( $_SERVER['REQUEST_URI'] ) ? current( explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	return llms_setcookie( $cookie, $val, $expires, $path, COOKIE_DOMAIN, is_ssl(), true );

}

/**
 * Set/Update user login time
 *
 * @since 4.5.0
 *
 * @param string  $user_login Username.
 * @param WP_User $user       WP_User object of the logged-in user.
 * @return void
 */
function llms_set_user_login_time( $user_login, $user ) {
	update_user_meta( $user->ID, 'llms_last_login', llms_current_time( 'mysql' ) );
}
add_action( 'wp_login', 'llms_set_user_login_time', 10, 2 );

/**
 * Remove a LifterLMS Student from a course or membership
 *
 * @since 3.0.0
 *
 * @see LLMS_Student->unenroll() the class method wrapped by this function
 *
 * @param int    $user_id     WP User ID.
 * @param int    $product_id  WP Post ID of the Course or Membership.
 * @param string $new_status  The value to update the new status with after removal is complete.
 * @param string $trigger     Only remove the student if the original enrollment trigger matches the submitted value.
 *                            Passing "any" will remove regardless of enrollment trigger.
 * @return bool
 */
function llms_unenroll_student( $user_id, $product_id, $new_status = 'expired', $trigger = 'any' ) {
	$student = new LLMS_Student( $user_id );
	return $student->unenroll( $product_id, $trigger, $new_status );
}

/**
 * Update a user.
 *
 * @since 3.0.0
 * @since 3.7.0 Unknown.
 * @since 5.0.0 Updated to utilize LLMS_Form_Handler class.
 *
 * @param array  $data Array of user data.
 * @param string $location (Optional) screen to perform validations for, accepts "account" or "checkout". Default value: 'account'
 * @param array  $args   Additional arguments passed to the short-circuit filter.
 * @return int|WP_Error WP_User ID on success or error object on failure.
 */
function llms_update_user( $data = array(), $location = 'account', $args = array() ) {
	return LLMS_Form_Handler::instance()->submit( $data, $location, $args );
}

/**
 * Performs validations for submitted user data.
 *
 * The related functions `llms_update_user()` and `llms_register_user()` automatically perform validations so this method
 * should only be used if you wish to test updates / registration without actually performing the registration or update action.
 *
 * @since 7.0.0
 *
 * @param array  $data     Array of user data.
 * @param string $location (Optional) screen to perform validations for, accepts "account" or "checkout". Default value: 'checkout'
 * @param array  $args     Additional arguments passed to the short-circuit filter.
 * @return bool|WP_Error Returns `true` if the user data passes validation, otherwise returns an error object describing
 *                       the validation issues.
 */
function llms_validate_user( $data = array(), $location = 'checkout', $args = array() ) {
	$args['validate_only'] = true;
	return LLMS_Form_Handler::instance()->submit( $data, $location, $args );
}
