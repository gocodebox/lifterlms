/**
 * Webpack config
 *
 * @package LifterLMS_Groups/Scripts/Dev
 *
 * @since Unknown
 * @version [version]
 */

// Deps.
const
	cssExtract     = require( 'mini-css-extract-plugin' ),
	cssRTL         = require( 'webpack-rtl-plugin' ),
	config         = require( '@wordpress/scripts/config/webpack.config' ),
	depExtract     = require( '@wordpress/dependency-extraction-webpack-plugin' )
	path           = require( 'path' ),
	{ isFunction } = require( 'lodash' ),
	{ NODE_ENV = 'production' } = process.env;

/**
 * Used by dependency extractor to handle requests to convert names of scripts included in the LifterLMS Core.
 *
 * @since 1.2.1
 * @since [version] Added handling for WP Core scripts: backbone and underscore.
 *
 * @param {string} request External script slug/id.
 * @return {String|Array} A string
 */
function requestToExternal( request ) {

	switch ( request ) {
		case 'llms-quill':
			return 'Quill';
			break;

		case 'llms-izimodal':
			return [ 'jQuery', 'iziModal' ];
			break;

		case 'backbone':
			return 'Backbone';
			break;

		case 'underscore':
			return '_';
			break;
	}

	if ( request.startsWith( 'llms/' ) || request.startsWith( 'LLMS/' ) ) {
		return request.split( '/' );
	}

}

/**
 * Used by dependency extractor to handle requests to scripts included in the LifterLMS Core.
 *
 * @since 1.2.1
 *
 * @param {string} request External script slug/id.
 * @return {String|Array} A string
 */
function requestToHandle( request ) {
	if ( request.startsWith( 'llms/' ) || request.startsWith( 'LLMS/' ) ) {
		return 'llms';
	}
}

/**
 * Configure the `entry` object of the webpack config file.
 *
 * @since 1.2.1
 * @since 1.2.3 Add a configurable source file path.
 *
 * @param {String[]} js      Array of JS file slugs.
 * @param {String}   srcPath Relative path to the base source file directory.
 * @return {Object} Webpack config entry object.
 */
function setupEntry( js, srcPath ) {

	const entry = {};
	js.forEach( file => {
		entry[ file ] = path.resolve( process.cwd(), `${ srcPath }js/`, `${ file }.js` );
	} );

	return entry;

}

/**
 * Setup the `plugins` array of the webpack config file.
 *
 * @since 1.2.1
 * @since [version] Allow filename modification via modifyFileName arg.
 *
 * @param {Object[]} plugins        Array of plugin objects or classes.
 * @param {String[]} css            Array of CSS file slugs.
 * @param {String}   prefix         File prefix.
 * @param {Function} modifyFileName User-supplied function to customize the filename template.
 * @return {Object[]} Array of plugin objects or classes.
 */
function setupPlugins( plugins, css, prefix, minSuffix, modifyFileName ) {

	// Delete the css extractor implemented in the default config (we'll replace it with our own later).
	plugins.forEach( ( plugin, index ) => {
		if ( 'MiniCssExtractPlugin' === plugin.constructor.name ) {
			config.plugins.splice( index, 1 );
		}
	} );

	css.forEach( file => {

		// Extract CSS.
		plugins.push( new cssExtract( {
			filename: ( pathData ) => modifyFileName( `css/${ prefix }[name]${ minSuffix }.css`, { ...pathData, filename: file, basename: file } ),
		} ) );

		// Generate an RTL CSS file.
		plugins.push( new cssRTL( {
			filename: modifyFileName( `css/${ prefix }[name]-rtl${ minSuffix }.css`, { filename: file, basename: file } ),
			minify: minSuffix ? true : false,
		} ) );

	} );

	// Add a custom dependency extractor.
	plugins.push( new depExtract( {
		requestToExternal,
		requestToHandle,
		injectPolyfill: true,
	} ) );

	return plugins;

}

/**
 * Generates a Webpack config object
 *
 * This is opinionated based on our opinions for directory structure.
 *
 * ESNext JS source files are located in `src/js`.
 *
 * SASS/SCSS source files are located in `src/sass`.
 *
 * SASS files should be imported via the JS source file.
 *
 * @since Unknown
 * @since 1.2.1 Reduce method size by using helper methods
 * @since 1.2.3 Add a configurable source file path option and set the default to `src/` instead of `assets/src`.
 * @since [version] Added optional minification suffix option.
 *
 * @param {String[]} options.css            Array of CSS file slugs.
 * @param {String[]} options.js             Array of JS file slugs.
 * @param {String}   options.prefix         File prefix.
 * @param {String}   options.outputPath     Relative path to the output directory.
 * @param {String}   options.minSuffix      If specified and building in the 'production' environment, will append the suffix before the file extension.
 * @param {Function} options.modifyFileName User-supplied function to customize the filename template.
 * @return {Object} A webpack.config.js object.
 */
module.exports = ( { css = [], js = [], prefix = 'llms-', outputPath = 'assets/', srcPath = 'src/', minSuffix = '', modifyFileName = null } ) => {

	minSuffix = 'production' === NODE_ENV ? minSuffix : '';

	modifyFileName = isFunction( modifyFileName ) ? modifyFileName : ( filename ) => filename;

	return {
		...config,
		entry: setupEntry( js, srcPath ),
		output: {
			filename: ( pathData ) => modifyFileName( `js/${ prefix }[name]${ minSuffix }.js`, pathData ),
			path: path.resolve( process.cwd(), outputPath ),
		},
		plugins: setupPlugins( config.plugins, css, prefix, minSuffix, modifyFileName ),
	};

}
