/**
 * Main Gulp File
 *
 * Requires all task files
 */

var gulp = require('gulp'),
	requireDir = require( 'require-dir' );

require( 'lifterlms-lib-tasks' )( gulp );
// requireDir( './tasks' );
