const { interfaceVersion, resolve: originalResolve } = require( 'eslint-import-resolver-node' );

exports.interfaceVersion = interfaceVersion;

/**
 * Determine if an imported module / path is a WordPress package.
 *
 * @since 3.0.0
 *
 * @param {string} source Module name or path.
 * @return {boolean} Returns `true` if the module looks like a WordPress package.
 */
function isWordPress( source ) {
	return source.startsWith( '@wordpress/' );
}

exports.resolve = function( source, file, config ) {
	if ( isWordPress( source ) ) {
		return {
			found: true,
			path: null,
		};
	}

	return originalResolve( source, file, config );
};
