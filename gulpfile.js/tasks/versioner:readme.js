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
		.pipe( replace( /Stable tag: (\d+\.\d+\.\d+)(\-\D+\.\d+)?/g, function( match, p1, p2, string ) {
	        // if there's a prerelease suffix (eg -beta.1) remove it entirely
	        if ( p2 ) {
	          match = match.replace( p2, '' );
	        }
			return match.replace( p1, the_version );
		} ) )
		.pipe( gulp.dest( './' ) );

} );
