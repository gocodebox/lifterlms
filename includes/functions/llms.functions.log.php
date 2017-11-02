<?php
/**
 * Logging & Related Functions
 *
 * @since   3.0.0
 * @version 3.0.0
 */


/**
 * Retrive the full path to the log file for a given log handle
 * @param    string  $handle  log handle
 * @return   string
 * @since    3.0.0
 * @version  3.0.0
 */
function llms_get_log_path( $handle ) {

	return trailingslashit( LLMS_LOG_DIR ) . $handle . '-' . sanitize_file_name( wp_hash( $handle ) ) . '.log';

}

/**
 * Log arbitrary messages to a log file
 * @param    mixed   $message   data to log
 * @param    string  $handle    allow creation of multiple log files by handle
 * @return   boolean
 * @since    1.0.0
 * @version  3.7.5
 */
function llms_log( $message, $handle = 'llms' ) {

	$r = false;

	$fh = fopen( llms_get_log_path( $handle ), 'a' );
	// open the file (creates it if it doesn't already exist)
	if ( $fh ) {

		// print array or objects with print_r
		if ( is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true );
		}

		$r = fwrite( $fh, date_i18n( 'm-d-Y @ H:i:s -' ) . ' ' . $message . "\n" );

		fclose( $fh );

	}

	return $r;

}
