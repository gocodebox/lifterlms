<?php
/**
 * Deprecated Functions
 *
 * * * * * * * * * * * * * * * *
 * sometimes a thing must      *
 * be set aflame for from that *
 * black all new things come   *
 * * * * * * * * * * * * * * * *
 *
 * @package LifterLMS/Functions
 *
 * @since 3.29.0
 * @since [version] Moved `llms_create_new_person()` function which was deprecated at version 3.0.0.
 *                Deprecated `llms_get_minimum_password_strength() with no replacement`.
 *                Deprecated `llms_set_user_password_rest_key()` in favor of WP Core `get_password_reset_key()`.
 *                Deprecated `llms_verify_password_reset_key()` in favor of WP Core `check_password_reset_key()`.
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add product-id to WP query variables
 *
 * @param array $vars [WP query variables]
 * @return array $vars [WP query variables]
 *
 * @todo  deprecate?
 */
function llms_add_query_var_product_id( $vars ) {
	$vars[] = 'product-id';
	return $vars;
}
add_filter( 'query_vars', 'llms_add_query_var_product_id' );


/**
 * Sanitize text field
 *
 * @param  string $var [raw text field input]
 * @return string [clean string]
 *
 * @todo  deprecate b/c sanitize_text_field() already exists....
 */
function llms_clean( $var ) {
	return sanitize_text_field( $var );
}

/**
 * Deprecated. Creates new user.
 *
 * @since Unknown.
 * @deprecated 3.0.0 Use 'llms_register_user' instead.
 *
 * @param string $email User email.
 * @param string $email2 User verify email.
 * @param string $username Username.
 * @param string $firstname User first name.
 * @param string $lastname User last name.
 * @param string $password User password.
 * @param string $password2 User verify password.
 * @param string $billing_address_1 User billing address 1.
 * @param string $billing_address_2 User billing address 2.
 * @param string $billing_city User billing city.
 * @param string $billing_state User billing state.
 * @param string $billing_zip User billing zip.
 * @param string $billing_country User billing country.
 * @param string $agree_to_terms Agree to terms checkbox bool.
 * @return int
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
 * Schedule expired membership cron
 *
 * @return void
 */
function llms_expire_membership_schedule() {
	if ( ! wp_next_scheduled( 'llms_check_for_expired_memberships' ) ) {
		  wp_schedule_event( time(), 'daily', 'llms_check_for_expired_memberships' );
	}
}
add_action( 'wp', 'llms_expire_membership_schedule' );

/**
 * Expire Membership
 *
 * @return void
 */
function llms_expire_membership() {
	global $wpdb;

	// find all memberships wth an expiration date
	$args = array(
		'post_type'      => 'llms_membership',
		'posts_per_page' => 500,
		'meta_query'     => array(
			'key' => '_llms_expiration_interval',
		),
	);

	$posts = get_posts( $args );

	if ( empty( $posts ) ) {
		return;
	}

	foreach ( $posts as $post ) {

		// make sure interval and period exist before continuing.
		$interval = get_post_meta( $post->ID, '_llms_expiration_interval', true );
		$period   = get_post_meta( $post->ID, '_llms_expiration_period', true );

		if ( empty( $interval ) || empty( $period ) ) {
			continue;
		}

		// query postmeta table and find all users enrolled
		$table_name        = $wpdb->prefix . 'lifterlms_user_postmeta';
		$meta_key_status   = '_status';
		$meta_value_status = 'Enrolled';

		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . $table_name . ' WHERE post_id = %d AND meta_key = "%s" AND meta_value = %s ORDER BY updated_date DESC',
				$post->ID,
				$meta_key_status,
				$meta_value_status
			)
		);

		for ( $i = 0; $i < count( $results ); $i++ ) {
			$results[ $results[ $i ]->post_id ] = $results[ $i ];
			unset( $results[ $i ] );
		}

		$enrolled_users = $results;

		foreach ( $enrolled_users as $user ) {

			$user_id               = $user->user_id;
			$meta_key_start_date   = '_start_date';
			$meta_value_start_date = 'yes';

			$start_date = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT updated_date FROM ' . $table_name . ' WHERE user_id = %d AND post_id = %d AND meta_key = %s AND meta_value = %s ORDER BY updated_date DESC',
					$user_id,
					$post->ID,
					$meta_key_start_date,
					$meta_value_start_date
				)
			);

			// add expiration terms to start date
			$exp_date = date( 'Y-m-d', strtotime( date( 'Y-m-d', strtotime( $start_date[0]->updated_date ) ) . ' +' . $interval . ' ' . $period ) );

			// get current datetime
			$today = current_time( 'mysql' );
			$today = date( 'Y-m-d', strtotime( $today ) );

			// if a date parse causes exp date to be unmodified then return.
			if ( $exp_date == $start_date[0]->updated_date ) {
				LLMS_log( 'An error occurred modifying the date value. Function: llms_expire_membership, interval: ' . $interval . ' period: ' . $period );
				continue;
			}

			// compare expiration date to current date.
			if ( $exp_date < $today ) {
				$set_user_expired = array(
					'post_id'  => $post->ID,
					'user_id'  => $user_id,
					'meta_key' => '_status',
				);

				$status_update = array(
					'meta_value'   => 'Expired',
					'updated_date' => current_time( 'mysql' ),
				);

				// change enrolled to expired in user_postmeta
				$wpdb->update( $table_name, $status_update, $set_user_expired );

				// remove membership id from usermeta array
				$users_levels = get_user_meta( $user_id, '_llms_restricted_levels', true );
				if ( in_array( $post->ID, $users_levels ) ) {
					$key = array_search( $post->ID, $users_levels );
					unset( $users_levels[ $key ] );

					update_user_meta( $user_id, '_llms_restricted_levels', $users_levels );
				}
			}
		}// End foreach().
	}// End foreach().

}
add_action( 'llms_check_for_expired_memberships', 'llms_expire_membership' );

/**
 * Retrieve the minimum accepted password strength for student passwords
 *
 * @since 3.0.0
 * @deprecated [version]
 *
 * @return string
 */
function llms_get_minimum_password_strength() {
	llms_deprecated_function( 'llms_get_minimum_password_strength', '[version]' );
	return apply_filters( 'llms_get_minimum_password_strength', 'strong' );
}

/**
 * Generate a user password reset key, hash it, and store it in the database
 *
 * @since 3.8.0
 * @deprecated [version]
 *
 * @param int $user_id WP_User ID.
 * @return string
 */
function llms_set_user_password_rest_key( $user_id ) {

	llms_deprecated_function( 'llms_set_user_password_rest_key', '[version]', 'get_password_reset_key' );

	$user = get_user_by( 'ID', $user_id );
	$key = wp_generate_password( 20, false );
	do_action( 'retrieve_password_key', $user->user_login, $key );
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
 * Verifies a plain text password key for a user (by login) against the hashed key in the database
 *
 * @since 3.8.0
 * @deprecated [version]
 *
 * @param string $key Plain text activation key.
 * @param string $login User login.
 * @return boolean
 */
function llms_verify_password_reset_key( $key = '', $login = '' ) {

	llms_deprecated_function( 'llms_verify_password_reset_key', '[version]', 'check_password_reset_key' );

	$valid = check_password_reset_key( $key, $login );
	return is_wp_error( $valid ) ? false : true;

}
