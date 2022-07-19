const
	chalk = require( 'chalk' ),
	columnify = require( 'columnify' ),
	YAML = require( 'yaml' ),
	{ existsSync } = require( 'fs' ),
	{ getProjectSlug, logResult, parseMainFileMetadata } = require( '../../utils' );

module.exports = {
	command: 'parse',
	description: "Retrieves metadata from the project's main file.",
	options: [
		[ '-F, --file <file>', 'Main project file name.', `${ getProjectSlug() }.php` ],
		[ '-k, --key <key>', 'Retrieves a single metadata by key name.' ],
		[ '-f, --format [format]', 'Output format. Accepts: table, json, yaml.', 'table' ],
	],
	action: ( { file, key, format } ) => {

		if ( ! existsSync( file ) ) {
			logResult( `Invalid file: "${ chalk.bold( file ) }".`, 'error' );
			process.exit( 1 );
		}

		const metas = parseMainFileMetadata( file );
		
		if ( key ) {
			if ( metas[ key ] ) {
				console.log( metas[ key ] );
				process.exit( 0 );
			} else {
				logResult( `No metadata found for key name "${ chalk.bold( key ) }".`, 'error' );
				process.exit( 1 );
			}
		}

		if ( 'json' === format ) {
			console.log( JSON.stringify( metas ) );
		} else if ( 'yaml' === format || 'yml' === format ) {
			console.log( YAML.stringify( metas ) );
		} else if ( 'table' === format ) {
			console.log(
				columnify(
					metas,
					{
						headingTransform: ( heading ) => chalk.bold.underline( heading.toUpperCase() ),
					},
				)
			);
		}

	},
};
