<?php
/**
 * Update functions for version [version]
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since [version]
 * @version [version]
 */

namespace LLMS\Updates\Version_7_7_0;

defined( 'ABSPATH' ) || exit;

/**
 * Retrieves the DB version of the migration.
 *
 * @since [version]
 *
 * @access private
 *
 * @return string
 */
function _get_db_version() {
	return '7.7.0';
}

/**
 * Create a new option to enable Access Plan SKUs if any existing plans have a SKU set.
 *
 * @since [version]
 *
 * @return false
 */
function maybe_set_option_llms_access_plans_allow_skus() {
	// Find postmeta values for `_llms_plan_sku` that are not empty.
	global $wpdb;
	$found_plan_skus = $wpdb->get_results(
		"SELECT *
		 FROM {$wpdb->postmeta}
		 WHERE meta_key = '_llms_plan_sku'
		 AND meta_value != ''"
	);

	// If we found a plan with a SKU, update the option and return.
	if ( $found_plan_skus ) {
		update_option( 'llms_access_plans_allow_skus', 'yes' );
	}

	return false;

}

/**
 * Update db version to [version].
 *
 * @since [version]
 *
 * @return false.
 */
function update_db_version() {
	\LLMS_Install::update_db_version( _get_db_version() );
	return false;
}
