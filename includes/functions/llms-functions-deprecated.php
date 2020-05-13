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
 * @version 3.37.17
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
 * Generate a user password reset key, hash it, and store it in the database
 *
 * @since 3.8.0
 * @deprecated 3.37.17 Use WP core `get_password_reset_key()` instead.
 *
 * @param int $user_id WP_User ID.
 * @return string
 */
function llms_set_user_password_rest_key( $user_id ) {

	llms_deprecated_function( 'llms_set_user_password_rest_key()', '3.37.17', 'get_password_reset_key()' );

	$key = get_password_reset_key( get_user_by( 'ID', $user_id ) );

	/**
	 * For backwards compatibility:
	 * The original function had no error handling and would always return a string.
	 */
	return is_wp_error( $key ) ? '' : $key;

}

/**
 * Verifies a plain text password key for a user (by login) against the hashed key in the database
 *
 * @since 3.8.0
 * @deprecated 3.37.17 Use wp core `check_password_reset_key()` instead.
 *
 * @param string $key   Plain text activation key.
 * @param string $login User login (username).
 * @return boolean
 */
function llms_verify_password_reset_key( $key = '', $login = '' ) {

	llms_deprecated_function( 'llms_verify_password_reset_key()', '3.37.17', 'check_password_reset_key()' );

	$check = check_password_reset_key( $key, $login );

	/**
	 * Backwards compatibility:
	 * The original function returned a bool, `true` for "valid" and `false` for "invalid".
	 */
	return is_a( $check, 'WP_User' );

}
