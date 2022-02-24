/**
 * Webpack config
 *
 * @package LifterLMS/Scripts/Dev
 *
 * @since 5.5.0
 * @version [version]
 */

const { readdirSync } = require( 'fs' ),
	{ resolve } = require( 'path' ),
	{ CleanWebpackPlugin } = require( 'clean-webpack-plugin' ),
	CopyPlugin = require( 'copy-webpack-plugin' ),
	generate = require( '@lifterlms/scripts/config/webpack.config' ),
	config = generate( {
		js: [
			'admin-addons',
			'admin-award-certificate',
			'admin-certificate-editor',

			// Module packages.
			'components',
			'icons',
			'utils',
		],
		css: [ 'admin-addons' ],
		outputPath: '',
	} ),
	defaultOutput = JSON.parse( JSON.stringify( config.output ) ),
	blocks = readdirSync( './src/blocks' ),
	patterns = [],
	ASSETS_DIR = 'assets';

// Setup entries and copy patterns for all blocks in the block library.
blocks.forEach( id => {
	config.entry[ id ] = resolve( process.cwd(), `src/blocks/${ id }/index.js` );
	patterns.push( {
		from: `src/blocks/${ id }/block.json`,
		to: `blocks/${ id }/block.json`,
	} );
} );

// Conditional output. Block JS is stored in the /blocks directory where as all else is stored in the assets/ dir.
config.output.filename = ( pathData, assetInfo ) => {

	if ( blocks.includes( pathData.chunk.name ) ) {
		return 'blocks/[name]/index.js';
	}

	return `${ ASSETS_DIR }/${ defaultOutput.filename }`;

};

// Remove the default directory clearer.
config.plugins = config.plugins.filter( plugin => {
	return 'CleanWebpackPlugin' !== plugin.constructor.name;
} );

// Update the paths of CSS files.
config.plugins = config.plugins.map( plugin => {
	if ( [ 'MiniCssExtractPlugin', 'WebpackRTLPlugin' ].includes( plugin.constructor.name ) ) {
		plugin.options.filename = `${ ASSETS_DIR }/${ plugin.options.filename }`;
	}
	return plugin;
} );

// Modified clean.
config.plugins.push( new CleanWebpackPlugin( {

	cleanOnceBeforeBuildPatterns: [
		// Source maps.
		`${ ASSETS_DIR }/js/*.js.map`,

		// Clean all blocks.
		'blocks/*',
	],

} ) );

// Copy block.json files to blocks/${block}/block.json
config.plugins.push( new CopyPlugin( {
	patterns,
} ) );

module.exports = config;
