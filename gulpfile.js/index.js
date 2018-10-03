/**
 * Main Gulp File
 *
 * Requires all task files
 */
var gulp = require('gulp');

// all tasks from lib-tasks
var  ab=require( 'lifterlms-lib-tasks' )( gulp );

// all custom tasks
var  a=require( './tasks/js:additional' );
var  b=require( './tasks/js:builder' );
var  c=require( './tasks/readme' );
var  d=require( './tasks/versioner:readme' );
 
  
