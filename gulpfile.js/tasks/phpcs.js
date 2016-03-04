/**
 * -----------------------------------------------------------
 * PHP_CodeSniffer
 * -----------------------------------------------------------
 *
 * Run PHP_CodeSniffer against PHP files
 * in the project
 *
 */

var   gulp  = require( 'gulp' )
	, argv  = require( 'yargs' ).argv
	, phpcs = require( 'gulp-phpcs' )
;

// parse a file or run default on all ph
var glob = ( argv.file ) ? argv.file : [
		'./**/*.php',
		'bin/**/*',
		'gulpefile.js/**/*',
		'!./node_modules/**/*',
		'!./tests/**/*',
		'!./vendor/**/*'
	];

// set default error severity
var errorSeverity = ( argv.error ) ? argv.errors : 1;

// set default warning severity
var warningSeverity = ( argv.warning !== undefined ) ? argv.warning : 1;


gulp.task( 'phpcs', function () {

	return gulp.src( glob )

		// Validate files using PHP Code Sniffer
		.pipe( phpcs( {
			bin: './vendor/bin/phpcs',
			errorSeverity: errorSeverity,
			standard: './phpcs.xml',
			warningSeverity: warningSeverity,
		} ) )

		// Log all problems that were found
		.pipe( phpcs.reporter( 'log' ) )

		// report an error if any found
		.pipe( phpcs.reporter( 'fail' ) );

} );
