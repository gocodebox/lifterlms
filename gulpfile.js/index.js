/**
 * Main Gulp File
 *
 * Requires all task files
 */

// var requireDir = require( 'require-dir' );

// requireDir( './tasks' );

var gulp = require('gulp');
require( 'lifterlms-lib-tasks' )( gulp );
require( './tasks/js:additional' );
require( './tasks/js:builder' );
