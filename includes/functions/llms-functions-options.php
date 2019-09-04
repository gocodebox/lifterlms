<?php
/**
 * Option/Settings related functions
 *
 * @since    3.29.0
 * @version  3.29.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve a "secure" option.
 * Checks environment variables and then constant definitions
 *
 * @param   string $name Name of the variable.
 * @return  mixed
 * @since   3.29.0
 * @version 3.29.0
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
