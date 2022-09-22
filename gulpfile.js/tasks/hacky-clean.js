const gulp = require( 'gulp' ),
	{ unlinkSync } = require( 'fs' );

	filesToRemove = [
		'assets/css/dancing-script-rtl.css',
		'assets/css/imperial-script-rtl.css',
		'assets/css/pirata-one-rtl.css',
		'assets/css/unifraktur-maguntia-rtl.css',
	]

gulp.task( 'hacky-clean', function( cb ) {

	filesToRemove.forEach( file => {
		unlinkSync( file );
	} );

	return cb();
} );
