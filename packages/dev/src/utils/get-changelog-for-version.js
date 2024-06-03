const parseChangelogFile = require( './parse-changelog-file' );

/**
 * Retrieve a changelog for the given version.
 *
 * @since 0.0.1
 *
 * @param {string} ver  A semver string for the version to retrieve.
 * @param {string} file Changelog file path.
 * @return {Object|undefined} Returns the changelog version entry object or undefined if not found.
 */
module.exports = ( ver, file ) => {
	return parseChangelogFile( file ).find( ( { version } ) => ver === version );
};
