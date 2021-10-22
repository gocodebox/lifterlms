const { readdirSync, readFileSync, existsSync } = require( 'fs' ),
	path = require( 'path' ),
	YAML = require( 'yaml' );

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
