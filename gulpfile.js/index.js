/**
 * Main Gulp File
 *
 * Requires all task files
 */
var gulp = require('gulp');

// all tasks from lib-tasks
require( 'lifterlms-lib-tasks' )( gulp );

// all custom tasks
require( './tasks/js:additional' );
require( './tasks/js:builder' );
require( './tasks/readme' );
require( './tasks/versioner:readme' );
