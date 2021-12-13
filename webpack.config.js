/**
 * Webpack config
 *
 * @package LifterLMS/Scripts/Dev
 *
 * @since 5.5.0
 * @version [version]
 */

const generate = require( '@lifterlms/scripts/config/webpack.config' ),
	config     = generate( {
		js: [
			'admin-addons',
			'admin-certificate-editor',

			// Module packages.
			'components',
			'icons'
		],
		css: [ 'admin-addons' ],
	} );

// Remove the directory clearer.
config.plugins = config.plugins.filter( plugin => {
	return 'CleanWebpackPlugin' !== plugin.constructor.name;
} );

module.exports = config;
