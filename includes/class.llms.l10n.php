<?php
/**
 * Localization Functions
 *
 * Currently only used to translate strings output by Javascript functions.
 * More robust features will be added in the future.
 *
 * @package LifterLMS/Classes
 *
 * @since 2.7.3
 * @version 3.17.8
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_L10n
 *
 * @since 2.7.3
 * @since 3.17.8 Unknown.
 */
class LLMS_L10n {

	/**
	 * Create an object of translatable strings
	 *
	 * This object is added to the LLMS.l10n JS object.
	 *
	 * The text used in JS *MUST* exactly match the string found in this object.
	 *
	 * @since 2.7.3
	 * @since 3.17.8 Unknown.
	 *
	 * @param  boolean $json If `true`, convert to JSON, otherwise return the array.
	 * @return string|array If `$json` is `true`, returns a JSON string, otherwise an array.
	 */
	public static function get_js_strings( $json = true ) {

		$strings = array();

		// Add strings that should only be translated on the admin panel.
		if ( is_admin() ) {

			$strings = apply_filters( 'lifterlms_js_l10n_admin', $strings );

		}

		// Allow filtering so extensions don't have to implement their own l10n functions.
		$strings = apply_filters( 'lifterlms_js_l10n', $strings );

		if ( true === $json ) {

			return json_encode( $strings );

		} else {

			return $strings;

		}

	}

}
