/**
 * Main Gulp File
 *
 * Requires all task files
 */
var gulp = require('gulp');

// all custom tasks
require( './tasks/js:additional' );
require( './tasks/js:builder' );
require( './tasks/readme' );
require( './tasks/versioner:readme' );

// all tasks from lib-tasks
require( 'lifterlms-lib-tasks' )( gulp );
