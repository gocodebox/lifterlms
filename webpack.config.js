/**
 * Webpack config
 *
 * @package LifterLMS/Scripts/Dev
 *
 * @since 5.5.0
 * @version 6.10.0
 */

const { resolve } = require( 'path' ),
	blocksConfig = require( '@lifterlms/scripts/config/blocks-webpack.config' ),
	{ CleanWebpackPlugin } = require( 'clean-webpack-plugin' ),
	generate = require( '@lifterlms/scripts/config/webpack.config' ),
	config = generate( {
		js: [
			'admin-addons',
			'admin-award-certificate',
			'admin-certificate-editor',
			'admin-notifications',
			'quill-wordcount',

			// Module packages.
			'components',
			'icons',
			'spinner',
			'utils',
		],
		css: [
			'admin-addons'
		],
	} );

// Remove the default directory clearer, since we include source JS in the assets/js directory we need to not clear the dest directory (for now).
config.plugins = config.plugins.filter( plugin => {
	return 'CleanWebpackPlugin' !== plugin.constructor.name;
} );

// Modified clean.
config.plugins.push( new CleanWebpackPlugin( {

	cleanOnceBeforeBuildPatterns: [
		// Source maps.
		`assets/js/*.js.map`,
	],

} ) );

// config.entry.fontawesome = resolve( './src/scss/fontawesome.scss' );

module.exports = [
	blocksConfig,
	config
];
