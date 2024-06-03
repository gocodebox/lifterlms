/**
 * Webpack config
 *
 * @package
 *
 * @since Unknown
 * @version [version] 
 */

// Deps.
const
	cssExtract = require( 'mini-css-extract-plugin' ),
	cssRTL = require( 'webpack-rtl-plugin' ),
	config = require( '@wordpress/scripts/config/webpack.config' ),
	depExtract = require( '@wordpress/dependency-extraction-webpack-plugin' ),
	path = require( 'path' );

/**
 * Used by dependency extractor to handle requests to convert names of scripts included in the LifterLMS Core.
 *
 * @since 1.2.1
 * @since 3.0.0 Load `@lifterlms/*` packages into the `window.llms` namespace.
 *
 * @param {string} request External script slug/id.
 * @return {string | Array} A string
 */
function requestToExternal( request ) {
	if ( 'llms-quill' === request ) {
		return 'Quill';
	} else if ( 'llms-izimodal' === request ) {
		return [ 'jQuery', 'iziModal' ];
	} else if ( request.startsWith( 'llms/' ) || request.startsWith( 'LLMS/' ) ) {
		return request.split( '/' );
	} else if ( request.startsWith( '@lifterlms/' ) ) {
		return [ 'llms', request.replace( '@lifterlms/', '' ) ];
	}
}

/**
 * Used by dependency extractor to handle requests to scripts included in the LifterLMS Core.
 *
 * @since 1.2.1
 * @since 3.0.0 Use `llms-*` as the script ID for `@lifterlms/*` packages.
 *
 * @param {string} request External script slug/id.
 * @return {string | Array} A string
 */
function requestToHandle( request ) {
	if ( request.startsWith( 'llms/' ) || request.startsWith( 'LLMS/' ) ) {
		return 'llms';
	} else if ( request.startsWith( '@lifterlms/' ) ) {
		return request.replace( '@lifterlms/', 'llms-' );
	}
}

/**
 * Configure the `entry` object of the webpack config file.
 *
 * @since 1.2.1
 * @since 1.2.3 Add a configurable source file path.
 *
 * @param {string[]} js      Array of JS file slugs.
 * @param {string}   srcPath Relative path to the base source file directory.
 * @return {Object} Webpack config entry object.
 */
function setupEntry( js, srcPath ) {
	const entry = {};
	js.forEach( ( file ) => {
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
 * @since 3.1.0 Add `protectWebpackAssets = false` to the `CleanWebpackPlugin` config.
 * @since 4.0.0 Remove the copy plugin pattern responsible for copying block.json files.
 *
 * @param {Object[]} plugins                      Array of plugin objects or classes.
 * @param {string[]} css                          Array of CSS file slugs.
 * @param {string}   prefix                       File prefix.
 * @param {string[]} cleanAfterEveryBuildPatterns List of patterns added to the CleanWebpackPlugin config.
 * @return {Object[]} Array of plugin objects or classes.
 */
function setupPlugins( plugins, css, prefix, cleanAfterEveryBuildPatterns ) {
	// Modify the CleanWebpackPlugin's cleanAfterEveryBuildPatterns config.
	if ( cleanAfterEveryBuildPatterns.length ) {
		plugins = plugins.filter( ( plugin ) => {
			if ( 'CleanWebpackPlugin' === plugin.constructor.name ) {
				plugin.cleanAfterEveryBuildPatterns = [
					...plugin.cleanAfterEveryBuildPatterns,
					...cleanAfterEveryBuildPatterns,
				];
				// Allow removal of current webpack assets.
				plugin.protectWebpackAssets = false;
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
		'MiniCssExtractPlugin',
	];
	plugins = plugins.filter( ( plugin ) => {
		const { name: pluginName } = plugin.constructor;

		/**
		 * Removes the copy plugin that copies block.json files from the src/ dir into the assets/blocks dir.
		 *
		 * Since we store blocks in the blocks/ dir we don't need this when compiling non-block assets.
		 */
		if ( 'CopyPlugin' === pluginName && '**/block.json' === plugin.patterns[ 0 ].from ) {
			return false;
		}

		return ! REMOVE_PLUGINS.includes( pluginName );
	} );

	css.forEach( () => {
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
 * Allow babel transpilation for the whole `@lifterlms` package.
 *
 * By default the WordPress' webpack config excludes all the packages
 * in `node_modules/` from being transpiled by babel.
 * With this we allow the whole `@lifterlms` package to be transpiled by babel during builds.
 *
 * @since 4.0.1
 * @since [version] Fixed cases when the module's rule has no loader.
 *
 * @param {Object[]} config Webpack config.
 * @return {Object[]}
 */
function allowBabelTranspilation( config ) {
	config.module.rules = config.module.rules.filter( ( rule ) => {
		if ( rule.exclude && '/node_modules/' === rule.exclude.toString() &&
				rule.use[0].loader?.indexOf('node_modules/babel-loader') ) {
			rule.exclude = /node_modules\/(?!\@lifterlms\/)/;
		}
		return rule;
	});
	return config;
}

/**
 * Generates a Webpack config object.
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
 * @since 4.0.1 Parse the original WordPress' config to allow babel transpilation for the whole `@lifterlms` package.
 *
 * @param {Object}   options                              Configuration options.
 * @param {string[]} options.css                          Array of CSS file slugs.
 * @param {string[]} options.js                           Array of JS file slugs.
 * @param {string}   options.prefix                       File prefix.
 * @param {string}   options.outputPath                   Relative path to the output directory.
 * @param {string}   options.srcPath                      Relative path to the base source file directory.
 * @param {string[]} options.cleanAfterEveryBuildPatterns List of patterns added to the CleanWebpackPlugin config.
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
		...allowBabelTranspilation( config ),
		entry: setupEntry( js, srcPath ),
		output: {
			filename: `js/${ prefix }[name].js`,
			path: path.resolve( process.cwd(), outputPath ),
		},
		plugins: setupPlugins( config.plugins, css, prefix, cleanAfterEveryBuildPatterns ),
	};
};
