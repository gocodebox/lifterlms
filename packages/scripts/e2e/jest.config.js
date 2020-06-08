/**
 * Main Jest config
 *
 * @since Unknown
 * @since 1.3.0 Add support for using config when loaded as a module.
 */

const
	fs        = require( 'fs' ),
	// List of modules that should be excluded in the transformIgnorePatterns rule
	esModules = [ '@lifterlms' ].join( '|' );

/**
 * Resolve files
 *
 * Allows module files to be duplicated to the projects base test directory
 * located at tests/e2e
 *
 * If a file is found there, it is loaded, otherwise it will load the file
 * from the module's directory.
 *
 * @since 1.3.0
 *
 * @param {String} file Relative file path.
 * @return {String}
 */
function getFilePath( file ) {
	return fs.existsSync( file ) ? file : require.resolve( file );
}

/**
 * Load the jest-puppeteer config file
 *
 * @see https://github.com/smooth-code/jest-puppeteer/issues/160#issuecomment-491975158
 */
process.env.JEST_PUPPETEER_CONFIG = require.resolve( './jest-puppeteer.config.js' );

/**
 * Jest Config
 *
 * @link https://jestjs.io/docs/en/configuration.html
 */
module.exports = {

	preset: 'jest-puppeteer',
	// rootDir: process.cwd(),

	roots: [
		`${ process.cwd() }/tests/e2e/tests`,
	],

	setupFilesAfterEnv: [
		getFilePath( './bootstrap.js' ),
		getFilePath( './screenshot-reporter.js' ),
	],

	transformIgnorePatterns: [`/node_modules/(?!${ esModules })`],

	// Sort tests alphabetically by path. Ensures Tests in the "activate" directory run first.
	testSequencer: getFilePath( './sequencer.js' ),

};
