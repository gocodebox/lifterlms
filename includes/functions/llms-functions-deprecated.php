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
 * @version 6.0.0
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
 * Retrieve the minimum accepted password strength for student passwords
 *
 * @since 3.0.0
 * @deprecated 5.0.0 `llms_get_minimum_password_strength` is deprecated with no replacement.
 *
 * @return string
 */
function llms_get_minimum_password_strength() {
	llms_deprecated_function( 'llms_get_minimum_password_strength', '5.0.0' );
	return apply_filters( 'llms_get_minimum_password_strength', 'strong' );
}

/**
 * Backwards compatibility for the deprecated earned engagement content meta keys.
 *
 * This public function is intentionally marked as private to denote it's temporary lifespan. This function
 * will be removed in the next major release when the associated meta key is also fully removed.
 *
 * @since 6.0.0
 *
 * @access private
 *
 * @param string                                      $val Default value (an empty string).
 * @param LLMS_User_Certificate|LLMS_User_Achievement $obj User engagement object.
 * @return string
 */
function llms_earned_engagement_deprecated_content( $val, $obj ) {
	_llms_earned_engagement_deprecated_function( $obj, 'content', 'the WP_Post object property "post_content"' );
	return $obj->get( 'content' );
}

/**
 * Backwards compatibility for the deprecated earned engagement image meta keys.
 *
 * This public function is intentionally marked as private to denote it's temporary lifespan. This function
 * will be removed in the next major release when the associated meta key is also fully removed.
 *
 * @since 6.0.0
 *
 * @access private
 *
 * @param string                                      $val Default value (an empty string).
 * @param LLMS_User_Certificate|LLMS_User_Achievement $obj User engagement object.
 * @return int
 */
function llms_earned_engagement_deprecated_image( $val, $obj ) {
	_llms_earned_engagement_deprecated_function( $obj, 'image', 'the WP_Post meta key "_thumbnail_id"' );
	return get_post_thumbnail_id( $obj->get( 'id' ) );
}

/**
 * Backwards compatibility for the deprecated earned engagement template meta keys.
 *
 * This public function is intentionally marked as private to denote it's temporary lifespan. This function
 * will be removed in the next major release when the associated meta key is also fully removed.
 *
 * @since 6.0.0
 *
 * @access private
 *
 * @param string                                      $val Default value (an empty string).
 * @param LLMS_User_Certificate|LLMS_User_Achievement $obj User engagement object.
 * @return string
 */
function llms_earned_engagement_deprecated_template( $val, $obj ) {
	_llms_earned_engagement_deprecated_function( $obj, 'template', 'the WP_Post object property "post_parent"' );
	return $obj->get( 'parent' );
}

/**
 * Backwards compatibility for the deprecated earned engagement title meta keys.
 *
 * This public function is intentionally marked as private to denote it's temporary lifespan. This function
 * will be removed in the next major release when the associated meta key is also fully removed.
 *
 * @since 6.0.0
 *
 * @access private
 *
 * @param string                                      $val Default value (an empty string).
 * @param LLMS_User_Certificate|LLMS_User_Achievement $obj User engagement object.
 * @return string
 */
function llms_earned_engagement_deprecated_title( $val, $obj ) {
	_llms_earned_engagement_deprecated_function( $obj, 'title', 'the WP_Post object property "post_title"' );
	return $obj->get( 'title' );
}

/**
 * Handle earned engagement deprecated meta keys.
 *
 * Throws a deprecation warning and replaces the default value with the new value.
 *
 * This public function is intentionally marked as private to denote it's temporary lifespan. This function
 * will be removed in the next major release when the associated meta key is also fully removed.
 *
 * @since 6.0.0
 *
 * @access private
 *
 * @param string  $val    Meta value.
 * @param int     $obj_id Object ID.
 * @param string  $key    Meta key.
 * @return string
 */
function llms_engagement_handle_deprecated_meta_keys( $val, $obj_id, $key ) {

	$deprecated = array(
		'_llms_certificate_content' => 'llms_earned_engagement_deprecated_content',
		'_llms_achievement_content' => 'llms_earned_engagement_deprecated_content',

		'_llms_certificate_title' => 'llms_earned_engagement_deprecated_title',
		'_llms_achievement_title' => 'llms_earned_engagement_deprecated_title',

		'_llms_certificate_image' => 'llms_earned_engagement_deprecated_image',
		'_llms_achievement_image' => 'llms_earned_engagement_deprecated_image',

		'_llms_certificate_template' => 'llms_earned_engagement_deprecated_template',
		'_llms_achievement_template' => 'llms_earned_engagement_deprecated_template',
	);

	if ( array_key_exists( $key, $deprecated ) ) {

		$post_type = get_post_type( $obj_id );
		if ( in_array( $post_type, array( 'llms_my_achievement', 'llms_my_certificate' ), true ) ) {

			$class = 'LLMS_User_' . strtoupper( str_replace( 'llms_my_', '', $post_type ) );
			return $deprecated[ $key ]( $val, new $class( $obj_id ) );

		}
	}

	return $val;
}
add_filter( 'get_post_metadata', 'llms_engagement_handle_deprecated_meta_keys', 20, 3 );

/**
 * Throw a deprecated function warning for earned engagement meta deprecations.
 *
 * This public function is intentionally marked as private to denote it's temporary lifespan. This function
 * will be removed in the next major release when the associated meta key is also fully removed.
 *
 * @since 6.0.0
 *
 * @access private
 *
 * @param LLMS_User_Certificate|LLMS_User_Achievement $obj             User engagement object.
 * @param string                                      $meta_key        Deprecated meta key part (excluding the prefix and post type).
 * @param string                                      $replacement_msg The replacement message.
 * @return void
 */
function _llms_earned_engagement_deprecated_function( $obj, $meta_key, $replacement_msg ) {
	$classname = get_class( $obj );
	$keyname   = strtolower( str_replace( 'LLMS_User_', '', $classname ) ) . '_' . $meta_key;
	_deprecated_function( esc_html( "{$classname} meta key '{$keyname}'" ), '6.0.0', wp_kses_post( $replacement_msg ) );
}
