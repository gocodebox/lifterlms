<?php
/**
 * Localization Functions
 * Currently only used to translate strings output by Javascript functions
 * More robust features will be added in the future
 *
 * @since  2.7.3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_l10n {

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
	 * @since 2.7.3
	 */
	public static function get_js_strings( $json = true ) {

		// add translatable strings to this array
		// alphabatize the array so we can quickly find strings
		// include references to the JS file where the string is used so we can cleanup if needed in the future
		$strings = array(

			/**
			 * file: _private/js/llms-ajax.js
			 */
			'Loading Question...' => __( 'Loading Question...', 'lifterlms' ),
			'Loading Quiz Results...' => __( 'Loading Quiz Results...', 'lifterlms' ),

			/**
			 * file: _private/js/app/llms-quiz.js
			 */
			'Hide Summary' => __( 'Hide Summary', 'lifterlms' ),
			'View Summary' => __( 'View Summary', 'lifterlms' ),
			'You must enter an answer to continue.' => __( 'You must enter an answer to continue.', 'lifterlms' ),
		);

		// allow filtering so extensions don't have to implement their own l10n functions
		$strings = apply_filters( 'lifterlms_js_l10n', $strings );

		if ( true === $json ) {

			return json_encode( $strings );

		} else {

			return $strings;

		}

	}

}
