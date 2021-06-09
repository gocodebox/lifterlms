<?php
/**
 * Update functions for version 5.0.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Turn off autoload for accounting legacy options
 *
 * @since [version]
 *
 * @return bool True if it needs to run again, false otherwise.
 */
function llms_update_500_legacy_options_autoload_off() {

	global $wpdb;

	$legacy_options_to_stop_autoloading = array(
		'lifterlms_registration_generate_username',
		'lifterlms_registration_password_strength',
		'lifterlms_registration_password_min_strength',
	);

	$sql = "
		UPDATE {$wpdb->options} SET autoload='no'
		WHERE option_name IN (" . implode( ', ', array_fill( 0, count( $legacy_options_to_stop_autoloading ), '%s' ) ) . ')';

	$wpdb->query(
		$wpdb->prepare(
			$sql,
			$legacy_options_to_stop_autoloading
		)
	); // db call ok; no-cache ok.

	set_transient( 'llms_update_500_autoload_off_legacy_options', 'complete', DAY_IN_SECONDS );
	return false;

}

/**
 * Update db version to 5.0.0
 *
 * @since [version]]
 *
 * @return void|true True if it needs to run again, nothing if otherwise.
 */
function llms_update_500_update_db_version() {

	if ( 'complete' !== get_transient( 'llms_update_500_autoload_off_legacy_options' ) ) {
		// Needs to run again.
		return true;
	}

	LLMS_Install::update_db_version( '5.0.0' );
}
