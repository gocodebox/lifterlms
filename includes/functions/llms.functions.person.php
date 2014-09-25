<?php
/**
* Person Functions
*
* Functions for managing users in the lifterLMS system
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/

if ( ! defined( 'ABSPATH' ) ) exit;

function llms_disable_admin_bar( $show_admin_bar ) {
	if ( apply_filters( 'lifterlms_disable_admin_bar', get_option( 'lifterlms_lock_down_admin', 'yes' ) === 'yes' ) && ! ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_lifterlms' ) ) ) {
		$show_admin_bar = false;
	}

	return $show_admin_bar;
}
add_filter( 'show_admin_bar', 'llms_disable_admin_bar', 10, 1 );

function llms_create_new_person( $email, $username = '', $password = '' ) {

	// Check the e-mail address
	if ( empty( $email ) || ! is_email( $email ) ) {
		return new WP_Error( 'registration-error', __( 'Please provide a valid email address.', 'lifterlms' ) );
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

		if ( username_exists( $username ) )
			return new WP_Error( 'registration-error', __( 'An account is already registered with that username. Please choose another.', 'lifterlms' ) );
	} else {

		$username = sanitize_user( current( explode( '@', $email ) ) );

		// Ensure username is unique
		$append     = 1;
		$o_username = $username;

		while ( username_exists( $username ) ) {
			$username = $o_username . $append;
			$append ++;
		}
	}

	// Handle password creation
	if ( 'yes' === get_option( 'lifterlms_registration_generate_password' ) && empty( $password ) ) {
		$password = wp_generate_password();
		$password_generated = true;

	} elseif ( empty( $password ) ) {
		return new WP_Error( 'registration-error', __( 'Please enter an account password.', 'lifterlms' ) );

	} else {
		$password_generated = false;
	}

	// WP Validation
	$validation_errors = new WP_Error();

	do_action( 'lifterlms_register_post', $username, $email, $validation_errors );

	$validation_errors = apply_filters( 'lifterlms_registration_errors', $validation_errors, $username, $email );

	if ( $validation_errors->get_error_code() )
		return $validation_errors;

	$new_person_data = apply_filters( 'lifterlms_new_person_data', array(
		'user_login' => $username,
		'user_pass'  => $password,
		'user_email' => $email,
		'role'       => 'person'
	) );

	$person_id = wp_insert_user( $new_person_data );

	if ( is_wp_error( $person_id ) ) {
		return new WP_Error( 'registration-error', '<strong>' . __( 'ERROR', 'lifterlms' ) . '</strong>: ' . __( 'Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', 'lifterlms' ) );
	}

	do_action( 'lifterlms_created_person', $person_id, $new_person_data, $password_generated );

	return $person_id;
}

function llms_set_person_auth_cookie( $person_id ) {
	global $current_user;

	$current_user = get_user_by( 'id', $person_id );

	wp_set_auth_cookie( $person_id, true );
}

