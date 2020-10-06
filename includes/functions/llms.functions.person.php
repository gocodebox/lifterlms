<?php
/**
 * Person Functions
 *
 * Functions for managing users in the LifterLMS system.
 *
 * @package LifterLMS/Functions
 *
 * @since 1.0.0
 * @version 4.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Determine whether or not a user can bypass enrollment, drip, and prerequisite restrictions
 *
 * @since 3.7.0
 * @since 3.9.0
 *
 * @param LLMS_Student|WP_User|int $user LLMS_Student, WP_User, or WP User ID, if none supplied get_current_user() will be used.
 * @return boolean
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
 * Checks LifterLMS user capabilities against an object
 *
 * @since 3.13.0
 * @since 4.5.0 Use strict array comparison.
 *
 * @param string $cap    Capability name.
 * @param int    $obj_id WP_Post or WP_User ID.
 * @return boolean
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
	 * @param boolean $grant Whether or not the requested capability is granted to the user.
	 * @param int     $obj_id WP_Post or WP_User ID.
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
 * @return boolean Whether or not the enrollment records have been successfully removed.
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
	 * @param boolean $disabled Whether or not the admin bar should be disabled.
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
 * @return boolean
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
 * Retrieve the minimum accepted password strength for student passwords
 *
 * @since 3.0.0
 *
 * @return string
 */
function llms_get_minimum_password_strength() {
	/**
	 * Filter  the minimum accepted password strength for student passwords
	 *
	 * @since 3.0.0
	 *
	 * @param string $strength Minimum required password strength setting.
	 */
	return apply_filters( 'llms_get_minimum_password_strength', get_option( 'lifterlms_registration_password_min_strength' ) );
}

/**
 * Retrieve the translated name of minimum accepted password strength for student passwords
 *
 * @since 3.0.0
 *
 * @return string
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
 * @since 3.8.0
 * @since 3.9.0 Unknown
 *
 * @param mixed $user  WP_User ID, instance of WP_User, or instance of any student class extending this class.
 * @return LLMS_Student|false LLMS_Student instance on success, false if user not found.
 */
function llms_get_student( $user = null ) {
	$student = new LLMS_Student( $user );
	return $student->exists() ? $student : false;
}

/**
 * Checks if the given object is complete for the given student
 *
 * @since Unknown
 * @since 3.3.1 Updated to use `LLMS_Student->is_enrolled()`.
 *
 * @see LLMS_Student->is_complete()
 *
 * @param int $user_id      WP User ID of the user.
 * @param int $object_id    WP Post ID of a Course, Section, or Lesson.
 * @param int $object_type  Type, either Course, Section, or Lesson.
 * @return boolean Returns `true` if complete, otherwise `false`.
 */
function llms_is_complete( $user_id, $object_id, $object_type = 'course' ) {
	$s = new LLMS_Student( $user_id );
	return $s->is_complete( $object_id, $object_type );
}

/**
 * Checks if user is currently enrolled in course
 *
 * @since Unknown
 * @since 3.25.0 Unknown.
 *
 * @see LLMS_Student->is_enrolled()
 *
 * @param int       $user_id    WP_User ID.
 * @param int|int[] $product_id WP Post ID of a Course, Lesson, or Membership or array of multiple IDs.
 * @param string    $relation   Comparator for enrollment check.
 *                              All = user must be enrolled in all $product_ids.
 *                              Any = user must be enrolled in at least one of the $product_ids.
 * @param bool      $use_cache  If true, returns cached data if available, if false will run a db query.
 * @return boolean
 */
function llms_is_user_enrolled( $user_id, $product_id, $relation = 'all', $use_cache = true ) {
	$student = new LLMS_Student( $user_id );
	return $student->is_enrolled( $product_id, $relation, $use_cache );
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
 * @param int    $object_type Object type [lesson|section|course|track].
 * @param string $trigger     String describing the event that triggered marking the object as complete.
 * @return boolean
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
 * @param int    $object_type Object type [lesson|section|course|track].
 * @param string $trigger     String describing the event that triggered marking the object as incomplete.
 * @return boolean
 */
function llms_mark_incomplete( $user_id, $object_id, $object_type, $trigger = 'unspecified' ) {
	$student = new LLMS_Student( $user_id );
	return $student->mark_incomplete( $object_id, $object_type, $trigger );
}

/**
 * Register a new user
 *
 * @since 3.0.0
 *
 * @see LLMS_Person_Handler::register()
 *
 * @param array  $data    array of registration data.
 * @param string $screen  the screen to be used for the validation template, accepts "registration" or "checkout".
 * @param bool   $signon  if true, signon the newly created user.
 *
 * @return int|WP_Error
 */
function llms_register_user( $data = array(), $screen = 'registration', $signon = true ) {
	return LLMS_Person_Handler::register( $data, $screen, $signon );
}

/**
 * Sets user auth cookie by id and records the date/time of the login in the usermeta table
 *
 * @since  Unknown
 * @since  3.0.0 Use `wp_set_current_user()` rather than overriding the global manually.
 * @since  3.36.0 Pass the `$remember` param to `wp_set_auth_cookie()`.
 * @deprecated 4.5.0 Use WP core methods such as `wp_signon()`, `wp_set_current_user()`, and/or `wp_set_auth_cookie()`.
 *
 * @param int  $user_id  WP_User ID.
 * @param bool $remember Whether to remember the user.
 * @return void
 */
function llms_set_person_auth_cookie( $user_id, $remember = false ) {
	llms_deprecated_function( 'llms_set_person_auth_cookie', '4.5.0' );
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, $remember );
	update_user_meta( $user_id, 'llms_last_login', current_time( 'mysql' ) );
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
 * @return boolean
 */
function llms_unenroll_student( $user_id, $product_id, $new_status = 'expired', $trigger = 'any' ) {
	$student = new LLMS_Student( $user_id );
	return $student->unenroll( $product_id, $trigger, $new_status );
}

/**
 * Perform validations according to $screen and updates the user
 *
 * @since 3.0.0
 * @since 3.7.0 Unknown.
 *
 * @see LLMS_Person_Handler::update()
 *
 * @param array  $data   Array of user data.
 * @param string $screen Screen to perform validations for, accepts "account" or "checkout".
 * @return int|WP_Error
 */
function llms_update_user( $data = array(), $screen = 'account' ) {
	return LLMS_Person_Handler::update( $data, $screen );
}
