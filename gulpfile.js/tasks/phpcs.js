var gulp = require( 'gulp' ),
	argv = require( 'yargs' ).argv,
	phpcs = require( 'gulp-phpcs' )
;

// parse a file or run default on all php
var glob = ( argv.file ) ? argv.file : [
		'./**/*.php',
		'bin/**/*',
		'gulpefile.js/**/*',
		'!./node_modules/**/*',
		'!./tests/**/*',
		'!./vendor/**/*'
	];

gulp.task( 'phpcs', function () {

	return gulp.src( glob )

		// Validate files using PHP Code Sniffer
		.pipe( phpcs( {
			bin: './vendor/bin/phpcs',
			errorSeverity: 1,
			standard: './phpcs.xml',
			warningSeverity: 1,
		} ) )

		// Log all problems that were found
		.pipe( phpcs.reporter( 'log' ) );

} );
