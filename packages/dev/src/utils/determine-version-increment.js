const
	semver = require( 'semver' ),
	getChangelogEntries = require( './get-changelog-entries' );

/**
 * Determine a version increment level.
 *
 * Uses existing changelog entries, the current version, and the requested preid to determine the increment to
 * be made.
 *
 * Finds the highest significance changelog entry and uses that significance for the increment.
 *
 * When a preid is passed and the current version is a prerelease, significance will be disregarded and "prerelease"
 * will be used for the increment.
 *
 * @since 0.0.1
 * @since 0.0.2 Added currentVersion and preid parameters.
 *              Add `pre` prefix when a `preid` is specified.
 *              Return `prerelease` when a `preid` is specified and `currentVersion` is already a prerelease.
 *
 * @param {string}  dir            Path to the directory where changelog entries are stored.
 * @param {string}  currentVersion Current project version.
 * @param {?string} preid          Preid identifier, eg "alpha", "beta", etc... And `null` when not requesting a prerelease.
 * @return {string} A version increment string.
 */
module.exports = ( dir, currentVersion, preid = null ) => {
	if ( preid && null !== semver.prerelease( currentVersion ) ) {
		return 'prerelease';
	}

	const
		logs = Array.from( new Set( getChangelogEntries( dir ).map( ( { significance } ) => significance ) ) ),
		increment = [ 'major', 'minor', 'patch' ].find( ( level ) => logs.includes( level ) ) || 'patch';

	return preid ? `pre${ increment }` : increment;
};
