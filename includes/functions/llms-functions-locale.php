<?php
/**
 * Localization functions.
 *
 * @package LifterLMS/Functions/Locales
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve locale info for the specified country
 *
 * @since [version]
 *
 * @param string $code Country code.
 * @return array|false Array of locale info or `false` if the specified country code was not found.
 */
function llms_get_country_locale_info( $code ) {
	$info = llms_get_locale_info();
	return isset( $info[ $code ] ) ? $info[ $code ] : false;
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
 * @since [version]
 *
 * @param string $code Country code.
 * @return array
 */
function llms_get_country_states( $code ) {

	$all = llms_get_states();
	return isset( $all[ $code ] ) ? $all[ $code ] : array();

}

/**
 * Get country locale information.
 *
 * Provides a list of localization and formatting data by country.
 *
 * @since [version]
 *
 * @see languages/locale-info.php
 *
 * @return array
 */
function llms_get_locale_info() {

	$info = require LLMS_PLUGIN_DIR . 'languages/locale-info.php';

	/**
	 * Modify the default info list.
	 *
	 * @since [version]
	 *
	 * @param array $info Multi-dimensional array. See "languages/locale-info.php" for details.
	 */
	return apply_filters( 'lifterlms_locale_info', $info );

}

/**
 * Retrieve a list of states organized by country.
 *
 * @since [version]
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
	 * @since [version]
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
 * @since [version] Moved from llms.functions.currency.php.
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
	 * @param  array $countries Associative array of Country Code => Country Name.
	 */
	$countries = apply_filters( 'lifterlms_countries', $countries );

	return array_unique( $countries );

}

/**
 * Get the default LifterLMS country as configured in site settings.
 *
 * @since 3.0.0
 * @since [version] Moved from llms.functions.currency.php.
 *
 * @return string Country code.
 */
function get_lifterlms_country() {
	return apply_filters( 'lifterlms_country', get_option( 'lifterlms_country', 'US' ) );
}
