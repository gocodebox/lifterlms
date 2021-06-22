<?php
/**
 * Localization functions.
 *
 * @package LifterLMS/Functions/Locales
 *
 * @since 5.0.0
 * @version 5.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get countries address formatting and l10n information.
 *
 * Provides a list of language and address information for supported countries.
 *
 * @since 5.0.0
 *
 * @see languages/countries-address-info.php
 *
 * @return array
 */
function llms_get_countries_address_info() {

	$info = require LLMS_PLUGIN_DIR . 'languages/countries-address-info.php';

	/**
	 * Modify the default states list.
	 *
	 * @since 5.0.0
	 *
	 * @param array $info Multi-dimensional array. See "languages/address-countries-address-info.php" for details.
	 */
	return apply_filters( 'llms_countries_address_info', $info );

}

/**
 * Retrieve locale information for a specific country
 *
 * @since 5.0.0
 *
 * @param string $code Country code.
 * @return array
 */
function llms_get_country_address_info( $code ) {
	$all = llms_get_countries_address_info();
	return isset( $all[ $code ] ) ? $all[ $code ] : array();
}

/**
 * Retrieve the country name by country code
 *
 * @since 3.8.0
 *
 * @param string $code Country code.
 * @return string
 */
function llms_get_country_name( $code ) {
	$countries = get_lifterlms_countries();
	return isset( $countries[ $code ] ) ? $countries[ $code ] : $code;
}

/**
 * Retrieve a list of states for a given country.
 *
 * @since 5.0.0
 *
 * @param string $code Country code.
 * @return array
 */
function llms_get_country_states( $code ) {
	$all = llms_get_states();
	return isset( $all[ $code ] ) ? $all[ $code ] : array();
}

/**
 * Retrieve a list of states organized by country.
 *
 * @since 5.0.0
 *
 * @see languages/states.php
 *
 * @return array
 */
function llms_get_states() {

	$states = require LLMS_PLUGIN_DIR . 'languages/states.php';

	/**
	 * Modify the default states list.
	 *
	 * @since 1.0.0
	 *
	 * @param array $states Multi-dimensional array. See "languages/states.php" for details.
	 */
	return apply_filters( 'lifterlms_states', $states );

}

/**
 * Get Countries array for Select list
 *
 * @since 1.0.0
 * @since 3.28.2 Updated country list.
 * @since 5.0.0 Moved from llms.functions.currency.php.
 *               Use country list stored in file at languages/countries.php.
 *
 * @return array
 */
function get_lifterlms_countries() {

	$countries = require LLMS_PLUGIN_DIR . 'languages/countries.php';

	/**
	 * Modify the default countries list.
	 *
	 * @since 1.0.0
	 *
	 * @param array $countries Associative array of Country Code => Country Name.
	 */
	$countries = apply_filters( 'lifterlms_countries', $countries );

	return array_unique( $countries );

}

/**
 * Get the default LifterLMS country as configured in site settings.
 *
 * @since 3.0.0
 * @since 5.0.0 Moved from llms.functions.currency.php.
 *
 * @return string Country code.
 */
function get_lifterlms_country() {
	return apply_filters( 'lifterlms_country', get_option( 'lifterlms_country', 'US' ) );
}
