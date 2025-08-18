<?php

defined( 'ABSPATH' ) || exit;

/**
 * Code related to spam detection and prevention.
 */

// Constants. Define these in wp-config.php to override.
if ( ! defined( 'LLMS_SPAM_ACTION_NUM_LIMIT' ) ) {
	define( 'LLMS_SPAM_ACTION_NUM_LIMIT', 10 );
}
if ( ! defined( 'LLMS_SPAM_ACTION_TIME_LIMIT' ) ) {
	define( 'LLMS_SPAM_ACTION_TIME_LIMIT', 900 );  // in seconds
}

/**
 * Determine whether the current visitor a spammer.
 *
 * @since 9.0.0
 *
 * @return bool Whether the current visitor a spammer.
 */
function llms_is_spammer() {
	$is_spammer = false;

	$activity = llms_get_spam_activity();
	if ( false !== $activity && count( $activity ) >= LLMS_SPAM_ACTION_NUM_LIMIT ) {
		$is_spammer = true;
	}

	/**
	 * Allow filtering whether the current visitor is a spammer.
	 *
	 * @since 9.0.0
	 *
	 * @param bool  $is_spammer Whether the current visitor is a spammer.
	 * @param array $activity   The list of potential spam activity.
	 */
	return apply_filters( 'llms_is_spammer', $is_spammer, $activity );
}

/**
 * Get the list of potential spam activity.
 *
 * @since 9.0.0
 *
 * @param string|null $ip The IP address to get activity for, or leave as null to attempt to determine current IP address.
 *
 * @return array|false The list of potential spam activity if successful, or false if IP could not be determined.
 */
function llms_get_spam_activity( $ip = null ) {
	if ( empty( $ip ) ) {
		$ip = llms_get_ip_address();
	}

	// If we can't determine the IP, let's bail.
	if ( empty( $ip ) ) {
		return false;
	}

	$ip            = preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip );
	$transient_key = 'llms_spam_activity_' . $ip;
	$activity      = get_transient( $transient_key );
	if ( empty( $activity ) || ! is_array( $activity ) ) {
		$activity = array();
	}

	// Remove old items.
	$new_activity = array();
	$now          = time(); // UTC
	foreach ( $activity as $item ) {
		// Determine whether this item is recent enough to include.
		if ( $item > $now - ( absint( LLMS_SPAM_ACTION_TIME_LIMIT ) ) ) {
			$new_activity[] = $item;
		}
	}

	return $new_activity;
}

/**
 * Track spam activity.
 * When we hit a certain number, the spam flag will trigger.
 * For now we are only tracking credit card declines their timestamps.
 * IP address isn't a perfect way to track this, but it's the best we have.
 *
 * @since 9.0.0
 *
 * @param string|null $ip The IP address to track activity for, or leave as null to attempt to determine current IP address.
 *
 * @return bool True if the tracking of activity was successful, or false if IP could not be determined.
 */
function llms_track_spam_activity( $ip = null ) {
	if ( empty( $ip ) ) {
		$ip = llms_get_ip_address();
	}

	// If we can't determine the IP, let's bail.
	if ( empty( $ip ) ) {
		return false;
	}

	$activity = llms_get_spam_activity( $ip );
	$now      = time(); // UTC
	array_unshift( $activity, $now );

	// If we have more than the limit, don't bother storing them.
	if ( count( $activity ) > absint( LLMS_SPAM_ACTION_NUM_LIMIT ) ) {
		rsort( $activity );
		$activity = array_slice( $activity, 0, absint( LLMS_SPAM_ACTION_NUM_LIMIT ) );
	}

	// Save to transient.
	$ip            = preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip );
	$transient_key = 'llms_spam_activity_' . $ip;
	set_transient( $transient_key, $activity, (int) absint( LLMS_SPAM_ACTION_TIME_LIMIT ) );

	return true;
}

/**
 * Clears all stored spam activity for an IP address.
 * Note that the llms_get_spam_activity function clears out old values
 * automatically, and this should only be used to completely clear the activity.
 *
 * @since 9.0.0
 *
 * @param string|null $ip The IP address to clear activity for, or leave as null to attempt to determine current IP address.
 *
 * @return bool True if the clearing of activity was successful, or false if IP could not be determined.
 */
function llms_clear_spam_activity( $ip = null ) {
	if ( empty( $ip ) ) {
		$ip = llms_get_ip_address();
	}

	// If we can't determine the IP, let's bail.
	if ( empty( $ip ) ) {
		return false;
	}

	$transient_key = 'llms_spam_activity_' . $ip;

	delete_transient( $transient_key );

	return true;
}

/**
 * Track spam activity when checkouts or billing updates fail.
 * Hooked on wp so the $post global is set up.
 *
 * @since 9.0.0
 * @param MemberOrder $morder The order object used at checkout. We ignore it.
 */
function llms_track_failed_checkouts_for_spam() {
	// Bail if Spam Protection is disabled.
	if ( ! llms_is_spam_protection_enabled() ) {
		return;
	}

	// Bail if we're not on the LifterLMS checkout page.
	if ( is_admin() || ! is_llms_checkout() ) {
		return;
	}

	// Bail if there are no notices with type error.
	$notices = llms()->session->get( 'llms_notices', array() );
	$types   = array_keys( $notices );
	if ( ! in_array( 'error', $types ) ) {
		return;
	}

	llms_track_spam_activity();
}

/**
 * Determine whether spam protection is enabled.
 *
 * @since 9.0.0
 *
 * @return bool Whether spam protection is enabled.
 */
function llms_is_spam_protection_enabled() {
	return llms_parse_bool( get_option( 'lifterlms_spam_protection', 'yes' ) );
}

add_action( 'wp', 'llms_track_failed_checkouts_for_spam' );

/**
 * Disable checkout and billing update forms for spammers.
 *
 * @since 9.0.0
 *
 * @return mixed Truthy means stop checkout.
 */
function llms_disable_checkout_for_spammers() {
	// Bail if Spam Protection is disabled.
	if ( ! llms_is_spam_protection_enabled() ) {
		return false;
	}

	// Bail if the current visitor is not a spammer.
	if ( ! llms_is_spammer() ) {
		return false;
	}

	// Show a notice at LifterLMS checkout RE spam.
	$notice = __( 'Suspicious activity detected. Try again in a few minutes.', 'lifterlms' );
	llms_add_notice( $notice, 'error' );

	return true;
}
add_filter( 'llms_before_checkout_validation', 'llms_disable_checkout_for_spammers' );
