<?php
/**
 * Option/Settings related functions
 *
 * @package LifterLMS/Functions
 *
 * @since 3.29.0
 * @version 7.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve a "secure" option.
 *
 * Checks environment variables and then constant definitions
 *
 * @since 3.29.0
 *
 * @param string $secure_name Name of the option variable / constant.
 * @param mixed  $default     Optional default value used as a fallback.
 * @param string $db_name     Optional option name to fallback on if no constant or environment var is found.
 * @return mixed
 */
function llms_get_secure_option( $secure_name, $default = false, $db_name = '' ) {

	// Try an environment variable first.
	$val = getenv( $secure_name );

	if ( false !== $val ) {
		return $val;
	}

	// Try a constant.
	if ( defined( $secure_name ) ) {
		return constant( $secure_name );
	}

	if ( $db_name ) {
		return get_option( $db_name, $default );
	}

	// Return the default value.
	return $default;

}

/**
 * Determines if the given option name is stored in a "secure" manner, i.e. an environment variable or a constant.
 *
 * @since 7.0.0
 *
 * @param string $secure_name The name of the possibly secure option.
 * @return bool Returns `true` if the option is defined in an environment variable or a constant, else `false`.
 */
function llms_is_option_secure( $secure_name ) {

	// Sanity check for empty strings to prevent `getenv()` from returning ALL variables.
	if ( '' === $secure_name ) {
		return false;
	}

	/*
	 * Note: Do not store `false` values in an environment variable
	 * because `getenv()` returns `false` if the variable is not set.
	 */
	if ( false !== getenv( $secure_name ) ) {
		return true;
	}

	return defined( $secure_name );
}
