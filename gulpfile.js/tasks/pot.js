/**
 * -----------------------------------------------------------
 * pot
 * -----------------------------------------------------------
 *
 * Generate a new .pot file
 *
 */

var   gulp  = require( 'gulp' )
	, sort  = require( 'gulp-sort' )
	, wpPot = require( 'gulp-wp-pot' )
;

gulp.task( 'pot', function() {

	gulp.src( [ '*.php', './**/*.php', '!vendor/*' ] )

		.pipe( sort() )

		.pipe( wpPot( {
			domain: 'lifterlms',
			destFile:'lifterlms.pot',
			package: 'lifterlms',
			bugReport: 'https://github.com/gocodebox/lifterlms/issues',
			lastTranslator: 'Thomas Patrick Levy <thomas@lifterlms.com>',
			team: 'LifterLMS <help@lifterlms.com>',
		} ) )

		.pipe( gulp.dest( 'languages' ) )

} );
