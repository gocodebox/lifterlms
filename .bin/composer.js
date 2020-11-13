#!/usr/bin/env node

/**
 * Merge composer configurations to create an alternate configuration file for use in php8 environments
 *
 * Each JSON file found in the .config/composer/php8 directory (excluding the compiled composer.json file)
 * is merged into the project's main composer.json file, using the file name as the key to be added to the
 * file.
 *
 * A modified composer.json file is stored in the same directory. This file can be used to replace the project's
 * main composer.json file in php8 environments.
 */

const
	fs        = require( 'fs' ),
	getJSON   = file => JSON.parse( fs.readFileSync( file ).toString() ),
	mainPath  = `${ process.cwd() }/composer.json`,
	main      = getJSON( mainPath ),
	php8Dir   = `${ process.cwd() }/.config/composer/php8`,
	php8Files = fs.readdirSync( php8Dir );

php8Files.forEach( file => {

	if ( 'composer.json' !== file ) {
		main[ file.replace( '.json', '' ) ] =  getJSON( `${ php8Dir }/${ file }` );
	}

} );

fs.writeFileSync( `${ php8Dir }/composer.json`, JSON.stringify( main, null, 2 ) );
