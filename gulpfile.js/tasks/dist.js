/**
 * -----------------------------------------------------------
 * dist
 * -----------------------------------------------------------
 *
 * Pre-distribution tasks
 *
 */

var   gulp  = require( 'gulp' );

gulp.task( 'dist', [ 'versioner', 'rebuild', 'readme', 'pot:js', 'pot' ] );
