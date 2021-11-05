const getCurrentVersion = require( './get-current-version' ),
	getProjectSlug = require( './get-project-slug' );

/**
 * Retrieve the filename of the project's archive/distribution zip file.
 *
 * @since 0.0.1
 *
 * @param {?string} version The version number. If not supplied uses the current version.
 * @return {string} The archive filename.
 */
module.exports = ( version = null ) => {
	version = version ? version : getCurrentVersion();
	return `${ getProjectSlug() }-${ version }.zip`;
};
