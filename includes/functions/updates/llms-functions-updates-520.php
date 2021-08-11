<?php
/**
 * Update functions for version 5.2.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 5.2.0
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Explicitly set no subscribers for the new upcoming payment reminder notification
 *
 * @since 5.2.0
 *
 * @return bool True if it needs to run again, false otherwise.
 */
function llms_update_520_upcoming_reminder_notification_backward_compat() {

	$subscribers_for_type = array(
		'email' => array(
			'student',
		),
		'basic' => array(
			'student',
			'author',
			'custom',
		),
	);

	foreach ( $subscribers_for_type as $type => $subscribers ) {
		add_option( "llms_notification_upcoming_payment_reminder_{$type}_subscribers", array_fill_keys( $subscribers, 'no' ) );
	}

	return false;

}

/**
 * Update db version to 5.2.0
 *
 * @since 5.2.0
 *
 * @return void|true True if it needs to run again, nothing if otherwise.
 */
function llms_update_520_update_db_version() {
	LLMS_Install::update_db_version( '5.2.0' );
}
