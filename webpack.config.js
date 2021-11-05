/**
 * Webpack config
 *
 * @package LifterLMS/Scripts/Dev
 *
 * @since 5.5.0
 * @version 5.5.0
 */

const generate = require( '@lifterlms/scripts/config/webpack.config' ),
	config     = generate( {
		js: [ 'admin-addons' ],
		css: [ 'admin-addons' ],
	} );


// Remove the directory clearer.
config.plugins = config.plugins.filter( plugin => {
	return 'CleanWebpackPlugin' !== plugin.constructor.name;
} );

module.exports = config;
