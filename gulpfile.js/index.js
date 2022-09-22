/**
 * Main Gulp File
 *
 * Requires all task files
 */
var gulp = require('gulp');

// All custom tasks.
require( './tasks/hacky-clean' );
require( './tasks/js-additional' );
require( './tasks/js-builder' );

// All tasks from lib-tasks.
require( 'lifterlms-lib-tasks' )( gulp );
