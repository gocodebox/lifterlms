/**
 * -----------------------------------------------------------
 * pot:js
 * -----------------------------------------------------------
 *
 * Find JS l10n strings
 *
 */

var    gulp = require( 'gulp' ),
	     fs = require( 'fs' ),
	   glob = require( 'glob' )
  ;

gulp.task( 'pot:js', function() {

	var obj = {},
		counter = 0;
		patterns = [ '_private/js/**/*.js', 'assets/js/builder/**/*.js' ];

	patterns.forEach( function ( pattern ) {

		var files = glob.sync( pattern );

		files.forEach( function( file ) {

			if ( '_private/js/app/llms-l10n.js' === file ) {
				return;
			}

			var data = fs.readFileSync( process.cwd() + '/' + file ),
				regex = /(?:LLMS\.l10n\.(?:translate|replace))\( '([^'\\]*(?:\\.[^'\\]*)*)'/g,
				strings = [],
				matches;

			while ( matches = regex.exec( data ) ) {
				strings.push( matches[1] );
			}

			if ( strings.length ) {

				var since = /[ \t\/*#@]*@since(?:\s*)(.*)/g,
					version = /[ \t\/*#@]*@version(?:\s*)(.*)/g
					since_matches = since.exec( data )
					version_matches = version.exec( data );

				obj[ file ] = {
					since: since_matches ? since_matches[1] : '[version]',
					version: version_matches ? version_matches[1] : '[version]',
					strings: [],
				};

				// remove dupes
				strings.forEach( function( string, index ) {

					if ( -1 === obj[ file ].strings.indexOf( string ) ) {
						counter++;
						obj[ file ].strings.push( string );
					}

				} );

			}

		} );

	} );

	var data = '<?php\r\n/**\r\n\
 * LifterLMS "Pot File" for JS l10n\r\n\
 * @since    3.16.5\r\n\
 * @version  [version]\r\n\
 */\r\n\
if ( ! defined( \'ABSPATH\' ) ) { exit; }\r\n\r\n';

	data += '$strings = array(';
	for ( file in obj ) {

		data += '\r\n\
\t/**\r\n\
\t * file: ' + file + '\r\n\
\t * @since    ' + obj[file].since +'\r\n\
\t * @version  ' + obj[file].version +'\r\n\
\t */\r\n\
';

		obj[file].strings.forEach( function( string ) {
			data += "\t'" + string + "' => esc_html__( '" + string + "', 'lifterlms' ),\r\n"
		} );

	};
	data += ');\r\nreturn $strings;\r\n';

	fs.writeFile( './languages/lifterlms-js-pot.php', data, function( err ) {

		if ( err ) {
			console.error( err );
		}

		console.log( 'jspot completed and found ' + counter + ' strings' );

	} );

} );


