<?php
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
 * @since [version]
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
	 * @since [version]
	 *
	 * @param bool  $is_spammer Whether the current visitor is a spammer.
	 * @param array $activity   The list of potential spam activity.
	 */
	return apply_filters( 'llms_is_spammer', $is_spammer, $activity );
}

/**
 * Get the list of potential spam activity.
 *
 * @since [version]
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

	$ip = preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip );
	$transient_key = 'llms_spam_activity_' . $ip;
	$activity = get_transient( $transient_key );
	if ( empty( $activity ) || ! is_array( $activity ) ) {
		$activity = [];
	}

	// Remove old items.
	$new_activity = [];
	$now = current_time( 'timestamp', true ); // UTC
	foreach( $activity as $item ) {
		// Determine whether this item is recent enough to include.
		if ( $item > $now-( LLMS_SPAM_ACTION_TIME_LIMIT ) ) {
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
 * @since [version]
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
	$now = current_time( 'timestamp', true ); // UTC
	array_unshift( $activity, $now );

	// If we have more than the limit, don't bother storing them.
	if ( count( $activity ) > LLMS_SPAM_ACTION_NUM_LIMIT ) {
		rsort( $activity );
		$activity = array_slice( $activity, 0, LLMS_SPAM_ACTION_NUM_LIMIT );
	}

	// Save to transient.
	$ip = preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip );
	$transient_key = 'llms_spam_activity_' . $ip;
	set_transient( $transient_key, $activity, (int) LLMS_SPAM_ACTION_TIME_LIMIT );

	return true;
}

/**
 * Clears all stored spam activity for an IP address.
 * Note that the llms_get_spam_activity function clears out old values
 * automatically, and this should only be used to completely clear the activity.
 *
 * @since [version]
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
 *
 * @since [version]
 * @param MemberOrder $morder The order object used at checkout. We ignore it.
 */
function llms_track_failed_checkouts_for_spam( $morder ) {
	// Bail if Spam Protection is disabled.
	$spam_protection = get_option("llms_spam_protection");	
	if ( empty( $spam_protection ) ) {
		return;
	}
	
	llms_track_spam_activity();
}
// NEED TO WRITE THE LLMS VERSION OF THIS
add_action( 'llms_checkout_processing_failed', 'llms_track_failed_checkouts_for_spam' );
add_action( 'llms_update_billing_failed', 'llms_track_failed_checkouts_for_spam' );

/**
 * Disable checkout and billing update forms for spammers.
 *
 *
 * @since [version]
 *
 * @param array $required_fields The list of required fields.
 *
 * @return array The list of required fields.
 */
function llms_disable_checkout_for_spammers( $required_fields ) {
	// Bail if Spam Protection is disabled.
	$spam_protection = get_option("llms_spam_protection");	
	if ( empty( $spam_protection ) ) {
		return $required_fields;
	}
	
	/*
    // NEED TO WRITE THE LLMS VERSION OF THIS
    if ( llms_was_checkout_form_submitted() && llms_is_spammer() ) {
		llms_setMessage( __( 'Suspicious activity detected. Try again in a few minutes.', 'lifterlms' ), 'llms_error' );
	}
    */

	return $required_fields;
}
// NEED TO WRITE THE LLMS VERSION OF THIS
add_filter( 'llms_required_billing_fields', 'llms_disable_checkout_for_spammers' );