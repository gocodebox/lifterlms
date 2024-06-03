const ChangelogEntry = require( './changelog-entry' ),
	{ readdirSync, readFileSync, existsSync } = require( 'fs' ),
	path = require( 'path' ),
	YAML = require( 'yaml' );

/**
 * Retrieve all changelog entry files from the specified directory.
 *
 * This will attempt to parse all .y[a]ml files found in the specified directory.
 *
 * @since 0.0.1
 *
 * @param {string} dir Path to the directory.
 * @return {ChangelogEntry[]} Array of changelog entry objects.
 */
module.exports = ( dir ) => {
	const res = [];

	if ( ! existsSync( dir ) ) {
		return res;
	}

	readdirSync( dir ).forEach( ( file ) => {
		// Only parse valid changelog files.
		if ( ! file.includes( '.yml' ) && ! file.includes( '.yaml' ) ) {
			return;
		}

		const log = YAML.parse( readFileSync( path.join( dir, file ), 'utf8' ) ),
			{ comment = '', links = '', attributions = '' } = log;
		delete log.links;
		delete log.comment;
		delete log.attributions;
		res.push( {
			title: path.parse( file ).name,
			...log,
			comment,
			links,
			attributions,
		} );
	} );

	return res;
};
