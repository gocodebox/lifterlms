/**
 * Webpack config
 *
 * @package LifterLMS/Scripts/Dev
 *
 * @since [version]
 * @version [version]
 */

const { npm_lifecycle_event } = process.env; // NPM script name.

function modifyFileName( template, { filename } ) {
	if ( template.includes( '.css' ) && 'builder' === filename ) {
		template = template.replace( 'llms-', '' );
	}
	return template;
}

const
	webpack  = require( 'webpack' ),
	generate = require( './packages/scripts/config/webpack.config' ),
	config   = generate( {
		css: [ 'builder' ],
		js: [ 'builder' ],
		minSuffix: 'start' === npm_lifecycle_event ? '' : '.min',
		modifyFileName,
	} );

// Remove the CleanWebpackPlugin because it deletes the majority of our assets.
config.plugins = config.plugins.filter( ( plugin, i ) => {

	if ( 'CleanWebpackPlugin' === plugin.constructor.name ) {
		return false;
	}

	return true;

} );

// Disable chunking.
config.plugins.push(
	new webpack.optimize.LimitChunkCountPlugin( {
		maxChunks: 1
	} )
);

// Allow AMD module resolution (used by the builder BackBone app).
config.resolve.modules = [ './', 'node_modules' ];

module.exports = config;
