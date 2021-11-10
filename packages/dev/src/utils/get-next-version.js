const semver = require( 'semver' );

/**
 * Determine the next version for a release given the current version and an increment level + preid.
 *
 * This function is a wrapper around `semver.inc()` with some modifications:
 *   + If a `preid` is provided, `pre` will automatically be added to `increment` (unless it's already been added).
 *   + When creating the first prerelease, eg 1.0.0 -> 2.0.0-beta.1, this function skips beta.0 and makes beta.1.
 *
 * @since 0.0.1
 * @since 0.0.2 Only add "pre" to the increment if it is already added.
 *
 * @param {string} version   Version to increment.
 * @param {string} increment Increment level: major, premajor, minor, preminor, patch, prepatch, or prerelease.
 * @param {string} preid     Prerelease identifier when using `pre*` increment levels. EG: "alpha", "beta", "rc".
 * @return {string} The incremented string.
 */
module.exports = ( version, increment, preid = null ) => {
	increment = preid && ! increment.startsWith( 'pre' ) ? `pre${ increment }` : increment;

	// When incrementing a prerelease we want to skip versions like "-beta.0" and go right to "-beta.1".
	if ( increment.includes( 'pre' ) ) {
		const prever = semver.inc( version, increment, preid );
		if ( 0 === semver.prerelease( prever ).reverse()[ 0 ] ) {
			version = prever;
			increment = 'prerelease';
		}
	}

	return semver.inc( version, increment, preid );
};
