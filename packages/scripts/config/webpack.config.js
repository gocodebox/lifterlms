/**
 * Webpack config
 *
 * @package LifterLMS_Groups/Scripts/Dev
 *
 * @since Unknown
 * @version 2.1.0
 */

// Deps.
const
	cssExtract   = require( 'mini-css-extract-plugin' ),
	cssRTL       = require( 'webpack-rtl-plugin' ),
	config       = require( '@wordpress/scripts/config/webpack.config' ),
	depExtract   = require( '@wordpress/dependency-extraction-webpack-plugin' ),
	path         = require( 'path' );

/**
 * Used by dependency extractor to handle requests to convert names of scripts included in the LifterLMS Core.
 *
 * @since 1.2.1
 *
 * @param {string} request External script slug/id.
 * @return {String|Array} A string
 */
function requestToExternal( request ) {

	if ( 'llms-quill' === request ) {
		return 'Quill';
	} else if ( 'llms-izimodal' === request ) {
		return [ 'jQuery', 'iziModal' ];
	} else if ( request.startsWith( 'llms/' ) || request.startsWith( 'LLMS/' ) ) {
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
 * @since 2.0.0 Remove default DependencyExtractionWebpackPlugin in favor of our custom loader.
 * @since 2.1.0 Added `cleanAfterEveryBuildPatterns` parameter.
 *
 * @param {Object[]} plugins                      Array of plugin objects or classes.
 * @param {String[]} css                          Array of CSS file slugs.
 * @param {String}   prefix                       File prefix.
 * @param {String[]} cleanAfterEveryBuildPatterns List of patterns added to the CleanWebpackPlugin config.
 * @return {Object[]} Array of plugin objects or classes.
 */
function setupPlugins( plugins, css, prefix, cleanAfterEveryBuildPatterns ) {

	// Modify the CleanWebpackPlugin's cleanAfterEveryBuildPatterns config.
	if ( cleanAfterEveryBuildPatterns.length ) {

		plugins = plugins.filter( plugin => {

			if ( 'CleanWebpackPlugin' === plugin.constructor.name ) {

				plugin.cleanAfterEveryBuildPatterns = [
					...plugin.cleanAfterEveryBuildPatterns,
					...cleanAfterEveryBuildPatterns,
				];

			}

			return plugin;

		} );

	}

	const REMOVE_PLUGINS = [
		/**
		 * Remove the original WP Core dependency extractor. If we add an extractor
		 * without removing the initial one core dependencies get lost when our
		 * extractor runs.
		 */
		'DependencyExtractionWebpackPlugin',

		/**
		 * Remove the css extractor implemented in the default config.
		 *
		 * Our CSS extractor puts things in our preferred directory structure.
		 */
		'MiniCssExtractPlugin'
	];
	plugins = plugins.filter( plugin => ! REMOVE_PLUGINS.includes( plugin.constructor.name ) );

	css.forEach( file => {

		// Extract CSS.
		plugins.push( new cssExtract( {
			filename: `css/${ prefix }[name].css`,
		} ) );

		// Generate an RTL CSS file.
		plugins.push( new cssRTL( {
			filename: `css/${ prefix }[name]-rtl.css`,
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
 * @since 2.1.0 Add configuration option added to the CleanWebpackPlugin.
 *
 * @param {String[]} options.css                          Array of CSS file slugs.
 * @param {String[]} options.js                           Array of JS file slugs.
 * @param {String}   options.prefix                       File prefix.
 * @param {String}   options.outputPath                   Relative path to the output directory.
 * @param {String[]} options.cleanAfterEveryBuildPatterns List of patterns added to the CleanWebpackPlugin config.
 * @return {Object} A webpack.config.js object.
 */
module.exports = (
	{
		css = [],
		js = [],
		prefix = 'llms-',
		outputPath = 'assets/',
		srcPath = 'src/',
		cleanAfterEveryBuildPatterns = [],
	}
) => {

	return {
		...config,
		entry: setupEntry( js, srcPath ),
		output: {
			filename: `js/${ prefix }[name].js`,
			path: path.resolve( process.cwd(), outputPath ),
		},
		plugins: setupPlugins( config.plugins, css, prefix, cleanAfterEveryBuildPatterns ),
	};

}
