<?php
/**
 * Update functions for version 5.5.0.
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * For old users: explicitly limit by default the buddypress profile endpoints to those existing prior to 5.5.0.
 *
 * @since [version]
 *
 * @return bool True if it needs to run again, false otherwise.
 */
function llms_update_550_buddypress_profile_endpoints_bc() {

	if ( ! llms_parse_bool( get_option( 'llms_integration_buddypress_enabled', 'no' ) ) ) {
		return;
	}

	update_option(
		'llms_integration_buddypress_profile_endpoints',
		array(
			'view-courses',
			'view-memberships',
			'view-achievements',
			'view-certificates',
		)
	);

	return false;

}

/**
 * Update db version to 5.5.0.
 *
 * @since [version]
 *
 * @return void|true True if it needs to run again, nothing if otherwise.
 */
function llms_update_550_update_db_version() {
	LLMS_Install::update_db_version( '5.5.0' );
}
