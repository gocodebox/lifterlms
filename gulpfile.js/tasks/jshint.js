/**
 * -----------------------------------------------------------
 * jshint
 * -----------------------------------------------------------
 *
 * Run jshint against JS files
 * in the project
 *
 */

var   gulp   = require( 'gulp' )
	, argv   = require( 'yargs' ).argv
	, jshint = require( 'gulp-jshint' )
	, notify = require( 'gulp-notify' )
;

// parse a file or run default on all ph
var glob = ( argv.file ) ? argv.file : [
		'./_private/js/**/*.js',
	];


gulp.task( 'jshint', function() {

	gulp.src( glob )

		.pipe( jshint( '.jshintrc' ) )

		.pipe( jshint.reporter( 'jshint-stylish' ) )

		.pipe( jshint.reporter( 'fail' ) )

		.on( 'error', notify.onError( {

				message: '<%= error.message %>',
				sound: 'Funk',
				title: 'jshint error'

			} ) );

} );
