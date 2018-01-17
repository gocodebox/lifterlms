/**
 * -----------------------------------------------------------
 * js:builder
 * -----------------------------------------------------------
 * Compile Admin builder Javascript
 */

var   gulp              = require( 'gulp' )
	, requirejsOptimize = require( 'gulp-requirejs-optimize' )
	, rename            = require( 'gulp-rename' )
	, sourcemaps        = require( 'gulp-sourcemaps' )
;

gulp.task( 'js:builder', function() {
	gulp.src( 'assets/js/builder/main.js' )
		.pipe( sourcemaps.init() )
		.pipe( requirejsOptimize( function( file ) {
			return {
				name: '../vendor/almond',
				// optimize: 'uglify2',
				optimize: 'none',
				wrap: {
					start: "(function($){",
					end: "}(jQuery));"
				},
				baseUrl: 'assets/js/builder/',
				include: [ 'main' ],
				preserveLicenseComments: false
			};
		} ) )
		.pipe( rename( 'llms-builder.min.js' ) )
		.pipe( sourcemaps.write('/') )
		.pipe( gulp.dest( 'assets/js/' ) );
});
