/**
 * Remove trailing forward slash from a given string.
 *
 * @since 1.0.0
 *
 * @param {string} str A string with or without a trailing forward slash.
 * @return {string} The original string with the trailing forward slash removed.
 */
export function untrailingSlashIt( str ) {
	return str.endsWith( '/' ) ? str.slice( 0, -1 ) : str;
}
