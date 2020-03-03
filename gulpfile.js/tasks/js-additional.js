var    gulp = require( 'gulp' )
	,  header = require( 'gulp-header' )
	, include = require( 'gulp-include' )
	,    maps = require( 'gulp-sourcemaps' )
	,    pump = require( 'pump' )
	,  rename = require( 'gulp-rename' )
	,  uglify = require( 'gulp-uglify' )
	, gulpignore = require( 'gulp-ignore' )

    ,         path = require( 'path' )
;

gulp.task( 'js-additional', function( cb ) {

	var notice = [
		'/****************************************************************',
 		' *',
 		' * Contributor\'s Notice',
 		' * ',
 		' * This is a compiled file and should not be edited directly!',
 		' * The uncompiled script is located in the "assets/private" directory',
 		' * ',
 		' ****************************************************************/',
 		'',
 		'',
 	];

	pump( [
		gulp.src( 'assets/js/private/**/*.js' ),
			maps.init(),
			include(),
			header( notice.join( '\n' ) ),
			maps.write('../maps/js', { destPath: 'assets/js' } ),
			gulp.dest( 'assets/js' ),

	        // Don't pass maps any further.
	        gulpignore.exclude( file => '.js' !== path.extname( file.basename ) ),

			uglify(),
			rename( {
				suffix: '.min',
			} ),
			maps.write('../maps/js', { destPath: 'assets/js' } ),
			gulp.dest( 'assets/js' )
		],
		cb
	);

} );
