/**
 * -----------------------------------------------------------
 * Versioner
 * -----------------------------------------------------------
 *
 * Update placeholder @since & @version docblocks
 * witn an actual version numbers
 *
 * Usage: gulp versioner -V 9.9.9
 */

var   gulp    = require( 'gulp' )
	, replace = require( 'gulp-replace' )
	, argv    = require( 'yargs' ).argv
	, gutil   = require( 'gulp-util' )
;

gulp.task( 'versioner', function() {

	var the_version = argv.V;

	if ( ! the_version ) {
		gutil.log( gutil.colors.red( 'Missing version number. Try `gulp versioner -V 9.9.9`' ) );
		return;
	}

	return gulp.src( [ './lifterlms.php', './includes/**/*.php', './templates/**/*.php', './tests/**/*.php', './_private/**/*.js'  ], { base: './' } )
		.pipe( replace( /(\* @(since|version) +\[version\])/g, function( string ) {
			return string.replace( '[version]', the_version );
		} ) )
		.pipe( gulp.dest( './' ) );

} );
