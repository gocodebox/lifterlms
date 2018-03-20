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

gulp.task( 'versioner', [ 'v-docblocks', 'v-main', 'v-readme' ] );

// Replace @version, @since, & @deprecated [version] placeholders
// with the submitted version number
gulp.task( 'v-docblocks', function() {

	var the_version = argv.V;

	if ( ! the_version ) {
		gutil.log( gutil.colors.red( 'Missing version number. Try `gulp versioner -V 9.9.9`' ) );
		return;
	}

	return gulp.src( [ './lifterlms.php', './includes/**/*.php', './templates/**/*.php', './tests/**/*.php', './_private/**/*.js', './assets/js/builder/**/*.js'  ], { base: './' } )
		.pipe( replace( /(\* @(since|version|deprecated) +\[version\])/g, function( string ) {
			return string.replace( '[version]', the_version );
		} ) )
		.pipe( gulp.dest( './' ) );

} );

// replace main plugin file Version declaration as well as the public $version variable
gulp.task( 'v-main', function() {

	var the_version = argv.V;

	if ( ! the_version ) {
		gutil.log( gutil.colors.red( 'Missing version number. Try `gulp versioner -V 9.9.9`' ) );
		return;
	}

	return gulp.src( [ './lifterlms.php'  ], { base: './' } )
		.pipe( replace( / \* Version: (\d+\.\d+\.\d+)/g, function( match, p1, offset, string ) {
			return match.replace( p1, the_version );
		} ) )
		.pipe( replace( /	public \$version = '(\d+\.\d+\.\d+)';/g, function( match, p1, offset, string ) {
			return match.replace( p1, the_version );
		} ) )
		.pipe( gulp.dest( './' ) );

} );

gulp.task( 'v-readme', function() {

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
