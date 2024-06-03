<?php
/**
 * Update functions for version 6.3.0.
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 6.3.0
 * @version 6.3.0
 */

namespace LLMS\Updates\Version_6_3_0;

defined( 'ABSPATH' ) || exit;

/**
 * For old users: explicitly limit by default the buddypress profile endpoints to those existing prior to 6.3.0.
 *
 * @since 6.3.0
 *
 * @return bool True if it needs to run again, false otherwise.
 */
function buddypress_profile_endpoints_bc() {

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
 * Update db version to 6.3.0.
 *
 * @since 6.3.0
 *
 * @return void|true True if it needs to run again, nothing if otherwise.
 */
function update_db_version() {
	\LLMS_Install::update_db_version( '6.3.0' );
}
