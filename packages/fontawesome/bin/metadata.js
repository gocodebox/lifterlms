#!/usr/bin/node

/**
 * A CLI utility used to create the FontAwesome icon metadata file located at `src/metadata.json`
 *
 * This utility parses YAML metadata included with the FA package and converts it to our desired format.
 *
 * This is an internal script intended to be run during package builds before release or after updating the FontAwesome dependency.
 *
 * @since [version]
 * @version [version]
 *
 * Example usage:
 *
 * node ./bin/metadata.js
 */

const
	{ resolve } = require( 'path' ),
	YAML = require( 'yaml' ),
	{ existsSync, readFileSync, writeFileSync } = require( 'fs' ),
	METADATA_FILE = resolve( './node_modules/@fortawesome/fontawesome-free/metadata/icons.yml' ),
	SRC_DIR = resolve( './src' );

if ( ! existsSync( METADATA_FILE ) ) {
	console.error( 'Cannot locate the source metadata file, please run `npm i` and try again.' );
	process.exit( 1 );
}

const origMetadata = YAML.parse( readFileSync( METADATA_FILE, 'utf8' ) ),
	metadata = {};

Object.keys( origMetadata ).forEach( ( id ) => {

	const { styles, label, aliases } = origMetadata[ id ],
		terms = aliases?.names || [];

	metadata[ id ] = { styles, label, terms: [ ...terms, label, id ] };

} );

writeFileSync( `${ SRC_DIR }/metadata.json`, JSON.stringify( metadata, null, 2 ) );

console.log( `Successfully created ${ SRC_DIR }/metadata.json` );
