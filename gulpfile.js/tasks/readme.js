/**
 * -----------------------------------------------------------
 * readme
 * -----------------------------------------------------------
 *
 * Generate a readme.txt from _readme files
 *
 */

var gulp    = require( 'gulp' ),
	fs      = require( 'fs' ),
	include = require( 'gulp-include' )
;

/**
 * Convert the CHANGELOG.md file to the format WP.org needs for the special mdown
 */
function changelog( cb ) {

	fs.readFile( './CHANGELOG.md', 'utf8', function( err, data ) {

		if ( err ) {
			return console.log( err );
		}

		// add equals signs around version numbers
		data = data.replace( /v\d+\.\d+\.\d+ - \d{4}\/\d{2}\/\d{2}/g, function( match ) {
			return '= ' + match + ' =';
		} );

		// b/c ocd...
		data = data.replace( /-------------------/g, '-----------------------' );

		// write to the file in _readme dir
		fs.writeFile( './_readme/changelog.md', data, 'utf8', function( err ) {

			if ( err ) {
				return console.log( err );
			}

			cb();

		});


	} );

}

gulp.task( 'readme', function() {
	changelog( function() {

		return gulp.src( '_readme/readme.txt' )
			.pipe( include() )
			.pipe( gulp.dest( './' ) );

	} );

} );
