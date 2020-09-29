<?php
/**
 * Logging & Related Functions
 *
 * @package LifterLMS/Functions
 *
 * @since 3.0.0
 * @version 3.75
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the full path to the log file for a given log handle
 *
 * @since 3.0.0
 *
 * @param string $handle Log handle.
 * @return string
 */
function llms_get_log_path( $handle ) {

	return trailingslashit( LLMS_LOG_DIR ) . $handle . '-' . sanitize_file_name( wp_hash( $handle ) ) . '.log';

}

/**
 * Log arbitrary messages to a log file
 *
 * @since 1.0.0
 * @since 3.7.5 Unknown.
 *
 * @param mixed  $message Data to log.
 * @param string $handle  Allow creation of multiple log files by handle.
 * @return boolean
 */
function llms_log( $message, $handle = 'llms' ) {

	$ret = false;

	$fh = fopen( llms_get_log_path( $handle ), 'a' );

	// Open the file (creates it if it doesn't already exist).
	if ( $fh ) {

		// Print array or objects with `print_r`.
		if ( is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true );
		}

		$ret = fwrite( $fh, gmdate( 'Y-m-d H:i:s' ) . ' - ' . $message . "\n" );

		fclose( $fh );

	}

	return $ret ? true : false;

}

function llms_split_log( $handle ) {

	$file = llms_get_log_path( $handle );
	$size = file_exists( $file ) ? filesize( $file ) : 0;

	$maxsize = absint( apply_filters( 'llms_log_max_filesize', 5 ) ) * 1000 * 1000;

	if ( $size >= $maxsize ) {

		$copy = str_replace( '.log', sprintf( '-%d.log', time() ), $file );
		copy( $file, $copy );
		unlink( $file );

		return $copy;
 	}

 	return false;

}
