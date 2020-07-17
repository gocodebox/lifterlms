const
	cssExtract = require( 'mini-css-extract-plugin' ),
	cssRTL     = require( 'webpack-rtl-plugin' ),
	config     = require( '@wordpress/scripts/config/webpack.config' ),
	depExtract = require( '@wordpress/dependency-extraction-webpack-plugin' )
	path       = require( 'path' );

/**
 * Webpack config
 *
 * @package LifterLMS_Groups/Scripts/Dev
 *
 * @since 1.3.0
 * @version 1.3.0
 */

/**
 * Generates a Webpack config object
 *
 * This is opinionated based on our opinions for directory structure.
 *
 * ESNext JS source files are located in `assets/src/js`.
 *
 * SASS/SCSS source files are located in `assets/src/sass`.
 *
 * SASS files should be imported via the JS source file.
 *
 * @since 1.3.0
 *
 * @param {String[]} options.css  Array of CSS file slugs.
 * @param {String[]} options.js   Array of JS file slugs.
 * @param {String} options.prefix File prefix.
 * @return {Object} A webpack.config.js object.
 */
module.exports = ( { css = [], js = [], prefix = 'llms-' } ) => {

	// Setup entry files.
	const entry = {};
	js.forEach( file => {
		entry[ file ] = path.resolve( process.cwd(), 'assets/src/js/', `${ file }.js` );
	} );

	const plugins = config.plugins;
	// Delete the css extractor implemented in the default config (we'll replace it with our own later).
	plugins.forEach( ( plugin, index ) => {
		if ( 'MiniCssExtractPlugin' === plugin.constructor.name ) {
			config.plugins.splice( index, 1 );
		}
	} );

	// Setup CSS extraction & generate RTL files.
	css.forEach( file => {

		plugins.push( new cssExtract( {
			filename: `css/${ prefix }[name].css`,
		} ) );

		plugins.push( new cssRTL( {
			filename: `css/${ prefix }[name]-rtl.css`,
		} ) );

	} );


	plugins.push( new depExtract( {
		injectPolyfill: true,
		requestToExternal: request => {
			if ( 'llms-quill' === request ) {
				return 'Quill';
			} else if ( 'llms-izimodal' === request ) {
				return [ 'jQuery', 'iziModal' ];
			} else if ( request.startsWith( 'llms/' ) || request.startsWith( 'LLMS/' ) ) {
				return request.split( '/' );
			}
		},
		requestToHandle: request => {
			if ( request.startsWith( 'llms/' ) || request.startsWith( 'LLMS/' ) ) {
				return 'llms';
			}
		}
	} ) );

	return {
		...config,
		entry,
		output: {
			filename: `js/${ prefix }[name].js`,
			path: path.resolve( process.cwd(), 'assets/' ),
		},
		plugins,
	};

}
