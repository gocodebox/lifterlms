/**
 * A Webpack configuration for building WordPress blocks.
 *
 * @since [version]
 * @version [version]
 */

/* eslint-disable no-console */

process.env.WP_SRC_DIRECTORY = process.env.WP_SRC_DIRECTORY || './src';

const BLOCK_METADATA_GLOB = '**/block.json';

const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' ),
	{ readFileSync } = require( 'fs' ),
	{ sync: glob } = require( 'fast-glob' ),
	{ resolve, dirname, join, extname, sep } = require( 'path' );

const config = require( '@wordpress/scripts/config/webpack.config' ),
	blockEntries = 'function' === typeof config.entry ? config.entry() : config.entry;

/**
 * Remove the leading blocks/ from all the block file entrypoints.
 *
 * This ensures that distributed blocks are in the blocks/ directory, not the blocks/blocks directory.
 *
 * @since [version]
 * @version [version]
 *
 * @return {Object} A webpack entries object.
 */
config.entry = () => {
	try {
		const entries = Object.fromEntries(
			Object.entries( blockEntries ).map( ( [ key, val ] ) => {
				return [ key.replace( 'blocks/', '' ), val ];
			} )
		);

		const blockMetadataFiles = glob(
			`${ resolve( process.env.WP_SRC_DIRECTORY ) }${ sep }${ BLOCK_METADATA_GLOB }`,
			{
				absolute: true,
			}
		);
		blockMetadataFiles.forEach( ( jsonFilePath ) => {
			/**
			 * Add SCSS file entries for any block styles that aren't already compiled via JS import in the related JS file.
			 *
			 * By default each script file, script, viewScript, and editorScript will also build css files if the related .scss file is
			 * imported in the JS file. The below enables us to utilize a stylesheet without an associated script, for example if we wish
			 * to load viewStyle without having to have a viewScript file.
			 */
			const { style, editorStyle, viewStyle } = JSON.parse( readFileSync( jsonFilePath ) );
			[ style, editorStyle, viewStyle ]
				.flat()
				.filter( ( value ) => value && value.startsWith( 'file:' ) )
				.forEach( ( value ) => {
					// Removes the `file:` prefix.
					const filepath = join(
						dirname( jsonFilePath ),
						value.replace( 'file:', '' )
					);

					const entryName = filepath
						.replace( extname( filepath ), '' )
						.replace( resolve( process.env.WP_SRC_DIRECTORY ) + sep + 'blocks' + sep, '' )
						.replace( /\\/g, '/' );

					if ( ! Object.keys( entries ).includes( entryName ) ) {
						entries[ entryName ] = filepath.replace( '.css', '.scss' );
					}
				} );
		} );

		return entries;
	} catch ( e ) {
		console.log( 'Error: ' );
		console.log( '' );
		console.log( '' );
		console.log( '' );
		console.error( e );
		console.log( '' );
		console.log( '' );
		console.log( '' );
		console.log( '' );
	}

	return {};
};

// Put blocks in the blocks/ dir not the build/ dir.
config.output.path = resolve( process.cwd(), 'blocks' );

config.plugins.forEach( ( plugin ) => {
	const { name: pluginName } = plugin.constructor;

	if ( 'CopyPlugin' === pluginName && BLOCK_METADATA_GLOB === plugin.patterns[ 0 ].from ) {
		/**
		 * Modifies the copy plugin that moves block.json files from src/blocks -> blocks/.
		 *
		 * The default plugin moves the block.json file to blocks/blocks/[block-dir]/block.json.
		 */
		plugin.patterns[ 0 ].context = 'src/blocks';

		/**
		 * Copies block PHP files.
		 *
		 * This is a pattern the `@wordpress/block-library` follows.
		 */
		plugin.patterns.push( {
			from: '**/**.php',
			context: 'src/blocks',
			noErrorOnMissing: true,
		} );
	}
} );

// Removes empty .js files created when adding SCSS files to the entries array.
config.plugins.push( new RemoveEmptyScriptsPlugin() );

module.exports = config;
