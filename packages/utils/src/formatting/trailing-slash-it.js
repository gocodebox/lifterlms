import { untrailingSlashIt } from './untrailing-slash-it';

/**
 * Adds a trailing forward slash to a given string.
 *
 * @since 1.0.0
 *
 * @param {string} str A string with or without a trailing forward slash.
 * @return {string} The original string with a trailing forward slash added.
 */
export function trailingSlashIt( str ) {
	return `${ untrailingSlashIt( str ) }/`;
}
