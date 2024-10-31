<?php
/**
 * Helper functions
 *
 * @package LifterLMS_Helper/Functions
 *
 * @since 2.2.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the LLMS_Helper_Options singleton
 *
 * @since 3.0.0
 *
 * @return LLMS_Helper_Options
 */
function llms_helper_options() {
	return LLMS_Helper_Options::instance();
}

/**
 * Obfuscate the license key for the front-end HTML.
 *
 * @param string $key License key to obfuscate.
 *
 * @return string
 */
function llms_obfuscate_license_key( $key ) {
	return substr( $key, 0, 7 ) . str_repeat( '*', strlen( $key ) - 14 ) . substr( $key, -7 );
}

/**
 * Retrieve an array of addons that are available via currently active License Keys
 *
 * @since 3.0.0
 *
 * @param bool $installable_only If true, only includes installable addons, if false, includes non-installable addons (like bundles).
 * @return array
 */
function llms_helper_get_available_add_ons( $installable_only = true ) {

	$ids = array();
	foreach ( llms_helper_options()->get_license_keys() as $key ) {
		if ( 1 == $key['status'] ) {
			$ids = array_merge( $ids, $key['addons'] );
		}
		if ( false === $installable_only ) {
			$ids[] = $key['product_id'];
		}
	}

	return array_unique( $ids );
}

/**
 * Deletes transient data related to plugin and theme updates
 *
 * @since 3.2.1
 *
 * @return void
 */
function llms_helper_flush_cache() {

	delete_transient( 'llms_products_api_result' );
	delete_site_transient( 'update_plugins' );
	delete_site_transient( 'update_themes' );
}
