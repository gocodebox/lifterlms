/**
 * Webpack config
 *
 * @package LifterLMS_Blocks/Scripts/Dev
 *
 * @since 1.8.0
 * @version 2.4.3
 */

const
	path     = require( 'path' ),
	generate = require( '@lifterlms/scripts/config/webpack.config' ),
	config   = generate( {
		css: [ 'blocks' ],
		js: [ 'blocks', 'blocks-backwards-compat' ],
	} ),
	// Absolute path so sass resolves the import regardless of the importing file's location.
	varsPath = path.resolve( __dirname, 'src/scss/_vars.scss' ).replace( /\\/g, '/' );

config.module.rules.forEach( rule => {

	if ( '\\.(sc|sa)ss$' === rule.test.source ) {
		rule.use[ 3 ].options.additionalData = `@import "${ varsPath }";\n`;
	}

} );

module.exports = config;
