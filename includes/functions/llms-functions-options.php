<?php
/**
 * Option/Settings related functions
 *
 * @package LifterLMS/Functions
 *
 * @since 3.29.0
 * @version 3.29.0
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

	// Return default.
	return $default;

}
