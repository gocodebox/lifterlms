<?php
/**
 * Localization Functions
 * Currently only used to translate strings output by Javascript functions
 * More robust features will be added in the future
 *
 * @since   2.7.3
 * @version 3.17.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class LLMS_L10n {

	/**
	 * Create an object of translatable strings
	 *
	 * This object is added to the LLMS.l10n JS object
	 * the text used in JS *MUST* exactly match the string found in this object
	 * which is redundant but is the best and lightest weight solution
	 * I could dream up quickly
	 *
	 * @param  boolean $json if true, convert to JSON, otherwise return the array
	 * @return string|array
	 *
	 * @since   2.7.3
	 * @version 3.17.8
	 */
	public static function get_js_strings( $json = true ) {

		$strings = array();

		// add strings that should only be translated on the admin panel
		if ( is_admin() ) {

			$strings = apply_filters( 'lifterlms_js_l10n_admin', $strings );

		}

		// allow filtering so extensions don't have to implement their own l10n functions
		$strings = apply_filters( 'lifterlms_js_l10n', $strings );

		if ( true === $json ) {

			return json_encode( $strings );

		} else {

			return $strings;

		}

	}

}
