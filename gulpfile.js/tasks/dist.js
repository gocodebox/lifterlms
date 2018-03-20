/**
 * -----------------------------------------------------------
 * dist
 * -----------------------------------------------------------
 *
 * Pre-distribution tasks
 *
 */

var   gulp  = require( 'gulp' );

gulp.task( 'dist', [ 'versioner', 'rebuild', 'js:builder', 'readme', 'pot:js', 'pot' ] );
