/**
 * -----------------------------------------------------------
 * dist
 * -----------------------------------------------------------
 *
 * Pre-distribution tasks
 *
 */

var   gulp  = require( 'gulp' )
;

gulp.task( 'dist', [ 'rebuild', 'readme', 'pot' ] );
