import { cmp, coerce } from 'semver';

import { getWPVersion } from './get-wp-version';

/**
 * Run a version compare against the currently tested version of WordPress.
 *
 * @since [version]
 *
 * @param {string} version    A version string.
 * @param {string} comparator A comparison string, eg ">=" or "<", etc...
 * @return {boolean} Comparison result.
 */
export function wpVersionCompare( version, comparator = '>=' ) {
	return cmp( coerce( version ), comparator, coerce( getWPVersion() ) );
}
