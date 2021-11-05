const { existsSync } = require( 'fs' );

/**
 * Retrieve a JS object for the specified JSON config file.
 *
 * Returns an empty object if the config file can't be found.
 *
 * @since 0.0.1
 *
 * @param {string} filename The JSON config filename, eg "composer" or "package".
 * @return {Object} The config file as a JS object.
 */
function getConfig( filename ) {
	const path = `${ process.cwd() }/${ filename }.json`;
	if ( existsSync( path ) ) {
		return require( path );
	}
	return {};
}

/**
 * Determines if the specified JSON config file exists.
 *
 * @since 0.0.1
 *
 * @param {string} filename The JSON config file name, eg "composer" or "package".
 * @return {boolean} Returns true if the config file exists, otherwise false.
 */
function hasConfig( filename ) {
	return Object.keys( getConfig( filename ) ).length >= 1;
}

module.exports = {
	getConfig,
	hasConfig,
};
