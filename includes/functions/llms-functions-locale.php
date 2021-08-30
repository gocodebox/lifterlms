<?php
/**
 * Localization functions.
 *
 * @package LifterLMS/Functions/Locales
 *
 * @since 5.0.0
 * @version 5.3.0
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
 * Retrieve the translated (and optionally pluralized) name for a given time period string
 *
 * This is used primarily to display time period data which is stored directly in the database. When displaying
 * to a user, we wish to ensure that the translated version is displayed instead of the raw and untranslated value
 * stored in the database.
 *
 * @since 5.3.0
 *
 * @param string  $period A time period string, accepts "day", "week", "month", or "year".
 * @param integer $length The length of the period, passed to `_n()` and used for pluralization. Defaults to `1`.
 * @return string The translated and pluralized time period string. Returns the submitted string for unsupported strings.
 */
function llms_get_time_period_l10n( $period, $length = 1 ) {

	switch ( strtolower( $period ) ) {

		case 'day':
			$period = _n( 'day', 'days', $length, 'lifterlms' );
			break;

		case 'week':
			$period = _n( 'week', 'weeks', $length, 'lifterlms' );
			break;

		case 'month':
			$period = _n( 'month', 'months', $length, 'lifterlms' );
			break;

		case 'year':
			$period = _n( 'year', 'years', $length, 'lifterlms' );
			break;

	}

	/**
	 * Filter the translated name for a given time period string.
	 *
	 * @since 5.3.0
	 *
	 * @param string $period Translated period name.
	 * @param int    $length Period length, used for pluralization.
	 */
	return apply_filters( 'llms_time_period_l10n', $period, $length );

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
