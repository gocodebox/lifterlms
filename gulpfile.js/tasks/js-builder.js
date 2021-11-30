/**
 * -----------------------------------------------------------
 * js-builder
 * -----------------------------------------------------------
 * Compile Admin builder Javascript
 */

var   gulp              = require( 'gulp' )
	, requirejsOptimize = require( 'gulp-requirejs-optimize' )
	, rename            = require( 'gulp-rename' )
	, sourcemaps        = require( 'gulp-sourcemaps' )
;

gulp.task( 'js-builder', function( cb ) {

	gulp.src( 'assets/js/builder/main.js' )
		// unminified
		.pipe( sourcemaps.init() )
		.pipe( requirejsOptimize( function( file ) {
			return {
				name: 'vendor/almond',
				optimize: 'none',
				wrap: {
					start: "(function($){",
					end: "}(jQuery));"
				},
				baseUrl: 'assets/js/builder/',
				include: [ 'main' ],
				preserveLicenseComments: false
			};
		} ).on( 'error', ( err ) => console.log( err ) ) )
		.pipe( rename( 'llms-builder.js' ) )
		.pipe( sourcemaps.write( '../maps/js', { destPath: 'assets/js' } ) )
		.pipe( gulp.dest( 'assets/js/' ) )

		// minified
		.pipe( sourcemaps.init() )
		.pipe( requirejsOptimize( function( file ) {
			return {
				name: 'vendor/almond',
				optimize: 'uglify2',
				wrap: {
					start: "(function($){",
					end: "}(jQuery));"
				},
				baseUrl: 'assets/js/builder/',
				include: [ 'main' ],
				preserveLicenseComments: false
			};
		} ).on( 'error', ( err ) => console.log( err ) ) )
		.pipe( rename( 'llms-builder.min.js' ) )
		.pipe( sourcemaps.write( '../maps/js', { destPath: 'assets/js' } ) )
		.pipe( gulp.dest( 'assets/js/' ) );

	cb();

});
