#!/usr/bin/node

/**
 * A CLI utility used to copy source SVGs files from the FontAwesome package to a specified location.
 *
 * This script is intended to be used by projects including SVGs through this package.
 *
 * @since [version]
 * @version [version]
 *
 * Example usage:
 *
 *   node ./node_modules/@lifterlms/fontawesome/bin/svg.js [distDirectory]
 *
 * The distDirectory parameter is optional and defaults to `./src/img/fontawesome`.
 */

const
	{ argv } = process,
	srcDir = argv[ 2 ] || './src/img/fontawesome',
	{ resolve } = require( 'path' ),
	{ cpSync, existsSync } = require( 'fs' ),
	SVG_DIR = resolve( './node_modules/@fortawesome/fontawesome-free/svgs' ),
	SRC_DIR = resolve( srcDir );

const bold = '\x1b[32m\x1b[1m',
	reset = '\x1b[0m';

if ( ! existsSync( SVG_DIR ) ) {
	console.error( 'Cannot locate the SVG source directory, please run `npm i` and try again.' );
	process.exit( 1 );
}

console.log( `Copying SVG files from ${ bold }${ SVG_DIR }${ reset } to ${ bold }${ SRC_DIR }${ reset }.` );

cpSync( SVG_DIR, SRC_DIR, { recursive: true } );
