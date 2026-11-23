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
 * Retrieve the license status of the site.
 *
 * @since [version]
 *
 * @return string One of:
 *                `active`: at least one license key is saved on the site.
 *                `lapsed`: no license key is saved and LifterLMS.com reported a previously saved key as no longer valid.
 *                `none`: no license key is saved and no key is known to have lapsed.
 */
function llms_helper_get_license_status() {

	if ( llms_helper_options()->get_license_keys() ) {
		return 'active';
	}

	return llms_helper_options()->get_lapsed_license_keys() ? 'lapsed' : 'none';
}

/**
 * Retrieve the active add-ons that require a license and are not covered by a license key saved on the site.
 *
 * @since [version]
 *
 * @return LLMS_Helper_Add_On[] Array of add-ons. An empty array when the list of add-ons cannot be retrieved from LifterLMS.com.
 */
function llms_helper_get_unlicensed_add_ons() {

	$products = llms_get_add_ons();
	if ( is_wp_error( $products ) || empty( $products['items'] ) ) {
		return array();
	}

	// A theme add-on is also in use when a child theme of it is active.
	$parent_theme = get_template();

	$unlicensed = array();
	foreach ( $products['items'] as $product ) {
		$add_on = llms_get_add_on( $product );
		if ( ! $add_on->requires_license() || $add_on->is_licensed() ) {
			continue;
		}
		if ( $add_on->is_active() || ( 'theme' === $add_on->get_type() && $parent_theme === $add_on->get( 'update_file' ) ) ) {
			$unlicensed[] = $add_on;
		}
	}

	return $unlicensed;
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
