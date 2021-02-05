<?php
/**
 * Update functions for version 4.14.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove orphan access plans
 *
 * @since [version]
 *
 * @return bool True if it needs to run again, false otherwise.
 */
function llms_update_4140_remove_orphan_access_plans() {

	$limit = 50;
	$skip  = get_transient( 'llms_4140_skipper_orphan_access_plans' );
	if ( ! $skip ) {
		$skip = 0;
	}
	set_transient( 'llms_4140_skipper_orphan_access_plans', $skip + $limit, DAY_IN_SECONDS );

	global $wpdb;

	$orphan_access_plans = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT pm.post_id AS apid
			FROM {$wpdb->postmeta} AS pm
			LEFT JOIN {$wpdb->posts} AS p
			ON pm.meta_value = p.ID
			WHERE pm.meta_key = '_llms_product_id'
			AND p.ID IS NULL
			ORDER BY apid ASC
			LIMIT %d, %d
		",
			$skip,
			$limit
		)
	); // db call ok; no-cache ok.

	// Finished.
	if ( empty( $orphan_access_plans ) ) {
		set_transient( 'llms_update_4140_remove_orphan_access_plans', 'complete', DAY_IN_SECONDS );
		return false;
	}

	foreach ( $orphan_access_plans as $orphan_access_plan_id ) {
		wp_delete_post( $orphan_access_plan_id );
	}

	// Needs to run again.
	return true;
}

/**
 * Update db version to 4.14.0
 *
 * @since [version]
 *
 * @return void|true True if it needs to run again, nothing if otherwise.
 */
function llms_update_4140_update_db_version() {
	if ( 'complete' !== get_transient( 'llms_update_4140_remove_orphan_access_plans' ) ) {
		// Needs to run again.
		return true;
	}
	LLMS_Install::update_db_version( '4.14.0' );
}
