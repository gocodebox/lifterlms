<?php
/**
 * Logging & Related Functions
 *
 * @package LifterLMS/Functions
 *
 * @since 3.0.0
 * @version 4.12.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Copy a log file that is greater than or equal to the max allowed log file size
 *
 * If the log file's size is larger than the maximum allowed log file size (5MB) it will rename the log, adding
 * the current timestamp as a suffix and `.bk` to the extension.
 *
 * Future logs to the same file will result in a new logfile being created, ensuring that log files never grow
 * too large which could cause performance issues during reads and writes.
 *
 * @since 4.5.0
 *
 * @see llms_backup_logs()
 *
 * @param string $handle Log file handle.
 * @return null|boolean|string Returns `null` if the log file is not larger than the max size, `false` if an error is encountered,
 *                             and the new log file path on success.
 */
function llms_backup_log( $handle ) {

	$file = llms_get_log_path( $handle );
	$size = file_exists( $file ) ? filesize( $file ) : 0;

	/**
	 * Filter the max filesize of a log file before the log is backed up
	 *
	 * The value of this filter, `$maxsize` is an integer representing the maximum number of megabytes
	 * a file can be before it is split.
	 *
	 * @since 4.5.0
	 *
	 * @param int $maxsize Maximum file size (in MB). The default value is `5` (5MB).
	 */
	$maxsize = absint( apply_filters( 'llms_log_max_filesize', 5 ) ) * 1000 * 1000;

	if ( $size >= $maxsize ) {

		$copy = str_replace( '.log', sprintf( '-%d.log.bk', time() ), $file );

		/**
		 * Filter the name of a log file copy that's being backed up because it's reached the maximum allowed size
		 *
		 * While it is possible to change the extension of the log file (`.log.bk`), it is not recommended. The cron
		 * which creates copies filters out `.log.bk` so that it doesn't scan backups and attempt to split them
		 * again (infinitely).
		 *
		 * @since 4.5.0
		 *
		 * @param string $copy   Full path for the copy log file (the backup).
		 * @param string $file   Full path for the original log file.
		 * @param string $handle Log file handle.
		 */
		$copy = apply_filters( 'llms_log_split_file_name', $copy, $file, $handle );
		if ( rename( $file, $copy ) ) {

			/**
			 * Action triggered immediately following the creation of a logfile backup.
			 *
			 * @since 4.5.0
			 *
			 * @param string $copy   Full path for the copy log file (the backup).
			 * @param string $file   Full path for the original log file.
			 * @param string $handle Log file handle.
			 */
			do_action( 'llms_log_file_backup_created', $copy, $file, $handle );

			return $copy;
		}
	}

	return null;

}

/**
 * Backup all log files in the LifterLMS log directory
 *
 * This function scans the `LLMS_LOG_DIR` and passes each log file to `llms_backup_log()` to
 * create backups of each log file.
 *
 * It does not include logs with the `.log.bk` extension as those logs are logs created by this process
 * and don't need to be scanned again.
 *
 * @since 4.5.0
 *
 * @see llms_backup_log()
 *
 * @return void
 */
function llms_backup_logs() {

	foreach ( glob( LLMS_LOG_DIR . '*.log' ) as $file ) {

		// Get the handle from the file path.
		$parts = explode( '-', basename( $file, '.log' ) );
		if ( $parts ) {
			llms_backup_log( implode( '-', array_slice( $parts, 0, -1 ) ) );
		}
	}

}
add_action( 'llms_backup_logs', 'llms_backup_logs' );


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

	/**
	 * Filter a log message before it's written to the logger.
	 *
	 * @since 4.12.0
	 *
	 * @param mixed  $message Data to log.
	 * @param string $handle  Allow creation of multiple log files by handle.
	 */
	$message = apply_filters( 'llms_log_message', $message, $handle );

	$ret = false;
	$fh  = fopen( llms_get_log_path( $handle ), 'a' );

	// Open the file (creates it if it doesn't already exist).
	if ( $fh ) {

		// Print array or objects with `print_r`.
		if ( is_array( $message ) || is_object( $message ) ) {
			$message = print_r( $message, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- This is intentional.
		}

		$ret = fwrite( $fh, gmdate( 'Y-m-d H:i:s' ) . ' - ' . $message . "\n" );

		fclose( $fh );

	}

	return $ret ? true : false;

}
