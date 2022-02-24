import { cmp, coerce, parse } from 'semver';

import { getWPVersion } from './get-wp-version';

/**
 * Run a version compare against the currently tested version of WordPress.
 *
 * @since 3.2.0
 * @since [version] Added `majorMinorOnly` argument option.
 *
 * @param {string}  version        A version string.
 * @param {string}  comparator     A comparison string, eg ">=" or "<", etc...
 * @param {boolean} majorMinorOnly If `true`, only uses the major and minor versions of the current WP version.
 *                                 For example, version 5.9.1 will be shortened to 5.9 for comparison purposes.
 * @return {boolean} Comparison result.
 */
export function wpVersionCompare( version, comparator = '>=', majorMinorOnly = true ) {
	let wpVersion = parse( coerce( getWPVersion() ) );
	if ( majorMinorOnly ) {
		wpVersion = `${ wpVersion.major }.${ wpVersion.minor }.0`;
	}
	return cmp( coerce( version ), comparator, wpVersion );
}
