/**
 * Webpack config
 *
 * @package LifterLMS_Blocks/Scripts/Dev
 *
 * @since 1.8.0
 * @version 2.4.3
 */

const
	generate = require( '@lifterlms/scripts/config/webpack.config' ),
	config   = generate( {
		css: [ 'blocks' ],
		js: [ 'blocks', 'blocks-backwards-compat' ],
	} );

config.module.rules.forEach( rule => {

	if ( '\\.(sc|sa)ss$' === rule.test.source ) {
		rule.use[ 3 ].options.additionalData = '@import "./src/scss/_vars.scss";\n';
	}

} );

module.exports = config;
