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

gulp.task( 'versioner:readme', function() {

	var the_version = argv.V;

	if ( ! the_version ) {
		gutil.log( gutil.colors.red( 'Missing version number. Try `gulp versioner -V 9.9.9`' ) );
		return;
	}

	return gulp.src( [ './_readme/header.md'  ], { base: './' } )
		.pipe( replace( /Stable tag: (\d+\.\d+\.\d+)/g, function( match, p1, offset, string ) {
			return match.replace( p1, the_version );
		} ) )
		.pipe( gulp.dest( './' ) );

} );
